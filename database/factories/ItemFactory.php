<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    private static $items = [
        'パソコン',
        'プリンター',
        'コピー機',
        'ステープラー',
        'ホッチキス',
        'ペン',
        'ノート',
        '付箋',
        'ファイル',
        'クリップ',
        '電卓',
        'デスクライト',
        'シュレッダー',
        'ホワイトボード',
        'マーカー',
        '名刺ケース',
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
            'is_set' => fake()->boolean(),
            'limits' => fake()->numberBetween(1, 15),
        ];
    }
}
