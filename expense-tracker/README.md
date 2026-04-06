# Smart Expense Tracker (PHP + MySQL)

A complete mini-project built with **HTML, CSS, JavaScript, PHP, and MySQL**.

## Folder Structure

```
expense-tracker/
├── assets/
│   ├── css/style.css
│   └── js/main.js
├── config/
│   ├── database.php
│   └── functions.php
├── db/
│   └── smart_expense_tracker.sql
├── includes/
│   ├── footer.php
│   └── header.php
├── index.php
├── login.php
├── logout.php
├── register.php
├── dashboard.php
├── expenses.php
├── expense_add.php
├── expense_edit.php
├── expense_delete.php
├── budget.php
├── profile.php
└── README.md
```

## Features

- User registration/login/logout with bcrypt hashing.
- Session-based authentication.
- Add, edit, delete, search, and filter expenses.
- Monthly budget setup and over-budget warning.
- Dashboard cards, recent transactions, and charts (Chart.js).
- Dark mode toggle.
- Responsive Bootstrap-based UI.

## XAMPP Setup Steps

1. Copy `expense-tracker` folder to `htdocs`.
2. Start **Apache** and **MySQL** from XAMPP Control Panel.
3. Open phpMyAdmin and import: `expense-tracker/db/smart_expense_tracker.sql`.
4. Verify DB credentials in `config/database.php` (defaults are set for XAMPP).
5. Open: `http://localhost/expense-tracker/`

## Demo Flow

1. Register a new account.
2. Login and set monthly budget.
3. Add daily expenses from Manage Expenses.
4. View dashboard totals, remaining balance, and charts.

