# Project Requirements Summary

## 1. Project Overview
Build a simple e-commerce shopping cart system using Laravel and Livewire starter kit.

## 2. Technical Stack
- **Backend:** Laravel (latest stable)
- **Frontend:** Livewire (Laravel Breeze)
- **Styling:** Tailwind CSS
- **Authentication:** Laravel built-in (Breeze)
- **Version Control:** Git/GitHub

## 3. Functional Requirements

### 3.1 User Authentication
- Register, Login, Logout
- All cart actions restricted to authenticated users

### 3.2 Product Management
- Products with name, price, stock_quantity
- Browse available products
- Zero stock products cannot be added to cart

### 3.3 Shopping Cart System
- Cart stored in database (not session/localStorage)
- Cart associated with authenticated user
- Add, update quantity, remove products
- Quantity validation (≥1, ≤ stock)

### 3.4 Checkout & Sales Recording
- Simple checkout flow
- Reduce stock on checkout
- Persist sales data for reporting

## 4. Background Processing

### 4.1 Low Stock Notification (Queue Job)
- Dispatch job when product stock reaches low threshold
- Send email to dummy admin
- Asynchronous via Laravel Queue

### 4.2 Daily Sales Report (Scheduled Job)
- Run every evening via Laravel Scheduler
- Collect products sold that day
- Send summary report to admin email

## 5. Architecture Requirements
- SOLID Principles
- Service Layer for business logic
- Event-driven flow for notifications
- Separation of concerns (Livewire components thin)

## 6. Testing Requirements
- TDD approach
- Feature tests for workflows

## 7. Documentation Requirements
- README with setup instructions
- Queue and scheduler instructions
