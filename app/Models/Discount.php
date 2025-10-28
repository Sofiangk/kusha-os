<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'name_ar',
    'code',
    'type',
    'value',
    'minimum_order_amount',
    'valid_from',
    'valid_to',
    'is_active',
    'description_ar',
    'created_by'
  ];

  protected $casts = [
    'value' => 'decimal:2',
    'minimum_order_amount' => 'decimal:2',
    'valid_from' => 'datetime',
    'valid_to' => 'datetime',
    'is_active' => 'boolean',
  ];

  public function isValid(float $orderSubtotal): array
  {
    if (!$this->is_active) {
      return ['valid' => false, 'reason' => 'Discount is not active'];
    }

    $now = now();
    if ($this->valid_from && $now->lt($this->valid_from)) {
      return ['valid' => false, 'reason' => 'Discount not yet valid'];
    }

    if ($this->valid_to && $now->gt($this->valid_to)) {
      return ['valid' => false, 'reason' => 'Discount has expired'];
    }

    if ($this->minimum_order_amount && $orderSubtotal < $this->minimum_order_amount) {
      return ['valid' => false, 'reason' => "Minimum order amount is {$this->minimum_order_amount} LYD"];
    }

    return ['valid' => true];
  }

  public function calculateAmount(float $subtotal): float
  {
    if ($this->type === 'percentage') {
      return round($subtotal * ($this->value / 100), 2);
    }
    return min((float) $this->value, $subtotal);
  }
}
