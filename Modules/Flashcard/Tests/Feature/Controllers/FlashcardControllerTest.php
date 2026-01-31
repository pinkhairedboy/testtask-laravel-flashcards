<?php

namespace Modules\Flashcard\Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Modules\Flashcard\Models\Flashcard;

class FlashcardControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFaker();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('TestToken')->plainTextToken;
    }

    public function test_index(): void
    {
        Flashcard::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this
            ->actingAs($this->user, 'api')
            ->withHeaders([
                'Authorization' => 'Bearer '.$this->token,
            ])
            ->get(route('api.flashcards.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'question',
                        'answer',
                        'status',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_store(): void
    {
        $question = $this->faker->sentence;
        $answer = $this->faker->sentence;

        $response = $this
            ->actingAs($this->user, 'api')
            ->withHeaders([
                'Authorization' => 'Bearer '.$this->token,
            ])
            ->postJson(route('api.flashcards.store'), [
                'question' => $question,
                'answer' => $answer,
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'question',
                    'answer',
                    'status',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'data' => [
                    'question' => $question,
                    'answer' => $answer,
                    'status' => 'Not Answered',
                ],
            ]);

        $this->assertDatabaseHas('flashcards', [
            'question' => $question,
            'answer' => $answer,
            'status' => 'Not Answered',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_show(): void
    {
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ])->refresh();

        $response = $this
            ->actingAs($this->user, 'api')
            ->withHeaders([
                'Authorization' => 'Bearer '.$this->token,
            ])
            ->getJson(route('api.flashcards.show', ['id' => $flashcard->id]));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'question',
                    'answer',
                    'status',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $flashcard->id,
                    'question' => $flashcard->question,
                    'answer' => $flashcard->answer,
                    'status' => $flashcard->status,
                ],
            ]);
    }

    public function test_update(): void
    {
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $newQuestion = $this->faker->sentence;
        $newAnswer = $this->faker->sentence;

        $response = $this
            ->actingAs($this->user, 'api')
            ->withHeaders([
                'Authorization' => 'Bearer '.$this->token,
            ])
            ->patchJson(route('api.flashcards.update', ['id' => $flashcard->id]), [
                'question' => $newQuestion,
                'answer' => $newAnswer,
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'question',
                    'answer',
                    'status',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $flashcard->id,
                    'question' => $newQuestion,
                    'answer' => $newAnswer,
                    'status' => $flashcard->status,
                ],
            ]);

        $this->assertDatabaseHas('flashcards', [
            'id' => $flashcard->id,
            'question' => $newQuestion,
            'answer' => $newAnswer,
        ]);
    }

    public function test_destroy(): void
    {
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this
            ->actingAs($this->user, 'api')
            ->withHeaders([
                'Authorization' => 'Bearer '.$this->token,
            ])
            ->delete(route('api.flashcards.destroy', ['id' => $flashcard->id]));

        $response->assertOk()
            ->assertJson(['message' => 'Flashcard deleted successfully']);

        $this->assertSoftDeleted('flashcards', [
            'id' => $flashcard->id,
        ]);
    }

    public function test_get_statistics(): void
    {
        Flashcard::factory()->count(5)->create(['status' => 'Not Answered', 'user_id' => $this->user->id]);
        Flashcard::factory()->count(3)->create(['status' => 'Correct', 'user_id' => $this->user->id]);
        Flashcard::factory()->count(2)->create(['status' => 'Incorrect', 'user_id' => $this->user->id]);

        $response = $this
            ->actingAs($this->user, 'api')
            ->withHeaders([
                'Authorization' => 'Bearer '.$this->token,
            ])
            ->get(route('api.flashcards.statistics'));

        $response->assertOk()
            ->assertJsonStructure([
                'total_questions',
                'percentage_answered',
                'percentage_correct',
            ])
            ->assertJson([
                'total_questions' => 10,
                'percentage_answered' => 50,
                'percentage_correct' => 30,
            ]);
    }

    public function test_reset_progress(): void
    {
        Flashcard::factory()->count(3)->create(['status' => 'Correct', 'user_id' => $this->user->id]);
        Flashcard::factory()->count(2)->create(['status' => 'Incorrect', 'user_id' => $this->user->id]);

        $response = $this
            ->actingAs($this->user, 'api')
            ->withHeaders([
                'Authorization' => 'Bearer '.$this->token,
            ])
            ->postJson(route('api.flashcards.reset'));

        $response->assertOk()
            ->assertJson(['message' => 'Flashcard progress reset successfully']);

        $this->assertDatabaseHas('flashcards', [
            'status' => 'Not Answered',
        ]);
    }

    public function test_restore(): void
    {
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this
            ->actingAs($this->user, 'api')
            ->withHeaders([
                'Authorization' => 'Bearer '.$this->token,
            ])
            ->delete(route('api.flashcards.destroy', ['id' => $flashcard->id]));

        $response = $this
            ->actingAs($this->user, 'api')
            ->withHeaders([
                'Authorization' => 'Bearer '.$this->token,
            ])
            ->postJson(route('api.flashcards.restore', ['id' => $flashcard->id]));

        $response->assertOk()
            ->assertJson(['message' => 'Flashcard restored successfully']);

        $this->assertDatabaseHas('flashcards', [
            'id' => $flashcard->id,
            'deleted_at' => null,
        ]);
    }

    public function test_restore_not_deleted(): void
    {
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this
            ->actingAs($this->user, 'api')
            ->withHeaders([
                'Authorization' => 'Bearer '.$this->token,
            ])
            ->postJson(route('api.flashcards.restore', ['id' => $flashcard->id]));

        $response->assertStatus(422)
            ->assertJson(['message' => 'Flashcard is not deleted']);
    }

    public function test_get_history(): void
    {
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $flashcard->update(['question' => 'New Question 1']);
        $flashcard->update(['answer' => 'New Answer 2']);
        $flashcard->update(['status' => 'Correct']);

        $response = $this
            ->actingAs($this->user, 'api')
            ->withHeaders([
                'Authorization' => 'Bearer '.$this->token,
            ])
            ->getJson(route('api.flashcards.history', ['id' => $flashcard->id]));

        $response->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'audit_id',
                    'question',
                    'answer',
                    'status',
                    'deleted',
                ],
            ]);
    }

    public function test_revert(): void
    {
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $initialFlashcard = $flashcard->toArray();

        $flashcard->update(['question' => 'New Question 1']);

        $audits = $flashcard->audits()->latest()->get();

        $response = $this
            ->actingAs($this->user, 'api')
            ->withHeaders([
                'Authorization' => 'Bearer '.$this->token,
            ])
            ->postJson(route('api.flashcards.revert', ['id' => $flashcard->id]), [
                'audit_id' => $audits->first()->id,
            ]);

        $response->assertOk()
            ->assertJson(['message' => "Flashcard #{$flashcard->id} reverted to state #{$audits->first()->id} successfully."]);

        $this->assertDatabaseHas('flashcards', [
            'id' => $initialFlashcard['id'],
            'question' => $initialFlashcard['question'],
            'answer' => $initialFlashcard['answer'],
            'status' => $initialFlashcard['status'],
        ]);
    }
}
