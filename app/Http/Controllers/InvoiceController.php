<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Generate invoice from order
     */
    public function generate(Order $order)
    {
        $order->load([
            'client:id,name_ar,phone,address_ar',
            'items.product:id,sku,name_ar',
            'creator:id,name',
            'items' => function ($query) {
                $query->select('id', 'order_id', 'product_id', 'quantity', 'unit_price', 'subtotal');
            }
        ]);

        $invoice = $this->buildInvoice($order);

        return $this->respond(['invoice' => $invoice], 'Invoice generated successfully');
    }

    /**
     * List all invoices (from orders)
     */
    public function index(Request $request)
    {
        $query = Order::whereNotNull('total')
            ->with(['client:id,name_ar,phone', 'creator:id,name'])
            ->latest();

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Filter by client
        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $orders = $query->get();

        $invoices = $orders->map(function ($order) {
            return [
                'invoice_number' => $order->order_number,
                'client_name' => $order->client->name_ar,
                'client_phone' => $order->client->phone,
                'total' => $order->total,
                'status' => $order->status,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return $this->respond(['invoices' => $invoices], 'Invoices retrieved successfully');
    }

    /**
     * Get full invoice details
     */
    public function show(Order $order)
    {
        $order->load([
            'client',
            'items.product',
            'creator',
        ]);

        $invoice = $this->buildInvoice($order);

        return $this->respond(['invoice' => $invoice], 'Invoice retrieved successfully');
    }

    /**
     * Build invoice array from order
     */
    private function buildInvoice(Order $order): array
    {
        return [
            'invoice_number' => $order->order_number,
            'invoice_date' => $order->created_at->format('Y-m-d'),
            'invoice_time' => $order->created_at->format('H:i:s'),

            'client' => [
                'name_ar' => $order->client->name_ar,
                'phone' => $order->client->phone,
                'address_ar' => $order->client->address_ar,
            ],

            'items' => $order->items->map(function ($item) {
                return [
                    'sku' => $item->product->sku,
                    'name_ar' => $item->product->name_ar,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ];
            }),

            'order_details' => [
                'total' => $order->total,
                'status' => $order->status,
                'delivery_date' => $order->delivery_date?->format('Y-m-d H:i'),
                'notes' => $order->notes,
            ],

            'discount' => $order->discount_amount > 0 ? [
                'name_ar' => $order->discount_name,
                'code' => $order->discount_code,
                'type' => $order->discount_type,
                'value' => $order->discount_value,
                'amount' => $order->discount_amount,
            ] : null,

            'summary' => [
                'items_count' => $order->items->sum('quantity'),
                'products_count' => $order->items->count(),
                'subtotal' => $order->subtotal,
                'discount' => $order->discount_amount,
                'total' => $order->total,
            ],
        ];
    }
}
