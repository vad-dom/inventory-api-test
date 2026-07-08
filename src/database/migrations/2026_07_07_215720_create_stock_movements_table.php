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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('product_id');

            $table->unsignedInteger('source_warehouse_id')->nullable();
            $table->unsignedInteger('target_warehouse_id')->nullable();

            $table->string('type', 32);

            $table->unsignedInteger('quantity');

            $table->text('comment')->nullable();

            $table->timestamps();

            $table->index('product_id');
            $table->index('source_warehouse_id');
            $table->index('target_warehouse_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
