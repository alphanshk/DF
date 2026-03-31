-- Smart Expense Tracker database schema and sample data
-- Create database
CREATE DATABASE IF NOT EXISTS expense_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE expense_tracker;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Expenses table
CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    category ENUM('Food', 'Travel', 'Shopping', 'Bills', 'Other') NOT NULL DEFAULT 'Other',
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_expenses_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Useful indexes for search/filter reports
CREATE INDEX idx_expenses_user_date ON expenses(user_id, date);
CREATE INDEX idx_expenses_category ON expenses(category);
CREATE INDEX idx_expenses_title ON expenses(title);

-- Sample user (password: Password@123)
INSERT INTO users (name, email, password)
VALUES ('Demo User', 'demo@example.com', '$2y$10$E00n2kBrIUBPdQ4rjEsQIuWTYybD8x41ulTZMR3hTVlVaR4F0zJYO')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Sample expenses for Demo User
INSERT INTO expenses (user_id, title, amount, category, date)
SELECT u.id, e.title, e.amount, e.category, e.date_value
FROM users u
JOIN (
    SELECT 'Grocery Shopping' AS title, 65.40 AS amount, 'Food' AS category, '2026-03-10' AS date_value
    UNION ALL SELECT 'Electricity Bill', 120.00, 'Bills', '2026-03-12'
    UNION ALL SELECT 'Cab Ride', 18.75, 'Travel', '2026-03-14'
    UNION ALL SELECT 'New Headphones', 89.99, 'Shopping', '2026-03-19'
    UNION ALL SELECT 'Coffee with Client', 14.50, 'Food', '2026-03-21'
) e
WHERE u.email = 'demo@example.com';
