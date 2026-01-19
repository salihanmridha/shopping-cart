# Architecture Decisions & Assumptions

## Stack Decisions
**Decision:** Use Laravel 12 with Livewire starter kit.

**Why:**
- My expertise is with Laravel and Livewire is better than React or Vue


## Design Decisions

### 1. Service Layer Pattern
**Decision:** All business logic lives in service classes, not in controllers or Livewire components.

**Why:**
- Single Responsibility Principle
- Reusable across different contexts (web, API, commands)
- Easier to test in isolation
- Livewire components remain thin

**Services:**
- `CartService` - Cart operations
- `CheckoutService` - Checkout process
- `StockService` - Stock management
- `SalesReportService` - Sales data aggregation

### 2. Event-Driven Architecture for Notifications
**Decision:** Use Laravel Events and Listeners for post-checkout side effects.

**Why:**
- Decouples checkout logic from notification logic
- Open/Closed Principle - add new listeners without modifying checkout
- Each listener has single responsibility
- Queued listeners for better performance

**Flow:**
```
Order Created → OrderCompleted Event → 3 Listeners → 3 Queued Jobs → 3 Emails
```

### 3. Database Queue Driver
**Decision:** Use database queue driver instead of Redis/SQS.

**Why:**
- Simpler setup for development/demo
- No additional infrastructure needed
- Sufficient for this project's scale
- Easy to inspect queued jobs

### 4. Model Events for Event Dispatching
**Decision:** Use `$dispatchesEvents` on Order model instead of manual dispatch.

**Why:**
- Cleaner separation
- Event always fires when order is created
- No risk of forgetting to dispatch

### 5. Configuration-Based Admin Email
**Decision:** Store dummy admin email in `config/shop.php` instead of database.

**Why:**
- No need for admin user record
- Easy to change via environment variable
- Simpler for demo purposes

### 6. Delayed Job Dispatch for Rate Limiting
**Decision:** Stagger email jobs with delays.

**Why:**
- Mailtrap free tier has rate limits
- Prevents "too many emails per second" errors
- Production would use proper mail provider without this issue

### 7. Eloquent Collection Processing for Sales Report
**Decision:** Use Eloquent with collection-level aggregation instead of raw SQL.

**Why:**
- Cleaner, more readable code
- Sufficient for expected data volume
- Easier to maintain and test

**Trade-off:** For large datasets in production, SQL-level aggregation with JOIN and GROUP BY would be more performant.

---

## Assumptions

### 1. Single Quantity on Add to Cart
- When adding a product to cart, quantity defaults to 1
- If product already in cart, quantity increments by 1

### 2. Simple Checkout Flow
- No payment integration
- No shipping address
- Order is immediately "completed" on checkout

### 3. Low Stock Threshold
- Configurable via `SHOP_LOW_STOCK_THRESHOLD` env variable
- Default: 5 units
- Notification sent when stock falls below threshold after order

### 4. Daily Sales Report Timing
- Runs at 6:00 PM (18:00) server time
- Reports on current day's completed orders
- Can be manually triggered for any date

### 5. Email Configuration
- Using Mailtrap for development/testing
- All emails queued (async)

### 6. No Admin Panel
- No admin interface for managing products
- Products managed via database seeder
- Focus is on customer-facing cart functionality

### 7. Authentication
- Using Laravel Breeze defaults
- No role-based access control
- All authenticated users are customers
