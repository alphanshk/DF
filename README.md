# Core PHP + MySQL E-Commerce System

Production-oriented e-commerce web app with **one common login** for three roles:
- Admin
- Seller
- User

## Tech Stack
- PHP (Core PHP, no framework)
- MySQL + PDO prepared statements
- HTML/CSS/JS + Bootstrap 5

## Features
- Common login (`/auth/login.php`) with role-based redirects
- Password hashing with `password_hash()` and verification with `password_verify()`
- Registration for User/Seller (Seller defaults to pending)
- Admin: dashboard, sellers approval/block, categories, all orders, users management
- Seller: dashboard, product CRUD, image upload validation, own orders
- User: browse products, search/filter, pagination, cart, checkout, order history, profile update
- CSRF token protection for forms
- Session-based auth and flash messages
- Input validation + output escaping via `htmlspecialchars`

## Folder Structure
```
/
|-- config/
|   |-- db.php
|   |-- helpers.php
|-- auth/
|   |-- login.php
|   |-- register.php
|   |-- logout.php
|-- admin/
|-- seller/
|-- user/
|-- includes/
|-- assets/
|-- uploads/
|-- database/
|   |-- ecommerce.sql
```

## Installation Steps
1. Clone/copy project into your web root (`htdocs`, `www`, etc.).
2. Create database and tables:
   - Import `database/ecommerce.sql` in MySQL.
3. Update DB credentials in `config/db.php`.
4. Ensure `uploads/` folder is writable by web server.
5. Start Apache + MySQL.
6. Open app in browser:
   - `/auth/login.php`

## Default Admin Login
- Email: `admin@example.com`
- Password: `Admin@123`

## Security Notes
- All database access uses PDO prepared statements.
- CSRF tokens are checked for state-changing forms.
- File uploads are limited by MIME and size (2MB).
- Session regeneration on login.

## Important
- Admin users are created manually (SQL seed included).
- Seller accounts are `pending` until admin approves.
