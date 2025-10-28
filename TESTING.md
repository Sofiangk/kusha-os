# KushaOS Product & Category System Testing Guide

## Implementation Complete ✓

All migrations, models, controllers, seeders, and routes have been successfully created and tested.

## Database Summary

-   **Categories**: 4 seeded (Betefour, Signature Items, Special Orders, Other)
-   **Products**: 20 seeded with Arabic names
-   **Users**: 3 test users created
-   **SKU Format**: PRD001, PRD002, PRD003... (universal, never resets)

## Test User Credentials

```
Email: admin@kushaos.test
Password: password123

Email: operator1@kushaos.test
Password: password123

Email: operator2@kushaos.test
Password: password123
```

## API Endpoints

Base URL: `http://kusha-os.test/api/v1`

### Authentication Endpoints

1. **Register** (if needed)

    - POST `/register`
    - Body: `{"name": "Test", "email": "test@example.com", "password": "password123", "password_confirmation": "password123"}`

2. **Login**

    - POST `/login`
    - Body: `{"email": "admin@kushaos.test", "password": "password123"}`
    - Response: `{"success": true, "data": {"token": "...", "user": {...}}}`
    - **Copy the token for subsequent requests**

3. **Logout**

    - POST `/logout`
    - Header: `Authorization: Bearer {token}`

4. **Get Current User**
    - GET `/me`
    - Header: `Authorization: Bearer {token}`

### Category Endpoints

All require: `Authorization: Bearer {token}`

1. **List Categories**

    - GET `/categories`
    - Optional query: `?active_only=1`

2. **Create Category**

    - POST `/categories`
    - Body:

    ```json
    {
        "name_en": "Seasonal",
        "name_ar": "موسمية",
        "prefix": "SEA",
        "description_ar": "منتجات موسمية",
        "is_active": true,
        "sort_order": 5
    }
    ```

3. **Get Single Category**

    - GET `/categories/{id}`

4. **Update Category**

    - PUT `/categories/{id}`
    - Body (partial update allowed):

    ```json
    {
        "name_ar": "قطع موسمية",
        "is_active": false
    }
    ```

5. **Delete Category**
    - DELETE `/categories/{id}`
    - Note: Will fail if category has products (409 error)

### Product Endpoints

All require: `Authorization: Bearer {token}`

1. **List Products**

    - GET `/products`
    - Optional queries:
        - `?category_id=1` - Filter by category
        - `?available_only=1` - Show only available products
        - `?search=شوكولاتة` - Search in Arabic names
        - `?per_page=10` - Pagination (default: 15)

2. **Create Product**

    - POST `/products`
    - Body:

    ```json
    {
        "name_ar": "بيتي فور جديد",
        "description_ar": "وصف المنتج",
        "category_id": 1,
        "base_price": 25.5,
        "minimum_order_quantity": 12,
        "allow_below_minimum": false,
        "is_available": true
    }
    ```

    - Note: SKU will be auto-generated (e.g., PRD021)

3. **Get Single Product**

    - GET `/products/{id}`

4. **Update Product**

    - PUT `/products/{id}`
    - Body (partial update allowed):

    ```json
    {
        "base_price": 30.0,
        "is_available": false
    }
    ```

    - Note: Price change will be logged to price_histories

5. **Delete Product**
    - DELETE `/products/{id}`
    - Soft delete (data preserved)

## Testing Scenarios

### Scenario 1: Basic CRUD Flow

1. Login to get token
2. List all categories
3. List all products
4. Create a new product
5. Update the product's price
6. Verify price history was logged (check database or future endpoint)
7. Soft delete the product

### Scenario 2: Validation Testing

1. Try creating product without required fields → expect 422 error
2. Try creating product with invalid category_id → expect 422 error
3. Try creating product with negative price → expect 422 error
4. Try deleting category with products → expect 409 error

### Scenario 3: Filtering & Search

1. List products filtered by category
2. Search products by Arabic name
3. Filter only available products
4. Test pagination with different per_page values

### Scenario 4: Price Change Tracking

1. Create a product with base_price: 15.00
2. Update product with base_price: 20.00
3. Update again with base_price: 18.00
4. Check price_histories table to verify all changes logged

### Scenario 5: Category Protection

1. List all categories
2. Pick a category with products (e.g., Betefour - id: 1)
3. Try to delete it → should fail with 409 error
4. Create new empty category
5. Delete empty category → should succeed

### Scenario 6: SKU Generation

1. Create 3 new products
2. Verify SKUs are PRD021, PRD022, PRD023 (sequential)
3. Delete one product (soft delete)
4. Create another product
5. Verify SKU continues sequence (PRD024, not reusing deleted)

## Expected Response Formats

### Success Response

```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "product": {
      "id": 21,
      "sku": "PRD021",
      "name_ar": "بيتي فور جديد",
      "category_id": 1,
      "base_price": "25.50",
      ...
    }
  }
}
```

### Error Response

```json
{
    "success": false,
    "error": {
        "code": "CATEGORY_IN_USE",
        "message": "Cannot delete category with existing products",
        "details": {
            "products_count": 12
        }
    }
}
```

### Validation Error (Laravel Standard)

```json
{
    "message": "The name ar field is required. (and 1 more error)",
    "errors": {
        "name_ar": ["The name ar field is required."],
        "base_price": ["The base price field is required."]
    }
}
```

## Database Verification

Run these commands to verify data integrity:

```bash
# Count records
php artisan tinker --execute="echo 'Categories: ' . \App\Models\Category::count() . PHP_EOL;"
php artisan tinker --execute="echo 'Products: ' . \App\Models\Product::count() . PHP_EOL;"

# Check SKU sequence
php artisan tinker --execute="\App\Models\Product::orderBy('id')->get(['id', 'sku', 'name_ar'])->each(fn(\$p) => print(\$p->sku . ' - ' . \$p->name_ar . PHP_EOL));"

# Check price histories
php artisan tinker --execute="\App\Models\PriceHistory::with('product:id,name_ar')->get()->each(fn(\$h) => print('Product: ' . \$h->product->name_ar . ' | Old: ' . \$h->old_price . ' → New: ' . \$h->new_price . PHP_EOL));"
```

## Next Steps

After testing the Product & Category system:

1. **Clients Module** - Customer/client management
2. **Orders Module** - Order creation with order items
3. **Order Items** - Link products to orders with price snapshots
4. **Invoices** - Generate invoices from orders
5. **Analytics** - Reporting and insights
6. **Status Tracking** - Order status workflow

## Notes

-   All timestamps are in `Africa/Tripoli` timezone
-   Locale is set to Arabic (`ar`)
-   All API routes are versioned under `/api/v1`
-   Price changes are automatically logged (no manual intervention needed)
-   SKUs never reset or reuse deleted numbers
-   Soft deletes preserve data for analytics and historical records
