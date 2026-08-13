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
        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reason'); // dropdown-selected reason, e.g. "Wrong size"
            $table->text('notes')->nullable(); // customer's free-text explanation
            // pending: awaiting staff review
            // approved: staff approved, waiting for item to physically arrive back
            // rejected: staff declined the request
            // refunded: item received and refund processed
            $table->string('status')->default('pending');
            $table->decimal('refund_amount', 8, 2)->nullable(); // set when refund is processed
            $table->string('stripe_refund_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_requests');
    }
};
