<?php

namespace Modules\Flashcard\Tests\Unit\Services;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Modules\Flashcard\Models\Flashcard;
use Modules\Flashcard\Services\FlashcardService;

class FlashcardServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    private FlashcardService $flashcardService;

    public function test_create_flashcard(): void
    {
        $question = $this->faker->sentence;
        $answer = $this->faker->paragraph;

        $flashcard = $this->flashcardService->createFlashcard($question, $answer);

        $this->assertInstanceOf(Flashcard::class, $flashcard);
        $this->assertEquals($question, $flashcard->question);
        $this->assertEquals($answer, $flashcard->answer);
        $this->assertEquals($this->user->id, $flashcard->user_id);
        $this->assertDatabaseHas('flashcards', [
            'id' => $flashcard->id,
            'question' => $question,
            'answer' => $answer,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_list_flashcards(): void
    {
        Flashcard::factory()->count(3)->create(['user_id' => $this->user->id]);

        $flashcards = $this->flashcardService->listFlashcards();

        $this->assertInstanceOf(Collection::class, $flashcards);
        $this->assertCount(3, $flashcards);
        foreach ($flashcards as $flashcard) {
            $this->assertInstanceOf(Flashcard::class, $flashcard);
        }
    }

    public function test_find_flashcard(): void
    {
        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);

        $foundFlashcard = $this->flashcardService->findFlashcard($flashcard->id);

        $this->assertInstanceOf(Flashcard::class, $foundFlashcard);
        $this->assertEquals($flashcard->id, $foundFlashcard->id);
    }

    public function test_find_flashcard_not_found(): void
    {
        $foundFlashcard = $this->flashcardService->findFlashcard(999);

        $this->assertNull($foundFlashcard);
    }

    public function test_find_flashcard_with_deleted(): void
    {
        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);
        $flashcard->delete();

        $foundFlashcard = $this->flashcardService->findFlashcard($flashcard->id, true);

        $this->assertInstanceOf(Flashcard::class, $foundFlashcard);
        $this->assertEquals($flashcard->id, $foundFlashcard->id);
        $this->assertNotNull($foundFlashcard->deleted_at);
    }

    public function test_edit_flashcard(): void
    {
        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);
        $newQuestion = $this->faker->sentence;
        $newAnswer = $this->faker->paragraph;

        $result = $this->flashcardService->editFlashcard($flashcard, $newQuestion, $newAnswer);

        $this->assertTrue($result);
        $this->assertDatabaseHas('flashcards', [
            'id' => $flashcard->id,
            'question' => $newQuestion,
            'answer' => $newAnswer,
        ]);
    }

    public function test_delete_flashcard(): void
    {
        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);

        $result = $this->flashcardService->deleteFlashcard($flashcard);

        $this->assertTrue($result);
        $this->assertSoftDeleted('flashcards', [
            'id' => $flashcard->id,
        ]);
    }

    public function test_get_statistics(): void
    {
        Flashcard::factory()->count(5)->create(['user_id' => $this->user->id, 'status' => 'Correct']);
        Flashcard::factory()->count(3)->create(['user_id' => $this->user->id, 'status' => 'Incorrect']);
        Flashcard::factory()->count(2)->create(['user_id' => $this->user->id, 'status' => 'Not Answered']);

        $statistics = $this->flashcardService->getStatistics();

        $this->assertIsArray($statistics);
        $this->assertEquals(10, $statistics['total_questions']);
        $this->assertEquals(80, $statistics['percentage_answered']);
        $this->assertEquals(50, $statistics['percentage_correct']);
    }

    public function test_reset_progress(): void
    {
        Flashcard::factory()->count(5)->create(['user_id' => $this->user->id, 'status' => 'Correct']);

        $this->flashcardService->resetProgress();

        $this->assertDatabaseHas('flashcards', [
            'user_id' => $this->user->id,
            'status' => 'Not Answered',
        ]);
    }

    public function test_update_flashcard_status(): void
    {
        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id, 'status' => 'Not Answered']);
        $status = 'Correct';

        $result = $this->flashcardService->updateFlashcardStatus($flashcard, $status);

        $this->assertTrue($result);
        $this->assertDatabaseHas('flashcards', [
            'id' => $flashcard->id,
            'status' => $status,
        ]);
    }

    public function test_restore_flashcard(): void
    {
        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);
        $flashcard->delete();

        $result = $this->flashcardService->restoreFlashcard($flashcard);

        $this->assertTrue($result);
        $this->assertDatabaseHas('flashcards', [
            'id' => $flashcard->id,
            'deleted_at' => null,
        ]);
    }

    public function test_get_flashcard_history(): void
    {
        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);
        $flashcard->update(['question' => 'New Question']);

        $history = $this->flashcardService->getFlashcardHistory($flashcard);

        $this->assertGreaterThan(0, count($history));
        $this->assertEquals('New Question', $history[1]['question']);
    }

    public function test_revert_flashcard_to_audit(): void
    {
        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id, 'question' => 'Original Question', 'answer' => 'Original Answer']);
        $flashcard->update(['question' => 'Updated Question', 'answer' => 'Updated Answer']);
        $audit = $flashcard->audits()->latest()->first();

        $this->flashcardService->revertFlashcardToAudit($flashcard, $audit->id);

        $this->assertDatabaseHas('flashcards', [
            'id' => $flashcard->id,
            'question' => 'Original Question',
            'answer' => 'Original Answer',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFaker();
        $this->user = User::factory()->create();
        $this->flashcardService = new FlashcardService;
        $this->flashcardService->setUser($this->user);
    }
}
