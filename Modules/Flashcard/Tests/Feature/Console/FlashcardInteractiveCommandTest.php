<?php

namespace Modules\Flashcard\Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Modules\Flashcard\Models\Flashcard;
use Modules\Flashcard\Services\FlashcardService;

class FlashcardInteractiveCommandTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    public function test_exit(): void
    {
        $this->artisan('flashcard:interactive')
            ->expectsQuestion('Enter your user name', $this->user->name)
            ->expectsQuestion('Enter your password', 'password')
            ->expectsQuestion('What would you like to do?', 'Exit')
            ->expectsOutput('Goodbye!');
    }

    public function test_create_flashcard(): void
    {
        $question = $this->faker->sentence;
        $answer = $this->faker->sentence;

        $this->artisan('flashcard:interactive')
            ->expectsQuestion('Enter your user name', $this->user->name)
            ->expectsQuestion('Enter your password', 'password')
            ->expectsQuestion('What would you like to do?', 'Create a new flashcard')
            ->expectsQuestion('Enter the question', $question)
            ->expectsQuestion('Enter the answer', $answer)
            ->expectsOutput('Flashcard created successfully!')
            ->expectsQuestion('What would you like to do?', 'Exit')
            ->expectsOutput('Goodbye!')
            ->assertExitCode(0);

        $this->assertDatabaseHas('flashcards', [
            'question' => $question,
            'answer' => $answer,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_list_flashcards(): void
    {
        Flashcard::factory()->count(3)->create(['user_id' => $this->user->id]);

        $this->artisan('flashcard:interactive')
            ->expectsQuestion('Enter your user name', $this->user->name)
            ->expectsQuestion('Enter your password', 'password')
            ->expectsQuestion('What would you like to do?', 'List all flashcards')
            ->expectsTable(
                ['ID', 'Question', 'Answer', 'Status', 'Deleted'],
                Flashcard::where('user_id', $this->user->id)->get()->map(function ($flashcard) {
                    return [
                        'id' => $flashcard->id,
                        'question' => $flashcard->question,
                        'answer' => $flashcard->answer,
                        'status' => $flashcard->status,
                        'deleted' => $flashcard->deleted_at ? 'Yes' : 'No',
                    ];
                })->toArray()
            )
            ->expectsQuestion('What would you like to do?', 'Exit')
            ->expectsOutput('Goodbye!')
            ->assertExitCode(0);
    }

    public function test_view_flashcard(): void
    {
        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);

        $this->artisan('flashcard:interactive')
            ->expectsQuestion('Enter your user name', $this->user->name)
            ->expectsQuestion('Enter your password', 'password')
            ->expectsQuestion('What would you like to do?', 'View a flashcard')
            ->expectsQuestion('Enter the ID of the flashcard', $flashcard->id)
            ->expectsOutput("Question: $flashcard->question")
            ->expectsOutput("Answer: $flashcard->answer")
            ->expectsQuestion('What would you like to do?', 'Exit')
            ->expectsOutput('Goodbye!')
            ->assertExitCode(0);
    }

    public function test_edit_flashcard(): void
    {
        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);
        $newQuestion = $this->faker->sentence;
        $newAnswer = $this->faker->sentence;

        $this->artisan('flashcard:interactive')
            ->expectsQuestion('Enter your user name', $this->user->name)
            ->expectsQuestion('Enter your password', 'password')
            ->expectsQuestion('What would you like to do?', 'Edit a flashcard')
            ->expectsQuestion('Enter the ID of the flashcard', $flashcard->id)
            ->expectsQuestion('Enter the new question', $newQuestion)
            ->expectsQuestion('Enter the new answer', $newAnswer)
            ->expectsOutput('Flashcard updated successfully!')
            ->expectsQuestion('What would you like to do?', 'Exit')
            ->expectsOutput('Goodbye!')
            ->assertExitCode(0);

        $this->assertDatabaseHas('flashcards', [
            'id' => $flashcard->id,
            'question' => $newQuestion,
            'answer' => $newAnswer,
        ]);
    }

    public function test_delete_flashcard(): void
    {
        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);

        $this->artisan('flashcard:interactive')
            ->expectsQuestion('Enter your user name', $this->user->name)
            ->expectsQuestion('Enter your password', 'password')
            ->expectsQuestion('What would you like to do?', 'Delete a flashcard')
            ->expectsQuestion('Enter the ID of the flashcard', $flashcard->id)
            ->expectsConfirmation("Are you sure you want to delete flashcard $flashcard->id with question: $flashcard->question?", 'yes')
            ->expectsOutput('Flashcard deleted successfully.')
            ->expectsQuestion('What would you like to do?', 'Exit')
            ->expectsOutput('Goodbye!')
            ->assertExitCode(0);

        $this->assertSoftDeleted('flashcards', [
            'id' => $flashcard->id,
        ]);
    }

    public function test_restore_flashcard(): void
    {
        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);

        $this->artisan('flashcard:interactive')
            ->expectsQuestion('Enter your user name', $this->user->name)
            ->expectsQuestion('Enter your password', 'password')
            ->expectsQuestion('What would you like to do?', 'Delete a flashcard')
            ->expectsQuestion('Enter the ID of the flashcard', $flashcard->id)
            ->expectsConfirmation("Are you sure you want to delete flashcard $flashcard->id with question: $flashcard->question?", 'yes')
            ->expectsOutput('Flashcard deleted successfully.')
            ->expectsQuestion('What would you like to do?', 'Restore a flashcard')
            ->expectsQuestion('Enter the ID of the flashcard', $flashcard->id)
            ->expectsOutput('Flashcard restored successfully.')
            ->expectsQuestion('What would you like to do?', 'Exit')
            ->expectsOutput('Goodbye!')
            ->assertExitCode(0);

        $this->assertDatabaseHas('flashcards', [
            'id' => $flashcard->id,
            'deleted_at' => null,
        ]);
    }

    public function test_restore_flashcard_not_deleted(): void
    {
        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);

        $this->artisan('flashcard:interactive')
            ->expectsQuestion('Enter your user name', $this->user->name)
            ->expectsQuestion('Enter your password', 'password')
            ->expectsQuestion('What would you like to do?', 'Restore a flashcard')
            ->expectsQuestion('Enter the ID of the flashcard', $flashcard->id)
            ->expectsOutput('Flashcard is not deleted.')
            ->expectsQuestion('What would you like to do?', 'Exit')
            ->expectsOutput('Goodbye!')
            ->assertExitCode(0);
    }

    public function test_practice_flashcards(): void
    {
        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);

        $this->artisan('flashcard:interactive')
            ->expectsQuestion('Enter your user name', $this->user->name)
            ->expectsQuestion('Enter your password', 'password')
            ->expectsQuestion('What would you like to do?', 'Practice')
            ->expectsTable(
                ['ID', 'Question', 'Status'],
                Flashcard::where('user_id', $this->user->id)->get()->map(function ($flashcard) {
                    return [
                        'id' => $flashcard->id,
                        'question' => $flashcard->question,
                        'status' => $flashcard->status,
                    ];
                })->toArray()
            )
            ->expectsOutput('Completion Percentage: 0%')
            ->expectsQuestion('Enter the ID of the question you want to practice (or type "exit" to stop)', $flashcard->id)
            ->expectsOutput("Question: $flashcard->question")
            ->expectsQuestion('Enter your answer', $flashcard->answer)
            ->expectsOutput('Correct!')
            ->expectsQuestion('Enter the ID of the question you want to practice (or type "exit" to stop)', 'exit')
            ->expectsQuestion('What would you like to do?', 'Exit')
            ->expectsOutput('Goodbye!')
            ->assertExitCode(0);

        $this->assertDatabaseHas('flashcards', [
            'id' => $flashcard->id,
            'status' => 'Correct',
        ]);
    }

    public function test_statistics(): void
    {
        Flashcard::factory()->count(5)->create(['status' => 'Not Answered', 'user_id' => $this->user->id]);
        Flashcard::factory()->count(3)->create(['status' => 'Correct', 'user_id' => $this->user->id]);
        Flashcard::factory()->count(2)->create(['status' => 'Incorrect', 'user_id' => $this->user->id]);

        $this->artisan('flashcard:interactive')
            ->expectsQuestion('Enter your user name', $this->user->name)
            ->expectsQuestion('Enter your password', 'password')
            ->expectsQuestion('What would you like to do?', 'Statistics')
            ->expectsOutput('Total questions: 10')
            ->expectsOutput('Percentage of questions answered: 50%')
            ->expectsOutput('Percentage correctly answered: 30%')
            ->expectsQuestion('What would you like to do?', 'Exit')
            ->expectsOutput('Goodbye!')
            ->assertExitCode(0);
    }

    public function test_reset_progress(): void
    {
        Flashcard::factory()->count(3)->create(['status' => 'Correct', 'user_id' => $this->user->id]);
        Flashcard::factory()->count(2)->create(['status' => 'Incorrect', 'user_id' => $this->user->id]);

        $this->artisan('flashcard:interactive')
            ->expectsQuestion('Enter your user name', $this->user->name)
            ->expectsQuestion('Enter your password', 'password')
            ->expectsQuestion('What would you like to do?', 'Reset')
            ->expectsConfirmation('Are you sure you want to reset all your practice progress?', 'yes')
            ->expectsOutput('Practice progress reset successfully.')
            ->expectsQuestion('What would you like to do?', 'Exit')
            ->expectsOutput('Goodbye!')
            ->assertExitCode(0);

        $this->assertDatabaseHas('flashcards', [
            'status' => 'Not Answered',
        ]);
    }

    public function test_invalid_input_view_flashcard(): void
    {
        $this->artisan('flashcard:interactive')
            ->expectsQuestion('Enter your user name', $this->user->name)
            ->expectsQuestion('Enter your password', 'password')
            ->expectsQuestion('What would you like to do?', 'View a flashcard')
            ->expectsQuestion('Enter the ID of the flashcard', 'abc')
            ->expectsOutput('The id field must be an integer.')
            ->expectsQuestion('What would you like to do?', 'Exit')
            ->expectsOutput('Goodbye!')
            ->assertExitCode(0);
    }

    public function test_flashcard_not_found(): void
    {
        $this->artisan('flashcard:interactive')
            ->expectsQuestion('Enter your user name', $this->user->name)
            ->expectsQuestion('Enter your password', 'password')
            ->expectsQuestion('What would you like to do?', 'View a flashcard')
            ->expectsQuestion('Enter the ID of the flashcard', '999')
            ->expectsOutput('Flashcard not found.')
            ->expectsQuestion('What would you like to do?', 'Exit')
            ->expectsOutput('Goodbye!')
            ->assertExitCode(0);
    }

    public function test_permanent_delete_confirmation(): void
    {
        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);
        $this->artisan('flashcard:interactive')
            ->expectsQuestion('Enter your user name', $this->user->name)
            ->expectsQuestion('Enter your password', 'password')
            ->expectsQuestion('What would you like to do?', 'Delete a flashcard')
            ->expectsQuestion('Enter the ID of the flashcard', $flashcard->id)
            ->expectsConfirmation("Are you sure you want to delete flashcard $flashcard->id with question: $flashcard->question?", 'yes')
            ->expectsOutput('Flashcard deleted successfully.')
            ->expectsQuestion('What would you like to do?', 'Delete a flashcard')
            ->expectsQuestion('Enter the ID of the flashcard', $flashcard->id)
            ->expectsConfirmation("Are you sure you want to PERMANENTLY delete flashcard $flashcard->id with question: $flashcard->question? This action cannot be undone.", 'yes')
            ->expectsOutput('Flashcard permanently deleted.')
            ->expectsQuestion('What would you like to do?', 'Exit')
            ->expectsOutput('Goodbye!')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('flashcards', ['id' => $flashcard->id]);
    }

    public function test_revert_flashcard(): void
    {
        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id, 'question' => 'Original Question', 'answer' => 'Original Answer']);
        $flashcard->update(['question' => 'Updated Question', 'answer' => 'Updated Answer']);
        $audit = $flashcard->audits()->latest()->first();
        $flashcardService = new FlashcardService;
        $flashcardService->setUser($this->user);

        $this->artisan('flashcard:interactive')
            ->expectsQuestion('Enter your user name', $this->user->name)
            ->expectsQuestion('Enter your password', 'password')
            ->expectsQuestion('What would you like to do?', 'Revert a flashcard')
            ->expectsQuestion('Enter the ID of the flashcard', $flashcard->id)
            ->expectsTable(
                ['ID', 'Question', 'Answer', 'Status', 'Deleted'],
                collect($flashcardService->getFlashcardHistory($flashcard))->map(fn ($item) => [
                    'ID' => $item['audit_id'],
                    'Question' => $item['question'],
                    'Answer' => $item['answer'],
                    'Status' => $item['status'],
                    'Deleted' => $item['deleted'],
                ])->toArray()
            )
            ->expectsQuestion('Enter the ID of the state you want to revert to (or type "exit" to cancel)', $audit->id)
            ->expectsOutput("Flashcard #$flashcard->id reverted to state #$audit->id successfully.")
            ->expectsQuestion('What would you like to do?', 'Exit')
            ->expectsOutput('Goodbye!')
            ->assertExitCode(0);

        $this->assertDatabaseHas('flashcards', [
            'id' => $flashcard->id,
            'question' => 'Original Question',
            'answer' => 'Original Answer',
        ]);
    }

    public function test_no_flashcards_to_practice(): void
    {
        $this->artisan('flashcard:interactive')
            ->expectsQuestion('Enter your user name', $this->user->name)
            ->expectsQuestion('Enter your password', 'password')
            ->expectsQuestion('What would you like to do?', 'Practice')
            ->expectsOutput('You have no flashcards to practice.')
            ->expectsQuestion('What would you like to do?', 'Exit')
            ->expectsOutput('Goodbye!')
            ->assertExitCode(0);
    }

    public function test_invalid_action_input(): void
    {
        $this->artisan('flashcard:interactive')
            ->expectsQuestion('Enter your user name', $this->user->name)
            ->expectsQuestion('Enter your password', 'password')
            ->expectsQuestion('What would you like to do?', 'Invalid Action')
            ->expectsOutput('Invalid action.')
            ->expectsQuestion('What would you like to do?', 'Exit')
            ->expectsOutput('Goodbye!')
            ->assertExitCode(0);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFaker();
        $this->user = User::factory()->create();
    }
}
