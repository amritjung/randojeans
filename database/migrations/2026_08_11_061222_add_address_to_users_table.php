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
        Schema::table('users', function (Blueprint $table) {
            // Single text field to match how shipping_address is already
            // stored on orders (one free-text field rather than separate
            // street/suburb/state/postcode columns) — keeps the data shape
            // consistent between "saved profile address" and "order shipping
            // address" so pre-filling checkout from it is a straight copy.
            $table->text('address')->nullable()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
