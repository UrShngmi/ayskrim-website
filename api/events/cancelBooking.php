<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');
startSession();

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Please log in to cancel an event']);
    exit;
}

// Get the current user ID
$userId = getCurrentUserId();

// Get and validate input data
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($input['event_id']) || empty($input['event_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required field: event_id']);
    exit;
}

// Validate event ID
$eventId = filter_var($input['event_id'], FILTER_VALIDATE_INT);
if (!$eventId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid event ID']);
    exit;
}

try {
    $pdo = DB::getConnection();
    
    // First, check if the event exists and belongs to the current user
    $stmt = $pdo->prepare('
        SELECT id, status FROM events 
        WHERE id = ? AND user_id = ? AND is_deleted = 0
    ');
    $stmt->execute([$eventId, $userId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Event not found or you do not have permission to modify it']);
        exit;
    }
    
    // Check if the event is in a status that can be cancelled (not Completed or already Cancelled)
    if ($event['status'] === 'Completed' || $event['status'] === 'Cancelled') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Cannot cancel a completed or already cancelled event']);
        exit;
    }
    
    // Update the event status to Cancelled
    $stmt = $pdo->prepare('
        UPDATE events 
        SET status = "Cancelled", updated_at = NOW() 
        WHERE id = ? AND user_id = ?
    ');
    $stmt->execute([$eventId, $userId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Event cancelled successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to cancel event']);
    }
} catch (Exception $e) {
    error_log('cancelBooking.php Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'An error occurred while cancelling your event']);
}
