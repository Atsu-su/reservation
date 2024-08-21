<?php

namespace Database\Seeders;

use App\Models\Administrator;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Role;
use App\Models\ReservationItem;
use App\Models\Reservation;
use App\Models\User;
use Database\Factories\ItemFactory;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $itemLength = ItemFactory::getLength();


        // usersテーブル
        User::factory(10)->create();

        // rolesテーブル
        foreach (['admin', 'user'] as $role) {
            Role::create(['role_name' => $role]);
        }

        // administratorsテーブル
        Administrator::factory(5)->create();

        // itemsテーブル
        // Item::factory($itemLength)->create();

        // inventoriesテーブル
        Inventory::factory($itemLength)->create();

        // reservationsテーブル
        // reservation_itemsテーブル
        $users = User::count();

        for ($i = 1; $i <= $users; $i++) {
            $user = User::find($i);
            for ($j = 1; $j <= rand(1,3); $j++) {
                $reservation = $user->reservation();
                $reservation->create(
                    [
                        'user_id' => $user->id,
                        'reservation_date' => now()->add($j, 'day')->format('Y-m-d'),
                        
                        // borrowing_start_dateとuser_idが複合キー
                        'borrowing_start_date' => now()->add((3 + $j), 'day')->format('Y-m-d'),
                        'return_date' => now()->add((6 + $j), 'day')->format('Y-m-d'),
                        'status' => 0,
                    ]
                );

                // ここから
                // forの外においてreservationsの数を取得して回す

                $reservation2 = Reservation::find($j);
                // 3件ずつreservation_itemsテーブルにデータを挿入
                for ($k = 1; $k <= 3; $k++) {
                    $reservation2->reservationItem()->create(
                        [
                            'reservation_id' => $reservation2->id,
                            'item_id' => rand(1, $itemLength),
                            'amount' => rand(1, 5),
                        ]
                    );
                }
            }
        }
    }
}
