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
            $table->string('fit')->nullable()->after('story'); // e.g. "Relaxed Straight"
            $table->string('composition')->nullable()->after('fit'); // e.g. "100% Cotton"
            $table->decimal('weight_oz', 4, 1)->nullable()->after('composition'); // fabric weight, oz/yd²
            $table->string('weave')->nullable()->after('weight_oz'); // e.g. "3x1 Right-Hand Twill"
            $table->string('origin')->nullable()->after('weave'); // e.g. "Woven and sewn in Vietnam"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['fit', 'composition', 'weight_oz', 'weave', 'origin']);
        });
    }
};
