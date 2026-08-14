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
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
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

    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class);
    }

    // Only ever counts APPROVED reviews — pending/rejected ones stay
    // invisible to this calculation, so an unmoderated or rejected review
    // can never affect the public-facing star rating.
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

    // A customer is eligible to review this product if they have a
    // delivered order containing it, and haven't already reviewed it.
    // Returns the specific OrderItem to link the review to (proof of
    // purchase), or null if they're not eligible at all.
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

    // The price that actually applies right now — sale_price if genuinely
    // on sale, otherwise the normal base_price. This is the single place
    // that decision gets made, so everywhere else in the app can just call
    // this instead of duplicating the "is it on sale" check repeatedly.
    public function currentPrice(): float
    {
        return $this->isOnSale() ? (float) $this->sale_price : (float) $this->base_price;
    }
}
