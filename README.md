# blazxcart - PHP + MySQL E-Commerce (Admin / Seller / User)

Production-ready Core PHP e-commerce system with role-based access and one common login page.

## Stack
- Frontend: HTML, CSS, JavaScript, Bootstrap 5
- Backend: Core PHP (PDO)
- Database: MySQL

## Folder Structure
- `config/app.php` : app name/base URL/upload settings
- `config/db.php` : PDO configuration
- `auth/login.php` : common login
- `auth/register.php` : user/seller registration
- `auth/logout.php` : logout
- `admin/dashboard.php` : admin operations
- `seller/dashboard.php` : seller products/orders
- `user/home.php` : storefront/cart/checkout/orders/profile
- `database/ecommerce.sql` : schema + seed data

## Setup Guide
1. Copy project to your web root (example: `htdocs/blazxcart`).
2. Create database and import SQL:
   ```bash
   mysql -u root -p < database/ecommerce.sql
   ```
3. Update DB credentials in `config/db.php`.
4. If app runs in subfolder, set `APP_URL` in `config/app.php`.
   - Example: `define('APP_URL', '/blazxcart');`
5. Ensure `uploads/` is writable:
   ```bash
   chmod -R 775 uploads
   ```
6. Start Apache/PHP and open:
   - `/auth/login.php`

## Default Admin (manual admin creation is supported too)
- Email: `admin@shop.com`
- Password: `Admin@123`

## Security Implemented
- `password_hash()` + `password_verify()`
- PDO prepared statements
- CSRF token validation on forms
- Session-based auth + session ID regeneration
- Role-based route guards
- Input validation + output escaping (`htmlspecialchars`)
- File upload MIME/size checks

## Feature Coverage
- Common login redirects by role
- Registration for user/seller (seller default = pending approval)
- Admin: dashboard stats, seller approval/block, category management, user management, all orders
- Seller: product CRUD, image upload, inventory management, own orders
- User: browse with search/filter/pagination, cart, checkout, order tracking/history, profile update
- Flash messages + responsive Bootstrap UI
