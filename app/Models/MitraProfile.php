<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class MitraProfile extends Model
{
    protected $appends = [
        'logo_url',
    ];

    protected $fillable = [
        'user_id',
        'business_name',
        'slug',
        'description',
        'address',
        'latitude',
        'longitude',
        'contact_person',
        'whatsapp',
        'website',
        'logo_path',
        'bank_name',
        'bank_account_no',
        'bank_account_name',
        'commission_rate',
        'subscription_type',
        'status',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'commission_rate' => 'decimal:2',
            'joined_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'mitra_id');
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? Storage::url($this->logo_path) : null;
    }
}
