# KushaOS - Bakery Order Management System

**API Documentation**: [View Swagger Docs](#swagger-documentation)

A comprehensive backend API for managing a home bakery's orders, products, clients, and analytics. Built with Laravel 12 and Sanctum authentication.

## 🎯 Project Overview

KushaOS helps small bakeries track orders from receipt to delivery, manage inventory (betefour and signature items), handle client relationships, apply discounts, generate invoices, and analyze business performance.

**Business Context**: Home bakery in Benghazi, Libya - specialized in betefour with seasonal offerings.

---

## 🚀 Quick Start

### Prerequisites

-   PHP 8.2+
-   MySQL 8.0+
-   Composer
-   Laravel Herd (macOS) or your preferred development environment

### Installation

```bash
# Clone and navigate
git clone https://github.com/Sofiangk/kusha-os.git
cd kusha-os

# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kushaos
DB_USERNAME=root
DB_PASSWORD=

# Run migrations and seed
php artisan migrate --seed

# Start server
php artisan serve
```

API will be available at: `http://localhost:8000/api/v1`

---

## 📡 API Endpoints

**Base URL**: `https://your-domain.com/api/v1`

All protected endpoints require authentication via Sanctum token (Bearer token in header).

### Authentication

-   `POST /register` - Register new user
-   `POST /login` - Login and get token
-   `POST /logout` - Logout and invalidate token
-   `GET /me` - Get current user info

### Categories

-   `GET /categories` - List all categories
-   `POST /categories` - Create category
-   `GET /categories/{id}` - Get category details
-   `PUT /categories/{id}` - Update category
-   `DELETE /categories/{id}` - Soft delete category

### Products

-   `GET /products` - List products (filter: `category_id`, `search`, `available_only`)
-   `POST /products` - Create product
-   `GET /products/{id}` - Get product details
-   `PUT /products/{id}` - Update product
-   `DELETE /products/{id}` - Soft delete product

### Clients

-   `GET /clients` - List all clients
-   `POST /clients` - Create client
-   `POST /clients/search-phone` - Search by phone number
-   `GET /clients/{id}` - Get client details
-   `PUT /clients/{id}` - Update client
-   `DELETE /clients/{id}` - Soft delete client

### Orders

-   `GET /orders` - List orders (filter: `status`, `client_id`, `from_date`, `to_date`)
-   `POST /orders` - Create order with existing client
-   `POST /orders/with-new-client` - Create order with new client
-   `GET /orders/{id}` - Get order details
-   `PUT /orders/{id}` - Update order (items, notes, delivery_date, status)
-   `DELETE /orders/{id}` - Soft delete order

**Create Order Request**:

```json
{
    "client_id": 1,
    "delivery_date": "2025-11-01",
    "notes": "حجز لعطلة",
    "discount_code": "FAMILY15",
    "items": [{ "product_id": 1, "quantity": 10 }]
}
```

### Discounts

-   `GET /discounts` - List all discounts
-   `POST /discounts` - Create discount
-   `GET /discounts/{id}` - Get discount details
-   `PUT /discounts/{id}` - Update discount
-   `DELETE /discounts/{id}` - Soft delete discount

**Available Discount Codes**:

-   `FAMILY15` - 15% off, min 100 LYD
-   `RAMADAN20` - 20% off, min 50 LYD
-   `VIP10` - 10% off, min 80 LYD
-   `SEASON25` - 25 LYD fixed, min 120 LYD
-   `WELCOME5` - 5% off for new customers

### Invoices

-   `GET /invoices` - List all invoices
-   `GET /invoices/{order_id}` - Get full invoice details
-   `GET /orders/{id}/invoice` - Generate invoice from order

### Reports & Analytics

-   `GET /reports/revenue` - Total revenue, order count, average
-   `GET /reports/best-selling` - Top products by sales
-   `GET /reports/recurring-clients` - VIP customers (3+ orders)
-   `GET /reports/orders-by-status` - Status breakdown
-   `GET /reports/orders-by-date` - Time-based analytics
-   `GET /reports/category-performance` - Revenue by category
-   `GET /reports/client/{id}` - Client order history

---

## 🔐 Authentication

All protected endpoints require a Bearer token.

### Login Flow

1. POST to `/api/v1/login` with email and password
2. Receive token in response: `{"token": "1|abc123..."}`
3. Add to headers: `Authorization: Bearer {token}`
4. Use this token for all authenticated requests

### Example (Postman)

```
Headers:
Authorization: Bearer 1|xyz789...
Accept: application/json
```

---

## 📊 Data Structure

### Product SKU Format

Universal 6-digit format: `PRD000001`, `PRD000002`, ... up to `PRD999999`

### Order Number Format

Sequential 6-digit format: `ORD000001`, `ORD000002`, ... up to `ORD999999`

### Phone Normalization

All Libyan phone numbers auto-normalize to: `+21809XXXXXXXX` (11 total digits after +)

**Valid prefixes**: Only 090, 091, 092, 093, 094, or 095

Examples:

-   `0912345678` → `+218912345678`
-   `0921234567` → `+218921234567`
-   `002180956789123` → `+218956789123`

**Invalid**: Any number not starting with 09[0-5] will be rejected

---

## 🧪 Testing

### Postman Collection

Import `KushaOS-API.postman_collection.json` into Postman.

### Test Credentials (from seeder)

```
Email: admin@kushaos.test
Password: password

Email: operator1@kushaos.test
Password: password

Email: operator2@kushaos.test
Password: password
```

### Quick Test Flow

1. Login with test credentials
2. Copy Bearer token
3. Create an order with discount code `FAMILY15`
4. Generate invoice for the order
5. View revenue report

---

## 🎨 Response Format

### Success Response

```json
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "order": {
      "id": 1,
      "order_number": "ORD000001",
      "client_id": 1,
      "total": "180.00",
      "status": "placed",
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
        "code": "INSUFFICIENT_QUANTITY",
        "message": "Minimum quantity for المنتج is 12",
        "details": {
            "product": "برج بيتي فور",
            "minimum": 12
        }
    }
}
```

---

## 🔒 Security Features

-   ✅ Sanctum API token authentication
-   ✅ Password hashing (bcrypt)
-   ✅ Input validation on all endpoints
-   ✅ SQL injection protection (Eloquent ORM)
-   ✅ Soft deletes (data recovery)
-   ✅ Foreign key constraints
-   ✅ Rate limiting (recommended for production)
-   ✅ CORS configuration

---

## 📝 Key Business Rules

### Orders

-   Minimum quantity enforced per product
-   Price snapshots stored (historical accuracy)
-   Client analytics auto-update on order create/delete
-   Orders can be edited (items, dates, notes)
-   Refunded/canceled orders excluded from revenue

### Discounts

-   One discount per order (no stacking)
-   Validation: active status, date range, minimum amount
-   Snapshot stored (discount changes don't affect past orders)

### Analytics

-   Recurring clients: 3+ orders
-   Revenue excludes refunded and canceled orders
-   Best-selling products by quantity sold
-   Category performance tracking

---

## 🚢 Deployment

### Production Environment

1. Update `.env` with production database credentials
2. Set `APP_ENV=production`
3. Set `APP_DEBUG=false`
4. Run `php artisan config:cache`
5. Run `php artisan route:cache`
6. Deploy to Heroku, Railway, or your preferred hosting

### Heroku Deployment

```bash
heroku create kushaos
heroku addons:create heroku-postgresql:hobby-dev
git push heroku main
heroku run php artisan migrate --seed
```

---

## 📚 Swagger Documentation

Once deployed, visit: `https://your-domain.com/api/documentation`

For local development:

1. Run `php artisan l5-swagger:generate`
2. Visit `http://localhost:8000/api/documentation`

---

## 🛠️ Development

### Database

-   MySQL 8.0+
-   11 tables with relationships
-   Soft deletes on all main entities
-   Price history tracking

### Code Structure

```
app/
├── Http/
│   └── Controllers/     # All API controllers
├── Models/              # Eloquent models
└── Helpers/             # ApiResponse helper

database/
├── migrations/          # Database structure
└── seeders/            # Test data

routes/
└── api.php             # All API routes (v1 prefixed)
```

### Key Features

-   RESTful API design
-   JSON responses only
-   Arabic-first content
-   Versioned API (`/api/v1`)
-   Model events for automation
-   Comprehensive error handling

---

## 📞 Support

For frontend integration questions or API issues, contact the backend team.

**Endpoints ready for mobile/web integration.**

---

**Built with Laravel 12 • Sanctum Auth • MySQL**
