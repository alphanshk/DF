<?php
/**
 * Expense-related reusable business logic.
 */

declare(strict_types=1);

function getCategories(): array
{
    return ['Food', 'Travel', 'Shopping', 'Bills', 'Other'];
}

function sanitizeString(?string $value): string
{
    return trim((string) $value);
}

function validateExpenseInput(array $data): array
{
    $title = sanitizeString($data['title'] ?? '');
    $amount = $data['amount'] ?? '';
    $category = sanitizeString($data['category'] ?? 'Other');
    $date = sanitizeString($data['date'] ?? '');

    if ($title === '' || mb_strlen($title) > 150) {
        return [false, 'Title is required and must be under 150 characters.'];
    }

    if (!is_numeric($amount) || (float) $amount <= 0) {
        return [false, 'Amount must be a valid number greater than 0.'];
    }

    if (!in_array($category, getCategories(), true)) {
        return [false, 'Invalid category selected.'];
    }

    $dateObj = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
        return [false, 'Invalid date format.'];
    }

    return [true, 'Valid input'];
}

function getTotalExpenses(PDO $pdo, int $userId): float
{
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE user_id = :user_id');
    $stmt->execute(['user_id' => $userId]);
    return (float) $stmt->fetchColumn();
}

function getLastTransactions(PDO $pdo, int $userId, int $limit = 5): array
{
    $stmt = $pdo->prepare('SELECT id, title, amount, category, date FROM expenses WHERE user_id = :user_id ORDER BY date DESC, id DESC LIMIT :limit_num');
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit_num', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getMonthlySummary(PDO $pdo, int $userId): array
{
    $sql = 'SELECT DATE_FORMAT(date, "%Y-%m") AS month, COALESCE(SUM(amount), 0) AS total
            FROM expenses
            WHERE user_id = :user_id
            GROUP BY DATE_FORMAT(date, "%Y-%m")
            ORDER BY month DESC
            LIMIT 12';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $userId]);
    return array_reverse($stmt->fetchAll());
}

function getCategorySummary(PDO $pdo, int $userId): array
{
    $sql = 'SELECT category, COALESCE(SUM(amount), 0) AS total
            FROM expenses
            WHERE user_id = :user_id
            GROUP BY category
            ORDER BY total DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
}
