<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\Discount;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of orders
     */
    public function index(Request $request)
    {
        $query = Order::with(['client:id,name_ar,phone', 'creator:id,name', 'items.product:id,name_ar,sku'])
            ->latest();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by client
        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $orders = $query->paginate($request->get('per_page', 15));

        return $this->respond(['orders' => $orders], 'Orders retrieved successfully');
    }

    /**
     * Store a newly created order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'delivery_date' => 'nullable|date|after_or_equal:today',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'discount_code' => 'nullable|string',
        ]);

        // Validate and create order items with price snapshots
        $orderItems = [];
        $subtotal = 0;

        foreach ($validated['items'] as $itemData) {
            $product = Product::findOrFail($itemData['product_id']);

            // Enforce minimum quantity (unless override allowed)
            if (!$product->allow_below_minimum && $itemData['quantity'] < $product->minimum_order_quantity) {
                return $this->respondError(
                    "Minimum quantity for {$product->name_ar} is {$product->minimum_order_quantity}",
                    ['product' => $product->name_ar, 'minimum' => $product->minimum_order_quantity],
                    422,
                    'INSUFFICIENT_QUANTITY'
                );
            }

            $unitPrice = $product->base_price; // SNAPSHOT
            $itemSubtotal = $itemData['quantity'] * $unitPrice;

            $orderItems[] = [
                'product_id' => $itemData['product_id'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $unitPrice,
                'subtotal' => $itemSubtotal,
            ];

            $subtotal += $itemSubtotal;
        }

        // Optional discount application
        $discountSnapshot = [
            'discount_id' => null,
            'discount_name' => null,
            'discount_code' => null,
            'discount_type' => null,
            'discount_value' => null,
            'discount_amount' => 0,
        ];

        if (!empty($validated['discount_code'])) {
            $discount = Discount::where('code', $validated['discount_code'])->first();
            if (!$discount) {
                return $this->respondError('Invalid discount code', ['code' => $validated['discount_code']], 422);
            }
            $check = $discount->isValid((float) $subtotal);
            if (!$check['valid']) {
                return $this->respondError('Discount not applicable', ['reason' => $check['reason']], 422);
            }

            $amount = $discount->calculateAmount((float) $subtotal);
            $discountSnapshot = [
                'discount_id' => $discount->id,
                'discount_name' => $discount->name_ar,
                'discount_code' => $discount->code,
                'discount_type' => $discount->type,
                'discount_value' => $discount->value,
                'discount_amount' => $amount,
            ];
        }

        $total = max(0, $subtotal - $discountSnapshot['discount_amount']);

        // Create the order
        $order = Order::create([
            'client_id' => $validated['client_id'],
            'subtotal' => $subtotal,
            ...$discountSnapshot,
            'total' => $total,
            'status' => 'placed',
            'delivery_date' => $validated['delivery_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        // Create order items
        foreach ($orderItems as $item) {
            $order->items()->create($item);
        }

        // Client analytics are handled via Order model events

        // Load relationships for response
        $order->load(['client:id,name_ar,phone', 'items.product:id,name_ar,sku']);

        return $this->respond(['order' => $order], 'Order created successfully', 201);
    }

    /**
     * Display the specified order
     */
    public function show(Order $order)
    {
        $order->load(['client:id,name_ar,phone,address_ar', 'creator:id,name', 'items.product:id,name_ar,sku,base_price']);

        return $this->respond(['order' => $order], 'Order retrieved successfully');
    }

    /**
     * Update the specified order
     */
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:placed,preparing,ready_to_ship,shipped,delivered,refunded,canceled',
            'delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'sometimes|array|min:1',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.override_approved_by' => 'nullable|exists:users,id',
            'items.*.override_reason' => 'nullable|string|max:255',
            'discount_code' => 'nullable|string',
        ]);

        // Update basic fields
        $order->update(collect($validated)->only(['status', 'delivery_date', 'notes'])->toArray());

        // If items provided, replace items and recalc totals
        if (isset($validated['items'])) {
            $existingTotal = $order->total;

            $order->items()->delete();

            $subtotal = 0;
            $itemsPayload = [];

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                if (!$product->allow_below_minimum && $item['quantity'] < $product->minimum_order_quantity) {
                    return $this->respondError(
                        'Minimum order quantity not met for product',
                        ['product_id' => $product->id, 'minimum_quantity' => $product->minimum_order_quantity],
                        422
                    );
                }

                $unitPrice = $product->base_price;
                $subtotal += $unitPrice * $item['quantity'];

                $itemsPayload[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $unitPrice * $item['quantity'],
                    'override_approved_by' => $item['override_approved_by'] ?? null,
                    'override_reason' => $item['override_reason'] ?? null,
                ];
            }

            $order->items()->createMany($itemsPayload);

            // Recalculate discounts
            $discountSnapshot = [
                'discount_id' => $order->discount_id,
                'discount_name' => $order->discount_name,
                'discount_code' => $order->discount_code,
                'discount_type' => $order->discount_type,
                'discount_value' => $order->discount_value,
                'discount_amount' => $order->discount_amount,
            ];

            $activeDiscount = null;
            if (!empty($validated['discount_code'])) {
                $activeDiscount = Discount::where('code', $validated['discount_code'])->first();
                if (!$activeDiscount) {
                    return $this->respondError('Invalid discount code', ['code' => $validated['discount_code']], 422);
                }
            } elseif ($order->discount_id) {
                $activeDiscount = Discount::find($order->discount_id);
            }

            if ($activeDiscount) {
                $check = $activeDiscount->isValid((float) $subtotal);
                if ($check['valid']) {
                    $amount = $activeDiscount->calculateAmount((float) $subtotal);
                    $discountSnapshot = [
                        'discount_id' => $activeDiscount->id,
                        'discount_name' => $activeDiscount->name_ar,
                        'discount_code' => $activeDiscount->code,
                        'discount_type' => $activeDiscount->type,
                        'discount_value' => $activeDiscount->value,
                        'discount_amount' => $amount,
                    ];
                } else {
                    $discountSnapshot = [
                        'discount_id' => null,
                        'discount_name' => null,
                        'discount_code' => null,
                        'discount_type' => null,
                        'discount_value' => null,
                        'discount_amount' => 0,
                    ];
                }
            } else {
                $discountSnapshot = [
                    'discount_id' => null,
                    'discount_name' => null,
                    'discount_code' => null,
                    'discount_type' => null,
                    'discount_value' => null,
                    'discount_amount' => 0,
                ];
            }

            $total = max(0, $subtotal - $discountSnapshot['discount_amount']);
            $order->update(['subtotal' => $subtotal, ...$discountSnapshot, 'total' => $total]);

            // Adjust client analytics differential if total changed
            $diff = $total - $existingTotal;
            if ($diff !== 0 && $order->client) {
                if ($diff > 0) {
                    $order->client->increment('total_spent', $diff);
                } else {
                    $order->client->decrement('total_spent', abs($diff));
                }
            }
        }

        return $this->respond(['order' => $order->load('client:id,name_ar,phone', 'items.product:id,name_ar,sku')], 'Order updated successfully');
    }

    /**
     * Remove the specified order (soft delete)
     */
    public function destroy(Order $order)
    {
        // Client analytics are handled via Order model events

        $order->delete();

        return $this->respond(['order' => $order], 'Order deleted successfully');
    }

    /**
     * Create order with new client
     */
    public function storeWithNewClient(Request $request)
    {
        $validated = $request->validate([
            'client' => 'required|array',
            'client.name_ar' => 'required|string|max:150',
            'client.phone' => 'required|string|max:20',
            'client.address_ar' => 'nullable|string',
            'client.notes' => 'nullable|string',
            'delivery_date' => 'nullable|date|after_or_equal:today',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Check if client exists by phone
        $client = Client::where('phone', $validated['client']['phone'])->first();

        if (!$client) {
            // Create new client
            $client = Client::create([
                'name_ar' => $validated['client']['name_ar'],
                'phone' => $validated['client']['phone'],
                'address_ar' => $validated['client']['address_ar'] ?? null,
                'notes' => $validated['client']['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);
        }

        // Create order with new client
        $orderRequest = new Request($validated);
        $orderRequest->merge(['client_id' => $client->id]);

        return $this->store($orderRequest);
    }
}
