<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Item;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('inventories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->foreignIdFor(Item::class)->constrained();
            $table->smallInteger('stock_amount')->unsigned();
            $table->timestamps();
        });
    }
};
