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
$productId = isset($_POST['id']) ? intval($_POST['id']) : 0;

// Validate inputs
if (!$productId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
    exit;
}

try {
    $pdo = DB::getConnection();
    
    // Check if product exists
    $stmt = $pdo->prepare('SELECT id FROM products WHERE id = ? AND is_deleted = 0');
    $stmt->execute([$productId]);
    
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Product not found']);
        exit;
    }
    
    // Soft delete the product
    $stmt = $pdo->prepare('UPDATE products SET is_deleted = 1, updated_at = NOW() WHERE id = ?');
    $result = $stmt->execute([$productId]);
    
    if (!$result) {
        throw new Exception('Failed to delete product.');
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Product deleted successfully'
    ]);

} catch (Exception $e) {
    error_log('admin/products/delete.php Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while deleting product',
        'details' => $e->getMessage()
    ]);
}
?>
