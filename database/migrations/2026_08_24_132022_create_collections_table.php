<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // coleções para agrupamento de produtos
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45);
            $table->string('slug', 45)->unique();
            $table->string('image_url', 500);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};