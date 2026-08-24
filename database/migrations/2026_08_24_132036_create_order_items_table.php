<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // espelho da variante comprada para imutabilidade do pedido
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('products_variants')->restrictOnDelete();
            $table->string('sku', 100);
            $table->string('product_name', 255);
            $table->string('variant_color', 45);
            $table->string('variant_size', 10);
            $table->unsignedTinyInteger('quantity');
            $table->decimal('unit_price', 10, 2)->unsigned();
            $table->decimal('total_price', 10, 2)->unsigned();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};