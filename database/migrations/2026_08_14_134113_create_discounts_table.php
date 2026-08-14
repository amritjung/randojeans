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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // stored uppercase for consistent matching
            $table->decimal('percentage', 5, 2); // e.g. 10.00 = 10% off
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable(); // null = never expires
            $table->unsignedInteger('max_uses')->nullable(); // null = unlimited overall uses
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
