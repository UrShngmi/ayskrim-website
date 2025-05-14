<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');
startSession();

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Get booking ID and status from request
$bookingId = intval($_GET['id'] ?? 0);
$status = $_GET['status'] ?? '';

// Validate inputs
if (!$bookingId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid booking ID']);
    exit;
}

$validStatuses = ['Pending', 'Confirmed', 'Completed', 'Cancelled'];
if (!in_array($status, $validStatuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid status']);
    exit;
}

try {
    $pdo = DB::getConnection();

    // Update booking status
    $stmt = $pdo->prepare('
        UPDATE events
        SET status = ?, updated_at = NOW()
        WHERE id = ? AND is_deleted = 0
    ');

    $result = $stmt->execute([$status, $bookingId]);

    if ($result && $stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Booking status updated successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Booking not found or no changes made']);
    }

} catch (Exception $e) {
    error_log('updateBookingStatus.php Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while updating booking status',
        'details' => $e->getMessage()
    ]);
}
