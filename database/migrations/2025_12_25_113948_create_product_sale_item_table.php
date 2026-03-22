<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_sale_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_sale_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('price_sale', 15, 2);
            $table->integer('qty')->default(1);
            $table->timestamps();

            // Foreign keys
            $table->foreign('product_sale_id')
                ->references('id')
                ->on('product_sale')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_sale_items');
    }
};

