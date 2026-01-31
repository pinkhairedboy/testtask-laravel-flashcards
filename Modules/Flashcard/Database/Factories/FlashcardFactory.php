<?php

namespace Modules\Flashcard\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Flashcard\Models\Flashcard;

class FlashcardFactory extends Factory
{
    protected $model = Flashcard::class;

    public function definition(): array
    {
        $letter = $this->faker->randomLetter();

        return [
            'question' => 'The correct answer for this question is '.$letter,
            'answer' => $letter,
        ];
    }
}
