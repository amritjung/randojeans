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
            // Staff-entered explanation shown to the customer when a return
            // is rejected — e.g. "Outside the 30-day window" or "Item shows
            // signs of wear beyond normal try-on." Optional, but a rejection
            // with no reason at all is a poor customer experience.
            $table->text('admin_response')->nullable()->after('notes');
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
