<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    private static $items = [
        '野球ボール',
        'テニスボール',
        'ネット一式',
        'ビブス',
        'テニスラケット',
        'バトミントンラケット',
        'バスケットボール',
        'バレーボール',
        'サッカーボール',
        'フットサルボール',
        'ハンドボール',
        'ラグビーボール',
        'ボールポンプ',
        'コーン',
    ];

    public static function getLength(): int
    {
        return count(self::$items);
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(self::$items),
            'quantity' => fake()->numberBetween(1, 15),
            'is_set' => fake()->boolean(30),
        ];
    }
}
