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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_street')->nullable()->after('shipping_name');
            $table->string('shipping_suburb')->nullable()->after('shipping_street');
            $table->string('shipping_state', 3)->nullable()->after('shipping_suburb');
            $table->string('shipping_postcode', 4)->nullable()->after('shipping_state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
