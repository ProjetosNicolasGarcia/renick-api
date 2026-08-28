<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // entidade principal do produto
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('collection_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('gender', ['masculino', 'feminino', 'bebes', 'unissex'])->default('unissex');
            $table->string('name', 255);
            $table->string('slug', 150)->unique();
            $table->text('description');
            $table->decimal('rating_average', 3, 2)->unsigned()->nullable();
            $table->unsignedInteger('total_review')->default(0);
            $table->timestamps();
            // coluna is_active para a query do ProductService
            $table->boolean('is_active')->default(true);
            $table->fullText(['name', 'description'], 'idx_product_search');
            $table->index('created_at', 'idx_product_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};