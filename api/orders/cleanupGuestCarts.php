<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json');

try {
    $pdo = DB::getConnection();
    $stmt = $pdo->prepare('DELETE FROM guest_carts WHERE created_at < NOW() - INTERVAL 48 HOUR');
    $stmt->execute();
    $affectedRows = $stmt->rowCount();
    echo json_encode(['success' => true, 'message' => "Deleted $affectedRows guest carts"]);
} catch (Exception $e) {
    error_log('cleanupGuestCarts.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error']);
}
?>