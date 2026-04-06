# Multi Employee Attendance Web App

This is a complete **college mini-project** for managing attendance of multiple employees using PHP + MySQL.

## Features
- Registration/Login/Logout (bcrypt + sessions)
- Add/Edit/Delete Employees
- Mark daily attendance (Present/Absent/Leave)
- Dashboard cards and monthly chart (Chart.js)
- Responsive Bootstrap UI

## Folder Structure
- `config/` database + helper functions
- `includes/` common header/footer
- `assets/` css/js
- `db/employee_attendance_db.sql` database schema
- `register.php`, `login.php`, `dashboard.php`, `employees.php`, `attendance.php`

## XAMPP Setup
1. Place `employee-attendance` in `htdocs`.
2. Start Apache and MySQL.
3. Import `db/employee_attendance_db.sql` in phpMyAdmin.
4. Visit `http://localhost/employee-attendance/`.
