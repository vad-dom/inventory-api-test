<?php

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
        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('product_id');
            $table->unsignedInteger('warehouse_id');

            $table->unsignedInteger('quantity')->default(0);

            $table->timestamps();

            $table->index('product_id');
            $table->index('warehouse_id');

            $table->unique([
                'product_id',
                'warehouse_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_balances');
    }
};
