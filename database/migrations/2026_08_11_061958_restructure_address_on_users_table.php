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
            // Drop the single free-text 'address' column we added last step —
            // replacing it with proper structured Australian address fields
            // instead, which lets us validate postcode format, offer a fixed
            // state dropdown, and format addresses consistently everywhere
            // they're displayed (order admin, shipping labels, etc).
            $table->dropColumn('address');
    
            $table->string('address_street')->nullable()->after('phone');
            $table->string('address_suburb')->nullable()->after('address_street');
            $table->string('address_state', 3)->nullable()->after('address_suburb'); // e.g. "NSW"
            $table->string('address_postcode', 4)->nullable()->after('address_state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['address_street', 'address_suburb', 'address_state', 'address_postcode']);
            $table->text('address')->nullable();
        });
    }
};
