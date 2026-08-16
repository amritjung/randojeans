<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    protected $fillable = [
        'hero_image', 'feature_image', 'feature_video', 'video_heading', 'video_subtext',
        'manifesto_1_image', 'manifesto_2_image', 'story_image', 'product_page_bg',
    ];

    // Singleton pattern — there's only ever one row of site-wide
    // settings, so this always finds/creates record #1 rather than
    // letting the admin accidentally create multiple "homepages."
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }

    public function heroImageUrl(): ?string
    {
        return $this->hero_image ? Storage::disk('public')->url($this->hero_image) : null;
    }

    public function featureImageUrl(): ?string
    {
        return $this->feature_image ? Storage::disk('public')->url($this->feature_image) : null;
    }

    public function featureVideoUrl(): ?string
    {
        return $this->feature_video ? Storage::disk('public')->url($this->feature_video) : null;
    }

    public function manifesto1ImageUrl(): ?string
    {
        return $this->manifesto_1_image ? Storage::disk('public')->url($this->manifesto_1_image) : null;
    }

    public function manifesto2ImageUrl(): ?string
    {
        return $this->manifesto_2_image ? Storage::disk('public')->url($this->manifesto_2_image) : null;
    }

    public function storyImageUrl(): ?string
    {
        return $this->story_image ? Storage::disk('public')->url($this->story_image) : null;
    }

    public function productPageBgUrl(): ?string
    {
        return $this->product_page_bg ? Storage::disk('public')->url($this->product_page_bg) : null;
    }
}