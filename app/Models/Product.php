<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'collection_id',
        'gender',
        'name',
        'slug',
        'description',
        'rating_average',
        'total_review',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rating_average' => 'decimal:2',
            'total_review' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }
}