<?php

namespace Modules\Flashcard\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Flashcard\Models\Flashcard;

class FlashcardDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::all()->each(function ($user) {
            Flashcard::factory(4)->create(['user_id' => $user->id]);
        });
    }
}
