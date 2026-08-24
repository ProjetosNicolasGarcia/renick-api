<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // galeria de imagens associada a uma cor específica
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('color_slug', 45)->nullable();
            $table->string('image_url', 500);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['product_id', 'color_slug', 'sort_order'], 'product_color_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};