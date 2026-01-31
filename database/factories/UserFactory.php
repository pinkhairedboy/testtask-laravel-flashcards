<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    private static int $num = 0;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => 'user_'.static::$num++,
            'password' => static::$password ??= Hash::make('password'),
        ];
    }
}
