<?php

namespace Modules\Flashcard\Console;

use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\ValidatedData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use JetBrains\PhpStorm\NoReturn;
use Modules\Flashcard\Http\Requests\CreateFlashcardRequest;
use Modules\Flashcard\Http\Requests\FindFlashcardRequest;
use Modules\Flashcard\Http\Requests\UpdateFlashcardRequest;
use Modules\Flashcard\Models\Flashcard;
use Modules\Flashcard\Services\FlashcardService;

class FlashcardInteractiveCommand extends Command
{
    protected $signature = 'flashcard:interactive';

    protected $description = 'Enter interactive CLI mode for flashcards';

    private FlashcardService $flashcardService;

    #[NoReturn]
    public function handle(): void
    {
        $this->info('Welcome to the Flashcard Interactive CLI! You must login to proceed.');

        /** @var User $user */
        $user = $this->auth();
        $this->flashcardService = new FlashcardService;
        $this->flashcardService->setUser($user);

        $this->info("You have successfully logged in. Welcome, $user->name!");

        while (true) {
            $action = $this->choice(
                'What would you like to do?',
                [
                    '1' => 'Create a new flashcard',
                    '2' => 'List all flashcards',
                    '3' => 'View a flashcard',
                    '4' => 'Edit a flashcard',
                    '5' => 'Delete a flashcard',
                    '6' => 'Restore a flashcard',
                    '7' => 'Revert a flashcard',
                    '8' => 'Practice',
                    '9' => 'Statistics',
                    '10' => 'Reset',
                    '0' => 'Exit',
                ],
                '0'
            );

            if ($action === 'Exit') {
                $this->info('Goodbye!');

                return;
            }

            match ($action) {
                'Create a new flashcard' => $this->createFlashcard(),
                'List all flashcards' => $this->listFlashcards(),
                'View a flashcard' => $this->viewFlashcard(),
                'Edit a flashcard' => $this->editFlashcard(),
                'Delete a flashcard' => $this->deleteFlashcard(),
                'Restore a flashcard' => $this->restoreFlashcard(),
                'Revert a flashcard' => $this->revertFlashcard(),
                'Practice' => $this->practiceFlashcards(),
                'Statistics' => $this->showStatistics(),
                'Reset' => $this->resetProgress(),
                default => $this->error('Invalid action.'),
            };
        }
    }

    private function auth(): Authenticatable
    {
        while (true) {
            $username = $this->ask('Enter your user name');
            $password = $this->secret('Enter your password');

            if (Auth::attempt(['name' => $username, 'password' => $password])) {
                return Auth::user();
            } else {
                $this->error('Wrong username or password');
            }
        }
    }

    private function createFlashcard(): void
    {
        $this->info('Creating a new flashcard...');

        $question = $this->ask('Enter the question');
        $answer = $this->ask('Enter the answer');

        try {
            $data = $this->validateRequest(new CreateFlashcardRequest, [
                'question' => $question,
                'answer' => $answer,
            ]);

            $this->flashcardService->createFlashcard($data['question'], $data['answer']);
            $this->info('Flashcard created successfully!');
        } catch (ValidationException $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * @throws ValidationException
     */
    private function validateRequest(FormRequest $request, array $data): array|ValidatedData
    {
        $validator = Validator::make($data, $request->rules());

        return $validator->validate();
    }

    private function listFlashcards(): void
    {
        $this->info('Listing all flashcards...');

        $flashcards = $this->flashcardService->listFlashcards();

        if ($flashcards->isEmpty()) {
            $this->info('No flashcards found.');

            return;
        }

        $headers = ['ID', 'Question', 'Answer', 'Status', 'Deleted'];
        $data = $flashcards->map(fn ($f) => [
            'id' => $f->id,
            'question' => $f->question,
            'answer' => $f->answer,
            'status' => $f->status,
            'deleted' => $f->deleted_at ? 'Yes' : 'No',
        ])->toArray();

        $this->table($headers, $data);
    }

    private function viewFlashcard(): void
    {
        $this->info('Viewing a flashcard...');

        $flashcard = $this->selectFlashcard();

        if (! $flashcard) {
            $this->error('Flashcard not found.');

            return;
        }

        $this->info("Question: $flashcard->question");
        $this->info("Answer: $flashcard->answer");
    }

    private function selectFlashcard($flashcardId = null, bool $withDeleted = false): ?Flashcard
    {
        try {
            $data = $this->validateRequest(new FindFlashcardRequest, [
                'id' => $flashcardId ?? $this->ask('Enter the ID of the flashcard'),
            ]);
        } catch (ValidationException $exception) {
            $this->error($exception->getMessage());

            return null;
        }

        return $this->flashcardService->findFlashcard($data['id'], $withDeleted);
    }

    private function editFlashcard(): void
    {
        $this->info('Editing a flashcard...');

        $flashcard = $this->selectFlashcard();

        if (! $flashcard) {
            $this->error('Flashcard not found.');

            return;
        }

        $newQuestion = $this->ask('Enter the new question', $flashcard->question);
        $newAnswer = $this->ask('Enter the new answer', $flashcard->answer);

        try {
            $data = $this->validateRequest(new UpdateFlashcardRequest, [
                'question' => $newQuestion,
                'answer' => $newAnswer,
            ]);

            $this->flashcardService->editFlashcard($flashcard, $data['question'], $data['answer']);
            $this->info('Flashcard updated successfully!');
        } catch (ValidationException $exception) {
            $this->error($exception->getMessage());
        }
    }

    private function deleteFlashcard(): void
    {
        $this->info('Deleting a flashcard...');

        $flashcard = $this->selectFlashcard(withDeleted: true);

        if (! $flashcard) {
            $this->error('Flashcard not found.');

            return;
        }

        if ($flashcard->trashed()) {
            if ($this->confirm("Are you sure you want to PERMANENTLY delete flashcard $flashcard->id with question: $flashcard->question? This action cannot be undone.")) {
                $flashcard->forceDelete();
                $this->info('Flashcard permanently deleted.');
            } else {
                $this->info('Deletion cancelled.');
            }
        } else {
            if ($this->confirm("Are you sure you want to delete flashcard $flashcard->id with question: $flashcard->question?")) {
                $this->flashcardService->deleteFlashcard($flashcard);
                $this->info('Flashcard deleted successfully.');
            } else {
                $this->info('Deletion cancelled.');
            }
        }
    }

    private function restoreFlashcard(): void
    {
        $this->info('Restoring a flashcard...');

        $flashcard = $this->selectFlashcard(withDeleted: true);

        if (! $flashcard) {
            $this->error('Flashcard not found.');

            return;
        }

        if (! $flashcard->trashed()) {
            $this->error('Flashcard is not deleted.');

            return;
        }

        $this->flashcardService->restoreFlashcard($flashcard);
        $this->info('Flashcard restored successfully.');
    }

    private function revertFlashcard(): void
    {
        $this->info('Reverting a flashcard to a previous state...');

        $flashcard = $this->selectFlashcard(withDeleted: true);

        if (! $flashcard) {
            $this->error('Flashcard not found.');

            return;
        }

        $history = $this->flashcardService->getFlashcardHistory($flashcard);

        $this->info("Audit History for Flashcard #$flashcard->id:");

        $headers = ['ID', 'Question', 'Answer', 'Status', 'Deleted'];

        $auditData = collect($history)->map(fn ($item) => [
            'ID' => $item['audit_id'],
            'Question' => $item['question'],
            'Answer' => $item['answer'],
            'Status' => $item['status'],
            'Deleted' => $item['deleted'],
        ])->toArray();

        $this->table($headers, $auditData);

        $selectedAuditId = $this->ask('Enter the ID of the state you want to revert to (or type "exit" to cancel)');

        if ($selectedAuditId === 'exit') {
            $this->info('Revert cancelled.');

            return;
        }

        try {
            $this->flashcardService->revertFlashcardToAudit($flashcard, (int) $selectedAuditId);
            $this->info("Flashcard #$flashcard->id reverted to state #$selectedAuditId successfully.");
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    private function practiceFlashcards(): void
    {
        $this->info('Starting practice mode...');

        while (true) {
            $flashcards = $this->flashcardService->listFlashcards();

            if ($flashcards->isEmpty()) {
                $this->info('You have no flashcards to practice.');

                return;
            }

            $headers = ['ID', 'Question', 'Status'];

            $data = $flashcards->map(fn ($f) => [
                'id' => $f->id,
                'question' => $f->question,
                'status' => $f->status,
            ])->toArray();

            $total = count($data);
            $correct = collect($data)->where('status', 'Correct')->count();
            $percent = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

            $this->table($headers, $data);
            $this->info("Completion Percentage: $percent%");

            $flashcardId = $this->ask('Enter the ID of the question you want to practice (or type "exit" to stop)');

            if ($flashcardId === 'exit') {
                $this->info('Exiting practice mode.');

                return;
            }

            $flashcard = $this->selectFlashcard($flashcardId);

            if (! $flashcard) {
                $this->error('Flashcard not found.');

                return;
            }

            if ($flashcard->status === 'Correct') {
                $this->error('You have already answered this question correctly. Please select another question.');

                continue;
            }

            $this->info("Question: $flashcard->question");
            $answer = $this->ask('Enter your answer');

            if ($answer === $flashcard->answer) {
                $this->info('Correct!');
                $this->flashcardService->updateFlashcardStatus($flashcard, 'Correct');
            } else {
                $this->error("Incorrect. The correct answer is: $flashcard->answer");
                $this->flashcardService->updateFlashcardStatus($flashcard, 'Incorrect');
            }
        }
    }

    private function showStatistics(): void
    {
        $this->info('Showing statistics...');

        $stats = $this->flashcardService->getStatistics();

        $this->info("Total questions: {$stats['total_questions']}");
        $this->info("Percentage of questions answered: {$stats['percentage_answered']}%");
        $this->info("Percentage correctly answered: {$stats['percentage_correct']}%");
    }

    private function resetProgress(): void
    {
        if ($this->confirm('Are you sure you want to reset all your practice progress?')) {
            $this->flashcardService->resetProgress();
            $this->info('Practice progress reset successfully.');
        } else {
            $this->info('Reset cancelled.');
        }
    }
}
