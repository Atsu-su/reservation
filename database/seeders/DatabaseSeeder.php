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
        if (! User::first()) User::factory(10)->create();

        // rolesテーブル
        if (! Role::first()) {
            foreach (['admin', 'user'] as $role) {
                Role::create(['role_name' => $role]);
            }
        }

        // administratorsテーブル
        if (! Administrator::first()) Administrator::factory(5)->create();

        // inventoriesテーブル
        if (! Inventory::first()) Inventory::factory($itemLength)->create();

        // reservationsテーブル
        if (! Reservation::first()) {
            $users = User::count();

            for ($i = 1; $i <= $users; $i++) {
                $user = User::find($i);
                for ($j = 1; $j <= rand(1,3); $j++) {
                    $reservation = $user->reservation();
                    $reservation->create(
                        [
                            // borrowing_start_dateとuser_idが複合キー
                            'user_id' => $user->id,
                            'borrowing_start_date' => now()->add((3 + $j), 'day')->format('Y-m-d'),

                            'reservation_date' => now()->add($j, 'day')->format('Y-m-d'),                        
                            'return_date' => now()->add((6 + $j), 'day')->format('Y-m-d'),
                            'status' => 0,
                        ]
                    );
                }
            }
        }

        // reservation_itemsテーブル
        if (! ReservationItem::first()) {
            $reservations = Reservation::count();

            for ($i = 1; $i <= $reservations; $i++) {
                $reservation = Reservation::find($i);
                for ($j = 1; $j <= 3; $j++) {
                    $reservationItem = $reservation->reservationItem();
                    $reservationItem->create(
                        [
                            'reservation_id' => $reservation->id,
                            'item_id' => rand(1, $itemLength),
                            'amount' => rand(1, 10),
                        ]
                    );
                }
            }
        }
    }
}
