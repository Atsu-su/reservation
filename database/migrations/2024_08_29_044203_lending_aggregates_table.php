]<?php

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
        Schema::create('lending_aggregates', function (Blueprint $table) {
            $table->id();
            $table->date('borrowing_start_date');
            $table->foreignIdFor(Item::class)->constrained();
            $table->unsignedSmallInteger('total_amount');
            $table->timestamps();

            // 複合キーの設定
            $table->unique(['borrowing_start_date', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lending_aggregates');
    }
};
