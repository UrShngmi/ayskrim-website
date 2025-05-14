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
    echo json_encode(['success' => false, 'error' => 'Please log in to reschedule an event']);
    exit;
}

// Get the current user ID
$userId = getCurrentUserId();

// Get and validate input data
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$requiredFields = ['event_id', 'event_date', 'start_time', 'end_time'];
foreach ($requiredFields as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
        exit;
    }
}

// Validate event ID
$eventId = filter_var($input['event_id'], FILTER_VALIDATE_INT);
if (!$eventId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid event ID']);
    exit;
}

// Validate event date
$eventDate = filter_var($input['event_date'], FILTER_SANITIZE_STRING);
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid event date format']);
    exit;
}

// Validate times
$startTime = filter_var($input['start_time'], FILTER_SANITIZE_STRING);
$endTime = filter_var($input['end_time'], FILTER_SANITIZE_STRING);
if (!preg_match('/^\d{2}:\d{2}$/', $startTime) || !preg_match('/^\d{2}:\d{2}$/', $endTime)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid time format']);
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
    
    // Check if the event is in a status that can be rescheduled (not Completed or Cancelled)
    if ($event['status'] === 'Completed' || $event['status'] === 'Cancelled') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Cannot reschedule a completed or cancelled event']);
        exit;
    }
    
    // Update the event with new date and times
    $stmt = $pdo->prepare('
        UPDATE events 
        SET event_date = ?, start_time = ?, end_time = ?, updated_at = NOW() 
        WHERE id = ? AND user_id = ?
    ');
    $stmt->execute([$eventDate, $startTime, $endTime, $eventId, $userId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Event rescheduled successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to reschedule event']);
    }
} catch (Exception $e) {
    error_log('rescheduleBooking.php Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'An error occurred while rescheduling your event']);
}
