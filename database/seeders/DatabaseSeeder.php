<?php

namespace Database\Seeders;

use App\Models\Administrator;
use App\Models\Item;
use App\Models\LendingAggregate;
use App\Models\Role;
use App\Models\ReservationItem;
use App\Models\Reservation;
use App\Models\User;
use Database\Factories\ItemFactory;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{

    const ITEMS_PER_USER = 3;

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
        if (! Item::first()) Item::factory($itemLength)->create();

        // reservationsテーブル
        if (! Reservation::first()) {
            $users = User::count();

            for ($i = 1; $i <= $users; $i++) {
                $user = User::find($i);
                for ($j = 1; $j <= self::ITEMS_PER_USER; $j++) {
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
                            'amount' => rand(1, 5),
                        ]
                    );
                }
            }
        }

        // lending_aggregatesテーブル
        if (! LendingAggregate::first()) {
            // 集計データを取得
            $aggregates = DB::table('reservation_items as ri')
            ->join('reservations as r', 'ri.reservation_id', '=', 'r.id')
            ->groupBy('ri.item_id', 'r.borrowing_start_date')
            ->select('r.borrowing_start_date', 'ri.item_id',  DB::raw("sum(ri.amount) as total_amount"))
            ->orderBy('r.borrowing_start_date')
            ->orderBy('ri.item_id')
            ->get();

            // 集計データをlending_aggregatesテーブルに挿入
            foreach ($aggregates as $aggregate) {
                LendingAggregate::create(
                    [
                        'borrowing_start_date' => $aggregate->borrowing_start_date,
                        'item_id' => $aggregate->item_id,
                        'total_amount' => $aggregate->total_amount,
                    ]
                );
            }

        }
    }
}