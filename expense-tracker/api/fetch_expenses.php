<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/expense_functions.php';

if (!isLoggedIn()) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = currentUserId();
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$fromDate = trim($_GET['from_date'] ?? '');
$toDate = trim($_GET['to_date'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;
$export = ($_GET['export'] ?? '') === 'csv';

$where = ['user_id = :user_id'];
$params = ['user_id' => $userId];

if ($search !== '') {
    $where[] = 'title LIKE :search';
    $params['search'] = '%' . $search . '%';
}

if ($category !== '' && in_array($category, getCategories(), true)) {
    $where[] = 'category = :category';
    $params['category'] = $category;
}

if ($fromDate !== '') {
    $where[] = 'date >= :from_date';
    $params['from_date'] = $fromDate;
}

if ($toDate !== '') {
    $where[] = 'date <= :to_date';
    $params['to_date'] = $toDate;
}

$whereClause = implode(' AND ', $where);

$countSql = "SELECT COUNT(*) FROM expenses WHERE {$whereClause}";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $limit));

$dataSql = "SELECT id, title, amount, category, date FROM expenses WHERE {$whereClause} ORDER BY date DESC, id DESC";
if (!$export) {
    $dataSql .= ' LIMIT :limit OFFSET :offset';
}
$stmt = $pdo->prepare($dataSql);

foreach ($params as $key => $value) {
    $stmt->bindValue(':' . $key, $value);
}

if (!$export) {
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
}

$stmt->execute();
$expenses = $stmt->fetchAll();

if ($export) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="expenses_export.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Title', 'Amount', 'Category', 'Date']);

    foreach ($expenses as $expense) {
        fputcsv($output, [$expense['title'], $expense['amount'], $expense['category'], $expense['date']]);
    }
    fclose($output);
    exit;
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'expenses' => $expenses,
    'pagination' => [
        'page' => $page,
        'limit' => $limit,
        'total_rows' => $totalRows,
        'total_pages' => $totalPages,
    ],
]);
