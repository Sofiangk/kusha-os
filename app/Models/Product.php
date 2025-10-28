<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku',
        'name_ar',
        'description_ar',
        'category_id',
        'base_price',
        'minimum_order_quantity',
        'allow_below_minimum',
        'is_available',
        'created_by',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'minimum_order_quantity' => 'integer',
        'allow_below_minimum' => 'boolean',
        'is_available' => 'boolean',
    ];

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate SKU on creation
        static::creating(function ($product) {
            if (empty($product->sku)) {
                $product->sku = self::generateUniversalSku();
            }
        });

        // Log price changes on update
        static::updating(function ($product) {
            if ($product->isDirty('base_price')) {
                /** @var \App\Models\Product $product */
                PriceHistory::create([
                    'product_id' => $product->id,
                    'old_price' => $product->getOriginal('base_price'),
                    'new_price' => $product->base_price,
                    'changed_by' => auth()->id() ?? $product->created_by,
                    'reason' => 'manual_update',
                ]);
            }
        });
    }

    /**
     * Generate universal SKU (PRD000001, PRD000002, ... up to PRD999999)
     * Uses 6-digit padding to support up to 999,999 products
     */
    private static function generateUniversalSku(): string
    {
        $lastProduct = self::withTrashed()
            ->whereNotNull('sku')
            ->whereRaw("sku LIKE 'PRD%'")
            ->orderByRaw("CAST(SUBSTRING(sku, 4) AS UNSIGNED) DESC")
            ->first();

        $nextSequence = $lastProduct
            ? ((int) substr($lastProduct->sku, 4)) + 1
            : 1;

        return sprintf('PRD%06d', $nextSequence);
    }

    /**
     * Get the category that owns the product
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the user who created the product
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all price histories for this product
     */
    public function priceHistories(): HasMany
    {
        return $this->hasMany(PriceHistory::class);
    }

    /**
     * Scope to filter available products
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope to filter by category
     */
    public function scopeCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}
