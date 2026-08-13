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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Links to the specific purchased line item — this is what
            // actually proves "verified purchase," not just a checkbox.
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->string('title')->nullable();
            $table->text('body');
            $table->string('status')->default('pending'); // pending | approved | rejected
            $table->timestamps();
    
            // One review per product per customer — standard practice, and
            // also means a review can be edited/replaced rather than
            // someone leaving five separate reviews for the same jean.
            $table->unique(['product_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
