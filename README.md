# LASU Marketplace

A verified campus e-commerce platform for Lagos State University (LASU) students and entrepreneurs. Built with Laravel 13, Bootstrap 5, and Paystack.

---

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [Running the App](#running-the-app)
- [Sample Accounts](#sample-accounts)
- [Features by Role](#features-by-role)
- [Project Structure](#project-structure)
- [Common Commands](#common-commands)
- [Troubleshooting](#troubleshooting)

---

## Requirements

Make sure you have the following installed before you begin:

| Tool | Minimum Version |
|------|----------------|
| PHP | 8.2+ |
| Composer | 2.x |
| MySQL | 8.0+ |
| Node.js | 18+ |
| NPM | 9+ |

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/your-username/lasu-marketplace.git
cd lasu-marketplace
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install JavaScript dependencies

```bash
npm install
```

### 4. Copy the environment file

```bash
cp .env.example .env
```

### 5. Generate the application key

```bash
php artisan key:generate
```

---

## Configuration

Open `.env` and update the following sections:

### Database

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lasu_marketplace
DB_USERNAME=root
DB_PASSWORD=your_password
```

Create the database in MySQL before running migrations:

```sql
CREATE DATABASE lasu_marketplace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Mail (Gmail SMTP)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=yourgmail@gmail.com
MAIL_PASSWORD=xxxx xxxx xxxx xxxx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=yourgmail@gmail.com
MAIL_FROM_NAME="LASU Marketplace"
```

> The `MAIL_PASSWORD` is a **Google App Password**, not your Gmail password.
> Generate one at: https://myaccount.google.com/apppasswords
> (Requires 2-Step Verification to be enabled on your Google account)

### Paystack (for online payments)

```env
PAYSTACK_PUBLIC_KEY=pk_test_xxxxxxxxxxxxxxxxxxxxxxxx
PAYSTACK_SECRET_KEY=sk_test_xxxxxxxxxxxxxxxxxxxxxxxx
```

> Get your test keys from: https://dashboard.paystack.com/#/settings/developer

### Cache & Session (recommended for local development)

```env
CACHE_STORE=file
SESSION_DRIVER=file
```

---

## Database Setup

### Run migrations

```bash
php artisan migrate
```

### Seed the database with sample data

```bash
php artisan db:seed
```

This creates:
- 10 campus zones (real LASU locations)
- 10 product categories
- 1 admin account
- 3 seller accounts with stores and listings
- 2 buyer accounts

### Link storage for image uploads

```bash
php artisan storage:link
```

---

## Running the App

### Start the development server

```bash
php artisan serve
```

The app will be available at: **http://localhost:8000**

### Build frontend assets (optional, for Vite)

```bash
npm run dev
```

---

## Sample Accounts

All sample accounts use the password: **`password`**

### Admin

| Field | Value |
|-------|-------|
| Email | admin@lasu.edu.ng |
| Password | password |
| Access | Full admin panel at `/admin/dashboard` |

### Sellers

| Name | Email | Store |
|------|-------|-------|
| Amara Books | amara@lasu.edu.ng | Amara Books & Stationery |
| Chidi Tech Store | chidi@lasu.edu.ng | Chidi Tech Hub |
| Fatima Fashion Hub | fatima@lasu.edu.ng | Fatima's Fashion |

Sellers log in and are redirected to `/seller/dashboard`

### Buyers

| Name | Email |
|------|-------|
| Emeka Obi | emeka@lasu.edu.ng |
| Ngozi Adeyemi | ngozi@lasu.edu.ng |

Buyers log in and are redirected to the homepage

---

## Features by Role

### Buyer
- Browse and search listings by category, price, and condition
- Add items to cart and adjust quantities
- Checkout with cash on meetup or online payment (Paystack)
- Message sellers directly from any listing
- Propose, accept, or counter campus meetup locations
- Track order status from pending through to completion
- Leave star ratings and reviews after completed orders
- Report suspicious listings, users, or stores

### Seller
- Create and manage a storefront with logo and banner
- Add listings with up to 5 images, pricing, and stock levels
- Receive and manage customer orders
- Confirm orders, mark items as handed over
- Communicate with buyers through the messaging system
- View dashboard with revenue and order statistics

### Admin
- View platform-wide stats and activity
- Verify or suspend seller stores
- Moderate and remove listings
- Suspend or activate user accounts
- Review and resolve abuse reports
- Manage campus meetup zones (add, edit, delete)

---

## Project Structure

```
lasu-marketplace/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/          # Login, Register, Profile
│   │   │   ├── Buyer/         # Cart, Orders
│   │   │   ├── Seller/        # Dashboard, Store, Listings, Orders
│   │   │   ├── Admin/         # Dashboard, Users, Stores, Listings, Reports, Zones
│   │   │   ├── ConversationController.php
│   │   │   ├── MeetupProposalController.php
│   │   │   ├── PaystackController.php
│   │   │   ├── ReviewController.php
│   │   │   └── ReportController.php
│   │   └── Middleware/
│   │       └── RoleMiddleware.php
│   ├── Models/                # 15 Eloquent models
│   └── Notifications/         # 4 database notifications
├── database/
│   ├── migrations/            # 16 migration files
│   └── seeders/               # 5 seeders
├── resources/views/
│   ├── layouts/app.blade.php  # Master layout
│   ├── auth/                  # Login, Register, Profile, Verify
│   ├── buyer/                 # Cart, Checkout, Orders
│   ├── seller/                # Dashboard, Store, Listings, Orders
│   ├── admin/                 # Dashboard, Users, Stores, Listings, Reports, Zones
│   ├── conversations/         # Inbox, Chat
│   └── listings/              # Browse, Detail
└── routes/web.php             # All application routes
```

---

## Common Commands

```bash
# Clear all caches
php artisan optimize:clear

# Re-run migrations from scratch (WARNING: deletes all data)
php artisan migrate:fresh --seed

# Check registered routes
php artisan route:list

# Open interactive shell
php artisan tinker

# Rebuild composer autoloader (fixes class not found errors)
composer dump-autoload

# Create storage symlink (run once after setup)
php artisan storage:link
```

---

## Troubleshooting

### "Class not found" errors
```bash
composer dump-autoload
php artisan optimize:clear
```

### "Table does not exist" errors
```bash
php artisan migrate
```

### "View not found" errors
Make sure all blade files are in the correct folder under `resources/views/`. Refer to the project structure above.

### Images not displaying
```bash
php artisan storage:link
```
Then confirm your `APP_URL` in `.env` matches the URL you are accessing the app on (e.g. `http://localhost:8000`).

### Paystack payments not working
- Confirm you are using **test keys** for local development
- Ensure your `APP_URL` is publicly accessible for webhooks (use [ngrok](https://ngrok.com) to expose localhost during testing)
- Check that the Paystack webhook URL is set to `https://your-domain.com/webhooks/paystack` in your Paystack dashboard

### Mail not sending
- Confirm 2-Step Verification is enabled on your Gmail account
- Make sure you generated an **App Password** (not your regular password)
- Test with: `php artisan tinker` then `Mail::raw('Test', fn($m) => $m->to('test@example.com')->subject('Test'));`

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 13 (PHP 8.2+) |
| Frontend | Blade + Bootstrap 5 + Bootstrap Icons |
| Database | MySQL 8 |
| Authentication | Laravel built-in + custom RoleMiddleware |
| Payments | Paystack API |
| File Storage | Laravel local disk (public) |
| Notifications | Laravel database notifications |

---

## License

This project was developed as an undergraduate research project for Lagos State University.