<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('art_number')->nullable()->after('sale_price');
            $table->text('model_note')->nullable()->after('art_number');
            $table->boolean('low_in_stock')->default(false)->after('model_note');
            $table->string('shipping_carrier')->nullable()->after('low_in_stock');
            $table->string('shipping_note')->nullable()->after('shipping_carrier');
            $table->unsignedInteger('returns_days')->default(60)->after('shipping_note');
            $table->json('colors')->nullable()->after('returns_days');
            $table->json('size_groups')->nullable()->after('colors');
            $table->json('washing_instructions')->nullable()->after('size_groups');
            $table->string('size_fit_tag')->nullable()->after('washing_instructions');
            $table->json('size_fit_chips')->nullable()->after('size_fit_tag');
            $table->longText('size_fit_body')->nullable()->after('size_fit_chips');
            $table->string('description_tag')->nullable()->after('size_fit_body');
            $table->longText('description_body')->nullable()->after('description_tag');
            $table->json('description_bullets')->nullable()->after('description_body');
            $table->string('care_instructions')->nullable()->after('description_bullets');
            $table->string('sustainability_tag')->nullable()->after('care_instructions');
            $table->string('sustainability_message')->nullable()->after('sustainability_tag');
            $table->json('manufacturing_origins')->nullable()->after('sustainability_message');
            $table->json('custom_sections')->nullable()->after('manufacturing_origins');
            $table->json('section_order')->nullable()->after('custom_sections');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'art_number', 'model_note', 'low_in_stock',
                'shipping_carrier', 'shipping_note', 'returns_days',
                'colors', 'size_groups', 'washing_instructions',
                'size_fit_tag', 'size_fit_chips', 'size_fit_body',
                'description_tag', 'description_body', 'description_bullets',
                'care_instructions', 'sustainability_tag', 'sustainability_message',
                'manufacturing_origins', 'custom_sections', 'section_order',
            ]);
        });
    }
};