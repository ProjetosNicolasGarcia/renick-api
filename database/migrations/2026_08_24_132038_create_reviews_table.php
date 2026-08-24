<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // registro de avaliações atrelado ao usuário, produto e pedido específico
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->string('name', 100);
            $table->unsignedTinyInteger('rating');
            $table->string('comment', 1000);
            $table->json('photos')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'product_id', 'order_id'], 'uq_user_product_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};