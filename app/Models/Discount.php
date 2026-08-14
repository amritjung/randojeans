<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discount extends Model
{
    protected $fillable = ['code', 'percentage', 'is_active', 'expires_at', 'max_uses'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function usages(): HasMany
    {
        return $this->hasMany(DiscountUsage::class);
    }

    // The single source of truth for "can this code be used right now,
    // by anyone" — checks active status, expiry, and the overall usage
    // cap. Does NOT check per-customer usage — that's a separate check
    // (see hasBeenUsedBy below), since a code can be generally valid but
    // still blocked for one specific customer who already used it.
    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses && $this->usages()->count() >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function hasBeenUsedBy(string $email): bool
    {
        return $this->usages()->where('email', $email)->exists();
    }
}