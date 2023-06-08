<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pricing_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('order_id')->unique();
            $table->string('payment_type');
            $table->string('transaction_status');
            $table->string('pricing_type');
            $table->integer('pricing_duration_in_days');
            $table->decimal('pricing_price');
            $table->decimal('gross_amount');
            $table->integer('pricing_discount');
            $table->string('payment_number');
            $table->dateTime('transaction_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_orders');
    }
};
