<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        $query = Product::with(['creator:id,name', 'category:id,name_ar,name_en'])
            ->latest();

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by availability
        if ($request->has('available_only')) {
            $query->where('is_available', true);
        }

        // Search in Arabic name
        if ($request->has('search')) {
            $query->where('name_ar', 'like', '%' . $request->search . '%');
        }

        $products = $query->paginate($request->get('per_page', 15));

        return $this->respond(['products' => $products], 'Products retrieved successfully');
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:150',
            'description_ar' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric|min:0',
            'minimum_order_quantity' => 'required|integer|min:1',
            'allow_below_minimum' => 'boolean',
            'is_available' => 'boolean',
        ]);

        $product = Product::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return $this->respond(
            ['product' => $product->load('category')],
            'Product created successfully',
            201
        );
    }

    /**
     * Display the specified product
     */
    public function show(Product $product)
    {
        $product->load(['creator:id,name', 'category:id,name_ar,name_en']);

        return $this->respond(['product' => $product], 'Product retrieved successfully');
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name_ar' => 'sometimes|required|string|max:150',
            'description_ar' => 'nullable|string',
            'category_id' => 'sometimes|required|exists:categories,id',
            'base_price' => 'sometimes|required|numeric|min:0',
            'minimum_order_quantity' => 'sometimes|required|integer|min:1',
            'allow_below_minimum' => 'boolean',
            'is_available' => 'boolean',
        ]);

        $product->update($validated);

        return $this->respond(
            ['product' => $product->load('category')],
            'Product updated successfully'
        );
    }

    /**
     * Remove the specified product (soft delete)
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return $this->respond(['product' => $product], 'Product deleted successfully');
    }
}
