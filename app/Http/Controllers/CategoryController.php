<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index(Request $request)
    {
        $query = Category::orderBy('sort_order');

        // Filter active only if requested
        if ($request->has('active_only')) {
            $query->where('is_active', true);
        }

        $categories = $query->get();

        return $this->respond(['categories' => $categories], 'Categories retrieved successfully');
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:50',
            'name_ar' => 'required|string|max:50',
            'prefix' => 'nullable|string|max:3|unique:categories,prefix',
            'description_ar' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $category = Category::create($validated);

        return $this->respond(['category' => $category], 'Category created successfully', 201);
    }

    /**
     * Display the specified category
     */
    public function show(Category $category)
    {
        return $this->respond(['category' => $category], 'Category retrieved successfully');
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name_en' => 'sometimes|required|string|max:50',
            'name_ar' => 'sometimes|required|string|max:50',
            'prefix' => 'nullable|string|max:3|unique:categories,prefix,' . $category->id,
            'description_ar' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $category->update($validated);

        return $this->respond(['category' => $category], 'Category updated successfully');
    }

    /**
     * Remove the specified category (with protection)
     */
    public function destroy(Category $category)
    {
        // STRICT: Block if category has products (even soft-deleted products count)
        $productsCount = $category->products()->withTrashed()->count();

        if ($productsCount > 0) {
            return $this->respondError(
                'Cannot delete category with existing products',
                ['products_count' => $productsCount],
                409,
                'CATEGORY_IN_USE'
            );
        }

        $category->delete();

        return $this->respond(['category' => $category], 'Category deleted successfully');
    }
}
