<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Administrator>
 */
class AdministratorFactory extends Factory
{
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'role_id' => fake()->randomElement([1, 2]),
            'password' => static::$password ??= Hash::make('password'),
        ];
    }

    // public function configure()
    // {
    //     return $this->sequence(function ($sequence) {
    //         return [
    //             'id' => $sequence->index + 24001,
    //         ];
    //     });
    // }
}
