<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->createProduct(
            name: 'The Original Black Jean',
            slug: 'the-original-black-jean',
            description: 'A straight-fit black jean built to hold its colour wash after wash.',
            story: 'Cut and sewn in Vietnam from a heavyweight stretch denim, developed to resist fading through repeated washing — so it looks as good six months in as it did on day one.',
            fit: 'Regular Straight',
            composition: '99% Cotton, 1% Elastane',
            weight_oz: 13.5,
            weave: '3x1 Right-Hand Twill',
            origin: 'Woven and sewn in Vietnam',
            base_price: 149.00,
        );

        $this->createProduct(
            name: 'The Raw Selvedge',
            slug: 'the-raw-selvedge',
            description: 'Unwashed, rigid black denim for a fade that\'s entirely your own.',
            story: 'Woven on narrow shuttle looms for a genuine selvedge edge, left raw and unwashed — this pair will fade to charcoal exactly where you crease it, not where a factory wash decided to.',
            fit: 'Relaxed Straight',
            composition: '100% Cotton',
            weight_oz: 16.0,
            weave: '3x1 Right-Hand Twill, Selvedge',
            origin: 'Woven and sewn in Vietnam',
            base_price: 189.00,
        );

        $this->createProduct(
            name: 'The Everyday Black',
            slug: 'the-everyday-black',
            description: 'A lighter, more flexible black jean for daily wear.',
            story: 'A touch of stretch and a lighter hand-feel than our heavyweight styles — built for people who want black denim they can wear five days a week without it wearing them out.',
            fit: 'Regular Straight',
            composition: '98% Cotton, 2% Elastane',
            weight_oz: 11.5,
            weave: '3x1 Right-Hand Twill',
            origin: 'Woven and sewn in Vietnam',
            base_price: 129.00,
        );
    }

    private function createProduct(
        string $name, string $slug, string $description, string $story,
        string $fit, string $composition, float $weight_oz, string $weave,
        string $origin, float $base_price,
    ): void {
        $product = Product::create([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'story' => $story,
            'fit' => $fit,
            'composition' => $composition,
            'weight_oz' => $weight_oz,
            'weave' => $weave,
            'origin' => $origin,
            'base_price' => $base_price,
            'status' => 'active',
        ]);

        foreach (['28', '30', '32', '34', '36'] as $size) {
            ProductVariant::create([
                'product_id' => $product->id,
                'size' => $size,
                'inseam' => '32L',
                'sku' => strtoupper(str($slug)->slug()).'-'.$size.'-32L',
                'stock_quantity' => 25,
            ]);
        }
    }
}
