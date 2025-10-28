# Phase 4: Orders & Order Items - Implementation Complete ✓

## What Was Built

### Database

-   **orders** table with:

    -   Order number (ORD001, ORD002...) auto-generated
    -   Links to client_id (restrict on delete)
    -   Total amount (calculated from items)
    -   Status enum (placed, preparing, ready_to_ship, shipped, delivered, refunded, canceled)
    -   Delivery date
    -   Notes
    -   Creator tracking
    -   Soft deletes

-   **order_items** table with:
    -   Links to order_id (cascade on delete)
    -   Links to product_id (restrict on delete)
    -   Quantity
    -   **unit_price (SNAPSHOT of product.base_price at order time)**
    -   Subtotal (quantity × unit_price)
    -   Auto-calculated subtotal

### Models

-   **Order.php** with:

    -   Auto-generate order numbers (ORD001, ORD002...)
    -   Relationships to client, creator, items
    -   calculateTotal() method
    -   updateTotals() method to sync client analytics

-   **OrderItem.php** with:
    -   Auto-calculate subtotal on save
    -   Relationships to order and product

### Controller

-   **OrderController.php** with:
    -   Full CRUD operations
    -   Filter by status, client, date range
    -   **Order creation with price snapshots**
    -   **Minimum quantity enforcement** (unless allow_below_minimum is true)
    -   Update client analytics (total_orders, total_spent)
    -   Special endpoint: Create order with new client

### Routes

All under `/api/v1` and protected by `auth:sanctum`:

-   GET `/orders` - List all (with filters: status, client_id, from_date, to_date)
-   POST `/orders` - Create order (requires existing client)
-   POST `/orders/with-new-client` - Create order and client simultaneously
-   GET `/orders/{id}` - Show one with all relationships
-   PUT `/orders/{id}` - Update (mainly status)
-   DELETE `/orders/{id}` - Soft delete (refunds client analytics)

## Key Features

### ✅ Price Snapshots

-   Each order_item stores `unit_price` from product at order time
-   If product price changes later, historical orders stay accurate
-   Analytics work correctly

### ✅ Minimum Quantity Enforcement

-   Enforced by default (cannot order below minimum)
-   Can be overridden with `allow_below_minimum: true` on product
-   Returns 422 error with specific minimum if violated

### ✅ Client Analytics Auto-Update

-   When order created: client.total_orders++, client.total_spent += total
-   When order deleted: client totals decremented automatically
-   Prevents manual calculation errors

### ✅ Order Number Auto-Generation

-   ORD001, ORD002, ORD003... (never resets)
-   Unique and sequential

### ✅ Status Workflow

-   Enum values: placed → preparing → ready_to_ship → shipped → delivered
-   Can also be: refunded, canceled
-   Filterable in list endpoint

## Request/Response Examples

### Create Order

```json
POST /api/v1/orders
{
  "client_id": 1,
  "delivery_date": "2025-11-01",
  "notes": "Holiday order",
  "items": [
    {
      "product_id": 1,
      "quantity": 12
    },
    {
      "product_id": 2,
      "quantity": 24
    }
  ]
}
```

Response (201):

```json
{
    "success": true,
    "message": "Order created successfully",
    "data": {
        "order": {
            "id": 1,
            "order_number": "ORD001",
            "client_id": 1,
            "total": "270.00",
            "status": "placed",
            "items": [
                {
                    "product_id": 1,
                    "quantity": 12,
                    "unit_price": "15.00",
                    "subtotal": "180.00"
                },
                {
                    "product_id": 2,
                    "quantity": 24,
                    "unit_price": "18.00",
                    "subtotal": "432.00"
                }
            ],
            "client": {
                "total_orders": 16, // incremented
                "total_spent": "720.00" // updated
            }
        }
    }
}
```

### Minimum Quantity Error

```json
POST /api/v1/orders
{
  "client_id": 1,
  "items": [
    {
      "product_id": 1,
      "quantity": 5  // Below minimum of 12
    }
  ]
}
```

Response (422):

```json
{
    "success": false,
    "error": {
        "code": "INSUFFICIENT_QUANTITY",
        "message": "Minimum quantity for برج بيتي فور صغير is 12",
        "details": {
            "product": "برج بيتي فور صغير",
            "minimum": 12
        }
    }
}
```

## Complete System Flow

1. **External Order Received** (WhatsApp, Messenger, Phone)
2. **Search Client** → `/clients/search-phone` (returns client or 404)
    - If exists: use client_id
    - If not: create via `/orders/with-new-client`
3. **Create Order** → `/orders`
    - Validate minimum quantities
    - Create order with total = sum of items
    - Create order_items with SNAPSHOT prices
    - Update client analytics
4. **Track Order** → `/orders/{id}`
    - Update status (preparing, ready_to_ship, etc.)
5. **Complete Order** → status = "delivered"
6. **Generate Invoice** (Phase 6 - JSON for now)

## Next Steps

-   **Phase 5**: Status tracking and history
-   **Phase 6**: Invoice generation (JSON)
-   **Phase 7**: Analytics and reports

Ready to build Phase 5 or test this thoroughly? 🎉
