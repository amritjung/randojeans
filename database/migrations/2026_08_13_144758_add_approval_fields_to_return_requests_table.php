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
        Schema::table('return_requests', function (Blueprint $table) {
            // Short customer-facing reference (e.g. "RR-A3F9K2") — written on
            // the outside of the returned parcel so staff can match it to
            // this request without opening the package first.
            $table->string('reference_number')->unique()->nullable()->after('id');
            // Stamped automatically when status changes to 'approved',
            // same pattern as shipped_at/delivered_at on Order.
            $table->timestamp('approved_at')->nullable()->after('admin_response');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('return_requests', function (Blueprint $table) {
            //
        });
    }
};
