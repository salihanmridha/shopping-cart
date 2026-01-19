# Development Plan

## Task Overview

| Task | Description                           | Status |
|------|---------------------------------------|--------|
| 1    | Project Initialization                | ✅ Done |
| 2    | Environment & Configuration           | ✅ Done |
| 3    | Database Schema                       | ✅ Done |
| 4    | Model Relationships                   | ✅ Done |
| 5    | Product Data Preparation              | ✅ Done |
| 6    | Service Layer for Cart Business Logic | ✅ Done |
| 7    | Product Browsing UI (Livewire)        | ✅ Done |
| 8    | Cart UI (Livewire)                    | ✅ Done |
| 9    | Checkout Flow                         | ✅ Done |
| 10   | Events & Listeners                    | ✅ Done |
| 11   | Email Notifications                   | ✅ Done |
| 12   | Daily Sales Report                    | ✅ Done |
| 13   | Feature Testing                       | ✅ Done |

---

## Task Details

### Task 1: Project Initialization
- Create Laravel project with Breeze (Livewire)
- Initialize Git repository
- Run initial migrations
- Verify authentication works

### Task 2: Environment & Configuration
- Configure database, mail, queue drivers
- Create `config/shop.php` for app-specific settings
- Set up dummy admin email and low stock threshold

### Task 3: Database Schema
- `products` - name, price, stock_quantity
- `cart_items` - user_id, product_id, quantity
- `orders` - user_id, total_amount, status
- `order_items` - order_id, product_id, quantity, price

### Task 4: Model Relationships
- User → cartItems, orders
- Product → cartItems, orderItems
- Order → orderItems, user

### Task 5: Product Data Preparation
- Product factory and seeder
- Sample products with varied stock levels

### Task 6: Service Layer for Cart Business Logic
- `CartService` - add, update, remove, getCart, clear
- `CheckoutService` - process checkout with transaction
- `StockService` - validate, reduce, check low stock
- `SalesReportService` - aggregate daily sales data
- Stock and quantity validation on cart operations

### Task 7: Product Browsing UI
- Livewire component for product listing
- Add to cart action
- Pagination

### Task 8: Cart UI
- Livewire component for cart management
- Update quantity, remove items
- Display total

### Task 9: Checkout Flow
- Create order and order items
- Reduce stock atomically
- Clear cart after checkout

### Task 10: Events & Listeners
- `OrderCompleted` event (fired on order creation)
- Listeners for low stock check and notifications

### Task 11: Email Notifications
- `LowStockNotificationJob` - email admin
- `SendNewOrderNotificationJob` - email admin
- `SendOrderConfirmationJob` - email customer

### Task 12: Daily Sales Report
- Console command `report:daily-sales`
- `DailySalesReportJob` with `SalesReportService`
- Scheduled daily at 6:00 PM
