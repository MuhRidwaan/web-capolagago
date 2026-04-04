<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ActivityTag extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'slug',
        'group_name',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_activity_tags', 'tag_id', 'product_id');
    }
}
