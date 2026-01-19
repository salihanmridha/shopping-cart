# Shopping Cart

A simple e-commerce shopping cart system built with Laravel and Livewire.

## Features

- 🔐 **Authentication** - User registration, login, logout
- 🛒 **Shopping Cart** - Add, update, remove products
- 📦 **Stock Management** - Real-time stock validation
- 💳 **Checkout** - Simple checkout with order creation
- 📧 **Email Notifications** - Order confirmation, admin alerts
- 📊 **Daily Reports** - Automated sales reports in admin email
- ⚡ **Queue System** - Async job processing

---

## Tech Stack

- **Backend:** Laravel 12
- **Frontend:** Livewire 3
- **Styling:** Tailwind CSS
- **Database:** MySQL/MariaDB
- **Queue:** Database Driver
- **Mail:** Mailtrap (configurable)

---

## Prerequisites

- PHP 8.2+
- Composer
- Node.js & NPM
- MySQL/MariaDB
- Docker (optional)

---

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/shopping-cart.git
cd shopping-cart
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure Environment Variables

Edit `.env` file:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shopping_cart
DB_USERNAME=root
DB_PASSWORD=

# Queue (required for async jobs)
QUEUE_CONNECTION=database

# Mail (Mailtrap example)
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Shop Configuration
DUMMY_ADMIN_EMAIL=admin@example.com
SHOP_LOW_STOCK_THRESHOLD=10
```

### 5. Database Setup

```bash
php artisan migrate
php artisan db:seed
```

### 6. Build Assets

```bash
npm run build
```

### 7. Start the Application

```bash
php artisan serve
```

Visit: http://localhost:8000

---

## Queue & Background Jobs

This application uses queued jobs for:
- Low stock notifications
- Order confirmation emails
- New order admin notifications

### Running the Queue Worker

**Development (single process):**
```bash
php artisan queue:work
```

**Development (auto-restart on code changes):**
```bash
php artisan queue:listen
```

---

## Scheduled Tasks (Cron)

### Daily Sales Report

The system sends a daily sales report at 6:00 PM.

### Local Development

```bash
php artisan schedule:work
```

### Manual Execution

```bash
# Run for today
php artisan report:daily-sales

# Run for specific date
php artisan report:daily-sales --date=2026-01-19
```

---

## Project Structure

```
app/
├── Console/Commands/      # Artisan commands
├── Events/                # Domain events
├── Jobs/                  # Queued jobs
├── Listeners/             # Event listeners
├── Livewire/              # Livewire components
├── Mail/                  # Mailable classes
├── Models/                # Eloquent models
└── Services/              # Business logic services
    ├── CartService.php
    ├── CheckoutService.php
    ├── SalesReportService.php
    └── StockService.php
```

---

## Configuration

Application-specific settings in `config/shop.php`:

| Key | Description | Default |
|-----|-------------|---------|
| `dummy_admin_email` | Email for admin notifications | `admin@example.com` |
| `low_stock_threshold` | Stock level to trigger alerts | `10` |

---

## Email Notifications

| Email | Recipient | Trigger |
|-------|-----------|---------|
| Order Confirmation | Customer | After checkout |
| New Order Placed | Admin | After checkout |
| Low Stock Alert | Admin | Stock below threshold |
| Daily Sales Report | Admin | Scheduled (6 PM) |

---

## Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage
```

---

## Documentation

- [Requirements Summary](docs/REQUIREMENTS.md)
- [Development Plan](docs/DEVELOPMENT_PLAN.md)
- [Architecture Decisions & Assumptions](docs/DECISIONS.md)

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

