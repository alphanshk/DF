# Expense Tracker (Core PHP + MySQL)

A clean, single-purpose expense tracker website with only:
- User registration
- User login/logout
- Personal expense dashboard

## Tech Stack
- PHP (Core PHP)
- MySQL + PDO
- Bootstrap 5

## Features
- Secure authentication (`password_hash`, `password_verify`)
- CSRF protection on forms
- Add expense entries
- Delete expense entries
- Monthly view selector with month-wise expense list
- Summary cards for all-time and selected-month insights
- Category-wise monthly breakdown

## Project Structure
```
/
|-- auth/
|   |-- login.php
|   |-- register.php
|   |-- logout.php
|-- config/
|   |-- db.php
|   |-- helpers.php
|-- database/
|   |-- expense_tracker.sql
|-- includes/
|-- user/
|   |-- home.php
|-- assets/
|   |-- css/style.css
|   |-- js/app.js
|-- index.php
```

## Installation
1. Copy this project into your PHP web root.
2. Import `database/expense_tracker.sql` into MySQL.
3. Set DB credentials in `config/db.php`.
4. Open `/auth/register.php` and create your account.
5. Login and start tracking expenses.
