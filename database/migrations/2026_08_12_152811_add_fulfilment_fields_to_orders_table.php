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
            $table->string('carrier')->nullable()->after('status'); // e.g. "Australia Post"
            $table->string('tracking_number')->nullable()->after('carrier');
            // Recorded automatically when status changes to shipped/delivered
            // (via a model event below), rather than manually typed in — so
            // these timestamps are always accurate to when the status
            // actually changed, not when someone remembered to fill them in.
            $table->timestamp('shipped_at')->nullable()->after('tracking_number');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');
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
