<?php

use App\Models\User;
use App\Models\Item;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained();
            $table->date('reservation_date');
            $table->date('borrowing_start_date');
            $table->date('return_date')->nullable(); // 返却後にnullではなくなる
            $table->tinyInteger('status')->unsigned()->comment('0: 未貸出, 1: 貸出中, 2: 返却済み');
            $table->timestamps();

            // 複号キーの設定
            $table->unique(['user_id', 'borrowing_start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
