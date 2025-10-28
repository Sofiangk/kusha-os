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
        $local = null;

        // If already in international format (+218)
        if (str_starts_with($cleaned, '+218')) {
            $local = substr($cleaned, 4);
        }
        // If starts with 218 (without +)
        elseif (str_starts_with($cleaned, '218')) {
            $local = substr($cleaned, 3);
        }
        // If starts with 0 (local format)
        elseif (str_starts_with($cleaned, '0')) {
            $local = $cleaned;
        }
        // Invalid format
        else {
            throw new \InvalidArgumentException('Invalid Libyan phone number format');
        }

        // Validate local number: must be 10 digits starting with 09[0-5]
        if (!preg_match('/^09[0-5]\d{8}$/', $local)) {
            throw new \InvalidArgumentException('Invalid Libyan phone number. Must start with 090, 091, 092, 093, 094, or 095');
        }

        // Return normalized format
        return '+218' . $local;
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
