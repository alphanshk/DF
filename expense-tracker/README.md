# Smart Expense Tracker

Production-ready expense management web app using **Core PHP + MySQL + Vanilla JS + Chart.js**.

## Features
- User registration/login/logout with secure password hashing
- Session-based authentication guard
- Dashboard with total spending and last 5 transactions
- Add/edit/delete expenses (AJAX)
- Search by title, category filter, and date range filter
- Monthly and category-wise charts
- Pagination for expense list
- CSV export
- Dark mode toggle
- Toast notifications

## Project Structure

```
expense-tracker/
├── index.php
├── register.php
├── dashboard.php
├── logout.php
├── api/
│   ├── add_expense.php
│   ├── update_expense.php
│   ├── delete_expense.php
│   ├── fetch_expenses.php
├── includes/
│   ├── db.php
│   ├── auth.php
│   ├── expense_functions.php
├── assets/
│   ├── css/style.css
│   ├── js/script.js
└── database.sql
```

## Run on XAMPP
1. Copy `expense-tracker` folder into `htdocs` (for example: `C:/xampp/htdocs/expense-tracker`).
2. Start **Apache** and **MySQL** from XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin` and import `database.sql`.
4. Visit `http://localhost/expense-tracker` in your browser.
5. Demo login:
   - Email: `demo@example.com`
   - Password: `Password@123`

## Notes
- DB credentials are in `includes/db.php` (default XAMPP values).
- Uses PDO prepared statements for SQL injection protection.
