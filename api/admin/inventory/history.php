<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/functions.php';

header('Content-Type: application/json');
startSession();

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Get product ID from request
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validate inputs
if (!$productId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
    exit;
}

try {
    $pdo = DB::getConnection();

    // Fetch inventory history for the product
    $stmt = $pdo->prepare('
        SELECT
            il.*,
            u.full_name as admin_name
        FROM inventory_log il
        JOIN users u ON il.created_by = u.id
        WHERE il.product_id = ?
        ORDER BY il.created_at DESC
        LIMIT 100
    ');

    $stmt->execute([$productId]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the history data
    echo json_encode([
        'success' => true,
        'history' => $history
    ]);

} catch (Exception $e) {
    error_log('admin/inventory/history.php Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while fetching inventory history',
        'details' => $e->getMessage()
    ]);
}
