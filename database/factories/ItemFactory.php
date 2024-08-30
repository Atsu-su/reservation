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

    private static $index = 0;

    public static function getLength(): int
    {
        return count(self::$items);
    }

    public static function resetIndex(): void
    {
        self::$index = 0;
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $item = self::$items[self::$index];
        self::$index = (self::$index + 1);

        return [
            'name' => $item,
            // 'is_set' => fake()->boolean(),
            'stock_amount' => fake()->numberBetween(10, 30),
            'limit' => self::$index,
        ];
    }
}
