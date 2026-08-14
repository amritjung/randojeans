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
        Schema::table('products', function (Blueprint $table) {
            // Nullable — null means "not on sale, use base_price as normal."
            // When set, it should be LOWER than base_price (enforced in the
            // admin form below), and becomes the actual selling price.
            $table->decimal('sale_price', 8, 2)->nullable()->after('base_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
