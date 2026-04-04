<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $appends = [
        'primary_image_url',
    ];

    protected $fillable = [
        'mitra_id',
        'category_id',
        'name',
        'slug',
        'short_desc',
        'description',
        'price',
        'price_label',
        'min_pax',
        'max_pax',
        'max_capacity',
        'duration_hours',
        'is_featured',
        'is_active',
        'rating_avg',
        'review_count',
        'sort_order',
        'meta_title',
        'meta_desc',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_hours' => 'decimal:1',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'rating_avg' => 'decimal:2',
        ];
    }

    public function mitra(): BelongsTo
    {
        return $this->belongsTo(MitraProfile::class, 'mitra_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderByDesc('is_primary')->orderBy('sort_order');
    }

    public function activityTags(): BelongsToMany
    {
        return $this->belongsToMany(ActivityTag::class, 'product_activity_tags', 'product_id', 'tag_id');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->ofMany([
            'is_primary' => 'max',
            'sort_order' => 'min',
            'id' => 'min',
        ]);
    }

    public function getPrimaryImageUrlAttribute(): ?string
    {
        $image = $this->relationLoaded('primaryImage')
            ? $this->primaryImage
            : $this->primaryImage()->first();

        return $image?->image_url;
    }
}
