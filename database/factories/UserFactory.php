<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Modules\Auth\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'status' => UserStatus::Active->value,
            'password' => 'password',
            'remember_token' => Str::random(10),
        ];
    }
}
