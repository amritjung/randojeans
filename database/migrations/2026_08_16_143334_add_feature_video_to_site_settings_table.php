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
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('feature_video')->nullable()->after('feature_image');
            // Optional overlay text shown on top of the video — editable
            // from admin so you can change the message without touching code.
            $table->string('video_heading')->nullable()->after('feature_video');
            $table->text('video_subtext')->nullable()->after('video_heading');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            //
        });
    }
};
