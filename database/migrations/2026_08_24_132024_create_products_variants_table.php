<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // controle de sku, estoque e preço por variação
        Schema::create('products_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 100)->unique();
            $table->string('size', 10);
            $table->string('color_name', 45);
            $table->char('color_hex', 7);
            $table->decimal('price', 10, 2)->unsigned();
            $table->decimal('promo_price', 10, 2)->unsigned()->nullable();
            $table->timestamp('promo_start')->nullable();
            $table->timestamp('promo_end')->nullable();
            $table->unsignedSmallInteger('stock_quantity');
            $table->decimal('weight_kg', 5, 3)->unsigned();
            $table->unsignedTinyInteger('length_cm');
            $table->unsignedTinyInteger('width_cm');
            $table->unsignedTinyInteger('height_cm');
            $table->timestamps();
            
            $table->index(['price', 'promo_price'], 'idx_variants_price');
            $table->index(['size', 'color_name'], 'idx_variants_attributes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products_variants');
    }
};