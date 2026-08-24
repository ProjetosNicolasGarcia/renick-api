<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // itens transitórios no carrinho do usuário
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('products_variants')->restrictOnDelete();
            $table->unsignedTinyInteger('quantity');
            $table->timestamps();
            
            $table->unique(['cart_id', 'product_variant_id'], 'uq_cart_variant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};