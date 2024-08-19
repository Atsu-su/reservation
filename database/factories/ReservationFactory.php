<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        $day = rand(1, 14);
        $hour = rand(1, 6);
        $reservationDate = fake()->dateTimeBetween('-14day', 'now');
        $borrowingStartDate = (clone $reservationDate)->modify("+{$day} day");
        $random = fake()->randomElement([0, 1, 2]);
        $returnData = $random === 2 ? (clone $reservationDate)->modify("+{$day} day {$hour}hour") : null;

        return [
            'user_id' => fake()->randomElement(User::pluck('id')->toArray()),
            'reservation_date' => $reservationDate->format('Y-m-d H:i:s'),
            'borrowing_start_date' => $borrowingStartDate->format('Y-m-d H:i:s'),
            'return_date' => $returnData ? $returnData->format('Y-m-d H:i:s') : $returnData,
            'status' => $random,
        ];
    }
}
