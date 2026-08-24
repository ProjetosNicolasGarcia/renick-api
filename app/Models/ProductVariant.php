<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $table = 'products_variants';

    protected $fillable = [
        'product_id',
        'sku',
        'size',
        'color_name',
        'color_hex',
        'price',
        'promo_price',
        'promo_start',
        'promo_end',
        'stock_quantity',
        'weight_kg',
        'length_cm',
        'width_cm',
        'height_cm',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'promo_price' => 'decimal:2',
            'promo_start' => 'datetime',
            'promo_end' => 'datetime',
            'stock_quantity' => 'integer',
            'weight_kg' => 'decimal:3',
            'length_cm' => 'integer',
            'width_cm' => 'integer',
            'height_cm' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}