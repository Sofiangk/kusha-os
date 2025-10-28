<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
  public function index(Request $request)
  {
    $query = Discount::query()->latest();

    if ($request->has('active')) {
      $query->where('is_active', filter_var($request->active, FILTER_VALIDATE_BOOLEAN));
    }

    if ($request->has('code')) {
      $query->where('code', $request->code);
    }

    return $this->respond(['discounts' => $query->paginate($request->get('per_page', 15))]);
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'name_ar' => 'required|string|max:100',
      'code' => 'nullable|string|max:50|unique:discounts,code',
      'type' => 'required|in:percentage,fixed_amount',
      'value' => 'required|numeric|min:0',
      'minimum_order_amount' => 'nullable|numeric|min:0',
      'valid_from' => 'nullable|date',
      'valid_to' => 'nullable|date|after_or_equal:valid_from',
      'is_active' => 'boolean',
      'description_ar' => 'nullable|string',
    ]);

    $discount = Discount::create([
      ...$validated,
      'created_by' => $request->user()->id,
    ]);

    return $this->respond(['discount' => $discount], 'Discount created', 201);
  }

  public function show(Discount $discount)
  {
    return $this->respond(['discount' => $discount]);
  }

  public function update(Request $request, Discount $discount)
  {
    $validated = $request->validate([
      'name_ar' => 'sometimes|string|max:100',
      'code' => 'nullable|string|max:50|unique:discounts,code,' . $discount->id,
      'type' => 'sometimes|in:percentage,fixed_amount',
      'value' => 'sometimes|numeric|min:0',
      'minimum_order_amount' => 'nullable|numeric|min:0',
      'valid_from' => 'nullable|date',
      'valid_to' => 'nullable|date|after_or_equal:valid_from',
      'is_active' => 'boolean',
      'description_ar' => 'nullable|string',
    ]);

    $discount->update($validated);
    return $this->respond(['discount' => $discount], 'Discount updated');
  }

  public function destroy(Discount $discount)
  {
    $discount->delete();
    return $this->respond(['discount' => $discount], 'Discount deleted');
  }
}
