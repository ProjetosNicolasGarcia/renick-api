<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // remove campos sensíveis e adiciona login social
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['name', 'email_verified_at', 'remember_token']);
            $table->string('password')->nullable()->change();
            $table->string('google_id')->nullable()->unique()->after('password');
        });
    }

    public function down(): void
    {
        // reverte a estrutura para o padrão do framework
        Schema::table('users', function (Blueprint $table) {
            $table->string('name');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->dropColumn('google_id');
        });
    }
};