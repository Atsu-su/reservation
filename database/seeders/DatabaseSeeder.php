<?php

namespace Database\Seeders;

use App\Models\Administrator;
use App\Models\Inventory;
use App\Models\Role;
use App\Models\ReservationItem;
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
        // 不要（ReservationFactory.php で自動で作成される）
        // ReservationItem::factory(10)->create();
        // Item::factory(10)->create();

        // User::factory(10)->create();
        // Role::factory()->create(['role_name' => 'admin']);
        // Role::factory()->create(['role_name' => 'user']);
        // Administrator::factory(10)->create();
        // Inventory::factory(ItemFactory::getLength())->create();

        // 先にReservationを作った方がいいかも
        // ReservationItem::factory(100)->create();

        User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
