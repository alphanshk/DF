# Student Expense Tracker Web Application

A beginner-friendly mini project using **HTML, CSS, JavaScript, PHP, MySQL**.

## Folder Structure

```text
student-expense-tracker/
├── assets/
│   ├── css/style.css
│   └── js/
│       ├── app.js
│       └── charts.js
├── config/db.php
├── database/student_expense_tracker.sql
├── includes/
│   ├── auth.php
│   ├── footer.php
│   ├── functions.php
│   └── header.php
├── dashboard.php
├── expenses.php
├── index.php
├── login.php
├── logout.php
├── profile.php
├── register.php
├── reports.php
└── README.md
```

## Setup Steps
1. Import `database/student_expense_tracker.sql` in MySQL.
2. Update DB credentials in `config/db.php`.
3. Put this folder in htdocs/www root.
4. Open `http://localhost/student-expense-tracker/`.

## Features Implemented
- Student registration/login/logout with sessions.
- Add/edit/delete expenses.
- Monthly allowance setup and balance alert.
- Dashboard with monthly total, recent entries, weekly summary.
- Reports with category pie chart + 30-day trend bar chart using Chart.js.
- Mobile responsive sidebar layout.
- Dark mode toggle.
