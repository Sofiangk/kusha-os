<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name_ar',
        'phone',
        'address_ar',
        'notes',
        'total_orders',
        'total_spent',
        'created_by',
    ];

    protected $casts = [
        'total_orders' => 'integer',
        'total_spent' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (Client $client) {
            if (!empty($client->phone)) {
                $client->phone = self::normalizePhone($client->phone);
            }
        });
    }

    private static function normalizePhone(string $phone): string
    {
        $cleaned = preg_replace('/[\s\-\(\)]/', '', $phone);

        if (!str_starts_with($cleaned, '+')) {
            if (str_starts_with($cleaned, '218')) {
                $cleaned = '+' . $cleaned;
            } elseif (str_starts_with($cleaned, '0')) {
                $cleaned = '+218' . substr($cleaned, 1);
            } else {
                $cleaned = '+218' . $cleaned;
            }
        }

        return $cleaned;
    }

    /**
     * Get the user who created this client
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all orders for this client
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Check if client has existing orders
     */
    public function hasOrders(): bool
    {
        return $this->orders()->withTrashed()->count() > 0;
    }
}
