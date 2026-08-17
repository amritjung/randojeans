<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\OrderItem;

class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'story', 'fit', 'composition',
        'weight_oz', 'weave', 'origin', 'base_price', 'status', 'sale_price',
        'art_number', 'model_note', 'low_in_stock',
        'shipping_carrier', 'shipping_note', 'returns_days',
        'colors', 'size_groups', 'washing_instructions',
        'size_fit_tag', 'size_fit_chips', 'size_fit_body',
        'description_tag', 'description_body', 'description_bullets',
        'care_instructions', 'sustainability_tag', 'sustainability_message',
        'manufacturing_origins', 'custom_sections', 'section_order',
    ];

    protected function casts(): array
    {
        return [
            'base_price'            => 'decimal:2',
            'sale_price'            => 'decimal:2',
            'low_in_stock'          => 'boolean',
            'colors'                => 'array',
            'size_groups'           => 'array',
            'washing_instructions'  => 'array',
            'size_fit_chips'        => 'array',
            'description_bullets'   => 'array',
            'manufacturing_origins' => 'array',
            'custom_sections'       => 'array',
            'section_order'         => 'array',
        ];
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): ?ProductImage
    {
        return $this->images->firstWhere('is_primary', true) ?? $this->images->first();
    }

    public function isWishlistedBy(?int $userId): bool
    {
        if (! $userId) {
            return false;
        }
        return \App\Models\WishlistItem::where('user_id', $userId)
            ->where('product_id', $this->id)
            ->exists();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews()
    {
        return $this->reviews()->where('status', 'approved')->latest();
    }

    public function averageRating(): ?float
    {
        $avg = $this->approvedReviews()->avg('rating');
        return $avg ? round($avg, 1) : null;
    }

    public function reviewCount(): int
    {
        return $this->approvedReviews()->count();
    }

    public function reviewableOrderItemFor(?int $userId): ?OrderItem
    {
        if (! $userId) {
            return null;
        }
        $alreadyReviewed = $this->reviews()->where('user_id', $userId)->exists();
        if ($alreadyReviewed) {
            return null;
        }
        return OrderItem::whereHas('order', function ($query) use ($userId) {
            $query->where('user_id', $userId)->where('status', 'delivered');
        })
        ->whereHas('productVariant', fn ($query) => $query->where('product_id', $this->id))
        ->first();
    }

    public function isOnSale(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->base_price;
    }

    public function currentPrice(): float
    {
        return $this->isOnSale() ? (float) $this->sale_price : (float) $this->base_price;
    }
}
