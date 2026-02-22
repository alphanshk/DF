# PHP + MySQL Multi-Role E-Commerce System

Production-ready core PHP e-commerce project with **Admin**, **Seller**, and **User** roles using one common login page.

## Features
- Common login (`/auth/login.php`) using `email + password`
- Secure password hashing (`password_hash`) and verification (`password_verify`)
- Role-based redirects and access guards
- Account statuses (`active`, `blocked`, `pending`)
- Admin dashboard with seller approvals, user/category/order management
- Seller dashboard with product and inventory management + image upload
- User storefront with search, category filter, pagination, cart, checkout, orders, profile
- CSRF protection, output escaping, prepared statements, session regeneration
- Flash messages and responsive Bootstrap UI

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
|   |-- dashboard.php
|   |-- sellers.php
|   |-- users.php
|   |-- categories.php
|   |-- orders.php
|-- seller/
|   |-- dashboard.php
|   |-- products.php
|   |-- edit_product.php
|   |-- orders.php
|-- user/
|   |-- home.php
|   |-- cart.php
|   |-- checkout.php
|   |-- orders.php
|   |-- profile.php
|-- includes/
|   |-- header.php
|   |-- footer.php
|-- assets/
|   |-- style.css
|-- uploads/
|-- database/
|   |-- ecommerce.sql
|-- index.php
```

## Installation Steps
1. Place this project in your web root (e.g. `htdocs/DF`).
2. Create MySQL database and tables:
   ```bash
   mysql -u root -p < database/ecommerce.sql
   ```
3. Update DB credentials in `config/db.php`.
4. Ensure `uploads/` is writable by your web server.
5. Start Apache/Nginx + PHP and open:
   - `http://localhost/DF/auth/login.php`

## Default Admin Login
- Email: `admin@example.com`
- Password: `Admin@123`

## Security Notes
- Uses PDO with prepared statements.
- CSRF token validated on all mutating forms.
- Escaping via `htmlspecialchars` helper.
- Session-based auth and role guards.
- File upload whitelist + size limit.

## Optional Enhancements
- Add payment gateway integration.
- Add email notifications for order updates.
- Add admin order status update page.
- Add seller-level order detail view.
