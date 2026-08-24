<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // registro estático da transação e dados do cliente no ato da compra
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('order_number', 20)->unique();
            $table->string('status', 20);
            $table->char('customer_cpf', 11);
            $table->string('customer_phone', 20);
            $table->string('customer_name', 100);
            $table->char('shipping_zip_code', 8);
            $table->char('shipping_state', 2);
            $table->string('shipping_street', 100);
            $table->string('shipping_number', 20);
            $table->string('shipping_complement', 100)->nullable();
            $table->string('shipping_neighborhood', 100);
            $table->decimal('shipping_fee', 10, 2)->unsigned();
            $table->decimal('coupon_discount_amount', 10, 2)->unsigned()->default(0);
            $table->decimal('subtotal', 10, 2)->unsigned();
            $table->decimal('total', 10, 2)->unsigned();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at'], 'idx_user_created');
            $table->index(['user_id', 'status'], 'idx_user_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};