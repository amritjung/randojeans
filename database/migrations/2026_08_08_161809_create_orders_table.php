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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('shipping_name');
            $table->text('shipping_address');
            $table->string('status')->default('pending'); // pending|paid|fulfilled|shipped|delivered|cancelled
            $table->decimal('subtotal', 8, 2);
            $table->decimal('shipping_cost', 8, 2)->default(0);
            $table->decimal('gst', 8, 2); // 10% GST component, itemised for AU invoicing
            $table->decimal('total', 8, 2);
            $table->string('stripe_payment_intent_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
