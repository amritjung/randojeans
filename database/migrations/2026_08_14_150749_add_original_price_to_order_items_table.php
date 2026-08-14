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
        Schema::table('order_items', function (Blueprint $table) {
            // Snapshots the product's normal base_price at checkout time,
            // regardless of whether it was on sale. Comparing this against
            // unit_price later is what lets us show "was $149, now $119"
            // in historical records forever, even after a sale ends.
            $table->decimal('original_price', 8, 2)->nullable()->after('unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            //
        });
    }
};
