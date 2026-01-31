<?php

namespace Modules\Flashcard\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Flashcard\Models\Flashcard;

class FlashcardService
{
    private ?User $user;

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function createFlashcard(string $question, string $answer): Flashcard
    {
        return $this->user->flashcards()->create([
            'question' => $question,
            'answer' => $answer,
        ]);
    }

    /**
     * @return Collection<Flashcard>
     */
    public function listFlashcards(): Collection
    {
        return $this->user->flashcards()->withTrashed()->get();
    }

    public function findFlashcard(int $flashcardId, bool $withDeleted = false): ?Flashcard
    {
        $query = $this->user->flashcards();
        if ($withDeleted) {
            $query = $query->withTrashed();
        }

        return
            $query->where('id', $flashcardId)
                ->first();
    }

    public function editFlashcard(Flashcard $flashcard, string $newQuestion, string $newAnswer): bool
    {
        return $flashcard->update(['question' => $newQuestion, 'answer' => $newAnswer]);
    }

    public function deleteFlashcard(Flashcard $flashcard): ?bool
    {
        return $flashcard->delete();
    }

    public function getStatistics(): array
    {
        $totalQuestions = $this->user->flashcards()->count();
        $answeredQuestions = $this->user->flashcards()->where('status', '!=', 'Not Answered')->count();
        $correctlyAnsweredQuestions = $this->user->flashcards()->where('status', 'Correct')->count();

        $percentageAnswered = $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100, 2) : 0;
        $percentageCorrect = $totalQuestions > 0 ? round(($correctlyAnsweredQuestions / $totalQuestions) * 100, 2) : 0;

        return [
            'total_questions' => $totalQuestions,
            'percentage_answered' => $percentageAnswered,
            'percentage_correct' => $percentageCorrect,
        ];
    }

    public function resetProgress(): void
    {
        DB::table('flashcards')
            ->where('user_id', $this->user->id)
            ->update(['status' => 'Not Answered']);
    }

    public function updateFlashcardStatus(Flashcard $flashcard, string $status): bool
    {
        return $flashcard->update(['status' => $status]);
    }

    public function restoreFlashcard(Flashcard $flashcard): bool
    {
        return $flashcard->restore();
    }

    public function getFlashcardHistory(Flashcard $flashcard): array
    {
        $audits = $flashcard->audits()->with('user')->latest()->get();

        $auditData = [];

        foreach ($audits as $audit) {
            $data = array_merge(
                [
                    'question' => $flashcard->question,
                    'answer' => $flashcard->answer,
                    'status' => $flashcard->status,
                    'deleted_at' => $flashcard->deleted_at,
                ],
                $audit->old_values ?? [],
                $audit->new_values ?? []
            );

            $auditData[] = [
                'audit_id' => $audit->id,
                'question' => $data['question'],
                'answer' => $data['answer'],
                'status' => $data['status'],
                'deleted' => $data['deleted_at'] ? 'Yes' : 'No',
            ];
        }

        return $auditData;
    }

    public function revertFlashcardToAudit(Flashcard $flashcard, int $auditId): void
    {
        $audit = $flashcard->audits()->find($auditId);

        if (! $audit) {
            abort(404, 'Audit record not found');
        }

        $dataToRevert = array_merge(
            [
                'question' => $flashcard->question,
                'answer' => $flashcard->answer,
                'status' => $flashcard->status,
                'deleted_at' => $flashcard->deleted_at,
            ],
            $audit->old_values ?? [],
            $audit->new_values ?? [],
        );

        $flashcard->update([
            'question' => $dataToRevert['question'],
            'answer' => $dataToRevert['answer'],
            'status' => $dataToRevert['status'],
            'deleted_at' => $dataToRevert['deleted_at'],
        ]);
    }
}
