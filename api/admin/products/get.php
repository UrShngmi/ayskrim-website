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
    
    // Fetch product details
    $stmt = $pdo->prepare('
        SELECT 
            p.*,
            c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.id = ? AND p.is_deleted = 0
    ');
    
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Product not found']);
        exit;
    }

    // Return the product data
    echo json_encode([
        'success' => true,
        'product' => $product
    ]);

} catch (Exception $e) {
    error_log('admin/products/get.php Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while fetching product details',
        'details' => $e->getMessage()
    ]);
}
?>
