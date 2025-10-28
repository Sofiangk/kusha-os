<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Get total revenue for a date range
     */
    public function revenue(Request $request)
    {
        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
        ]);

        $query = Order::whereNotIn('status', ['canceled', 'refunded'])
            ->whereNotNull('total');

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $totalRevenue = $query->sum('total');
        $orderCount = $query->count();
        $averageOrder = $orderCount > 0 ? $totalRevenue / $orderCount : 0;

        return $this->respond([
            'total_revenue' => round($totalRevenue, 2),
            'order_count' => $orderCount,
            'average_order_value' => round($averageOrder, 2),
            'period' => [
                'from' => $request->from_date,
                'to' => $request->to_date,
            ],
        ], 'Revenue report generated successfully');
    }

    /**
     * Get best selling products
     */
    public function bestSellingProducts(Request $request)
    {
        $limit = $request->get('limit', 10);
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $query = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNotIn('orders.status', ['canceled', 'refunded'])
            ->select(
                'products.id',
                'products.sku',
                'products.name_ar',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.sku', 'products.name_ar')
            ->orderBy('total_quantity', 'desc');

        if ($fromDate) {
            $query->whereDate('orders.created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('orders.created_at', '<=', $toDate);
        }

        $products = $query->limit($limit)->get();

        return $this->respond([
            'products' => $products,
            'limit' => $limit,
        ], 'Best selling products retrieved successfully');
    }

    /**
     * Get recurring clients (VIP customers)
     */
    public function recurringClients(Request $request)
    {
        $minOrders = $request->get('min_orders', 3);
        $limit = $request->get('limit', 20);

        $clients = Client::with('orders:id,client_id,order_number,total,status,created_at')
            ->where('total_orders', '>=', $minOrders)
            ->orderBy('total_orders', 'desc')
            ->limit($limit)
            ->get();

        return $this->respond([
            'clients' => $clients,
            'min_orders' => $minOrders,
            'limit' => $limit,
        ], 'Recurring clients retrieved successfully');
    }

    /**
     * Get orders by status breakdown
     */
    public function ordersByStatus(Request $request)
    {
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $query = Order::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status');

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $statusBreakdown = $query->get()->keyBy('status');

        return $this->respond([
            'status_breakdown' => $statusBreakdown,
            'period' => [
                'from' => $fromDate,
                'to' => $toDate,
            ],
        ], 'Orders by status retrieved successfully');
    }

    /**
     * Get orders by date (for daily/weekly/monthly reports)
     */
    public function ordersByDate(Request $request)
    {
        $validated = $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date',
            'group_by' => 'sometimes|in:day,week,month',
        ]);

        $groupBy = $request->get('group_by', 'day');

        $dateFormat = match ($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $orders = Order::whereBetween('created_at', [$validated['from_date'], $validated['to_date']])
            ->whereNotIn('status', ['canceled', 'refunded'])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as date_group"),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as total_revenue')
            )
            ->groupBy('date_group')
            ->orderBy('date_group')
            ->get();

        return $this->respond([
            'orders' => $orders,
            'group_by' => $groupBy,
            'period' => [
                'from' => $validated['from_date'],
                'to' => $validated['to_date'],
            ],
        ], 'Orders by date retrieved successfully');
    }

    /**
     * Get product category performance
     */
    public function categoryPerformance(Request $request)
    {
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $query = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNotIn('orders.status', ['canceled', 'refunded'])
            ->select(
                'categories.id',
                'categories.name_ar',
                'categories.name_en',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('categories.id', 'categories.name_ar', 'categories.name_en')
            ->orderBy('total_revenue', 'desc');

        if ($fromDate) {
            $query->whereDate('orders.created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('orders.created_at', '<=', $toDate);
        }

        $categories = $query->get();

        return $this->respond([
            'categories' => $categories,
            'period' => [
                'from' => $fromDate,
                'to' => $toDate,
            ],
        ], 'Category performance retrieved successfully');
    }

    /**
     * Get client order history
     */
    public function clientOrderHistory(Request $request, int $clientId)
    {
        $client = Client::findOrFail($clientId);

        $orders = $client->orders()
            ->with('items.product:id,sku,name_ar')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->respond([
            'client' => [
                'id' => $client->id,
                'name_ar' => $client->name_ar,
                'phone' => $client->phone,
                'total_orders' => $client->total_orders,
                'total_spent' => $client->total_spent,
            ],
            'orders' => $orders,
        ], 'Client order history retrieved successfully');
    }
}
