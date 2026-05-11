# TrackPOS - Point of Sale & Inventory Management System

A full-stack Point of Sale (POS) and Inventory Management system built with Laravel (PHP) and Blade templates.

## Features

### Core Modules
1. **POS (Cashier Screen)**
   - Product search and barcode input
   - Quantity adjustment
   - Apply discounts & tax
   - Multiple payment methods (cash, card, transfer)
   - Generate receipt

2. **Product & Inventory Management**
   - CRUD products
   - Categories, units, brands management
   - Stock quantity tracking
   - Low stock alerts

3. **Sales & Purchases**
   - Record sales transactions
   - Purchase (stock-in) module
   - Invoice system

4. **Customers & Suppliers**
   - Add/edit/delete customers & suppliers
   - View transaction history per user

5. **Reports & Analytics**
   - Daily/monthly sales reports
   - Product sales reports
   - Stock reports
   - Profit/loss summary

6. **User Roles & Permissions**
   - Admin (full access)
   - Manager (most access except user management)
   - Cashier (limited access)

## Tech Stack

- **Backend:** Laravel 13 (PHP 8.4)
- **Frontend:** Blade templates + Bootstrap 5
- **Database:** SQLite (can be MySQL)
- **Auth:** Session-based authentication

## Installation

### Prerequisites
- PHP 8.4+
- Composer
- Web server (Apache/Nginx)

### Setup Steps

1. **Install Dependencies**
   ```bash
   cd /workspace/project/trackpos
   composer install
   ```

2. **Copy Environment File**
   ```bash
   cp .env.example .env
   ```

3. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

4. **Run Migrations**
   ```bash
   php artisan migrate
   ```

5. **Seed Database (Demo Data)**
   ```bash
   php artisan db:seed
   ```

6. **Start Development Server**
   ```bash
   php artisan serve
   ```

7. **Access the Application**
   - URL: http://localhost:8000
   - Login: http://localhost:8000/login

## Demo Accounts

| Role   | Email                  | Password |
|--------|------------------------|----------|
| Admin  | admin@trackpos.com    | admin123 |
| Manager| manager@trackpos.com  | manager123 |
| Cashier| cashier@trackpos.com  | cashier123 |

## Project Structure

```
trackpos/
├── app/
│   ├── Http/Controllers/    # Controllers
│   └── Models/               # Eloquent Models
├── database/
│   ├── migrations/          # Database Migrations
│   └── seeders/             # Database Seeders
├── resources/views/          # Blade Templates
│   ├── layouts/             # Layouts
│   ├── auth/                # Authentication
│   ├── pos/                 # POS Module
│   ├── products/            # Products
│   ├── sales/               # Sales
│   └── reports/             # Reports
└── routes/
    └── web.php              # Web Routes
```

## API Endpoints

### Authentication
- `GET /login` - Login page
- `POST /login` - Login
- `POST /logout` - Logout

### POS
- `GET /pos` - POS interface
- `GET /pos/search` - Search products
- `GET /pos/barcode` - Find by barcode
- `POST /pos/sale` - Complete sale

### Products
- `GET /products` - Product list
- `POST /products` - Create product
- `PUT /products/{id}` - Update product
- `DELETE /products/{id}` - Delete product

### Sales
- `GET /sales` - Sales list
- `GET /sales/{id}` - Sale details

### Reports
- `GET /reports` - Reports dashboard
- `GET /reports/daily-sales` - Daily sales
- `GET /reports/product-sales` - Product sales
- `GET /reports/stock` - Stock report
- `GET /reports/profit-loss` - Profit/Loss

## Database Schema

### Tables
- `users` - User accounts with roles
- `categories` - Product categories
- `brands` - Product brands
- `units` - Measurement units
- `products` - Product inventory
- `customers` - Customer records
- `suppliers` - Supplier records
- `sales` - Sales transactions
- `sale_items` - Individual sale items
- `purchases` - Purchase orders
- `purchase_items` - Purchase items
- `returns` - Returns
- `stock_adjustments` - Stock changes
- `settings` - Application settings

## License

MIT License
