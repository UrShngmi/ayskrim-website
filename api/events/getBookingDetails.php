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

// Get booking ID from request
$bookingId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$bookingId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid booking ID']);
    exit;
}

try {
    $pdo = DB::getConnection();

    // Fetch booking details with user and payment info
    $stmt = $pdo->prepare('
        SELECT
            e.*,
            u.full_name,
            u.email,
            u.phone,
            ep.payment_method,
            ep.transaction_id,
            ep.payment_status,
            ep.payment_details,
            ep.created_at as payment_date
        FROM events e
        JOIN users u ON e.user_id = u.id
        LEFT JOIN event_payments ep ON e.id = ep.event_id
        WHERE e.id = ? AND e.is_deleted = 0
    ');

    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Booking not found']);
        exit;
    }

    // Parse special_requests JSON if it exists
    if ($booking['special_requests']) {
        try {
            $booking['special_requests_data'] = json_decode($booking['special_requests'], true);
        } catch (Exception $e) {
            // If JSON parsing fails, keep the original string
            $booking['special_requests_data'] = null;
        }
    }

    // Parse payment_details JSON if it exists
    if ($booking['payment_details']) {
        try {
            $booking['payment_details_data'] = json_decode($booking['payment_details'], true);
        } catch (Exception $e) {
            // If JSON parsing fails, keep the original string
            $booking['payment_details_data'] = null;
        }
    }

    // Return the booking data
    echo json_encode([
        'success' => true,
        'booking' => $booking
    ]);

} catch (Exception $e) {
    error_log('getBookingDetails.php Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while fetching booking details',
        'details' => $e->getMessage()
    ]);
}
