<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // tabela de hierarquia de categorias
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name', 100)->nullable();
            $table->string('slug', 100)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};