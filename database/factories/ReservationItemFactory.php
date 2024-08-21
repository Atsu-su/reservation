<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReservationItem>
 */
class ReservationItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 'reservation_id' => Reservation::factory(),
            // 'item_id' => fake()->randomElement(Item::pluck('id')->toArray()),
            // 'amount' => rand(1, 5),
        ];
    }
}
