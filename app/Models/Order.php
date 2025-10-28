<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'client_id',
        'subtotal',
        'discount_id',
        'discount_name',
        'discount_code',
        'discount_type',
        'discount_value',
        'discount_amount',
        'total',
        'status',
        'delivery_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'delivery_date' => 'datetime',
    ];

    /**
     * Auto-generate order number on creation
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });

        static::created(function ($order) {
            if ($order->client) {
                $order->client->increment('total_orders');
                $order->client->increment('total_spent', $order->total);
            }
        });

        static::deleted(function ($order) {
            if ($order->client) {
                $order->client->decrement('total_orders');
                $order->client->decrement('total_spent', $order->total);
            }
        });

        static::restored(function ($order) {
            if ($order->client) {
                $order->client->increment('total_orders');
                $order->client->increment('total_spent', $order->total);
            }
        });
    }

    /**
     * Generate order number (ORD000001, ORD000002, ... up to ORD999999)
     * Uses 6-digit padding to support up to 999,999 orders
     */
    private static function generateOrderNumber(): string
    {
        $lastOrder = self::withTrashed()
            ->whereNotNull('order_number')
            ->whereRaw("order_number LIKE 'ORD%'")
            ->orderByRaw("CAST(SUBSTRING(order_number, 4) AS UNSIGNED) DESC")
            ->first();

        $nextSequence = $lastOrder
            ? ((int) substr($lastOrder->order_number, 4)) + 1  // Changed: 4 instead of 3
            : 1;

        return sprintf('ORD%06d', $nextSequence);  // Changed: %06d for 6-digit padding
    }

    /**
     * Get the client that owns this order
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the user who created this order
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all items for this order
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Calculate total from order items
     */
    public function calculateTotal(): float
    {
        return $this->items()->sum('subtotal');
    }

    /**
     * Update order total and client analytics
     */
    public function updateTotals(): void
    {
        $total = $this->calculateTotal();
        $this->update(['total' => $total]);

        // Update client's total_spent and total_orders
        if ($this->client) {
            $this->client->increment('total_orders');
            $this->client->increment('total_spent', $total);
        }
    }
}