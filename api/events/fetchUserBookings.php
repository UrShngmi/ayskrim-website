<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// Start session and check authentication
startSession();
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Get the current user ID
$userId = getCurrentUserId();

try {
    $pdo = DB::getConnection();

    // Fetch all bookings for the current user with payment information
    $stmt = $pdo->prepare('
        SELECT
            e.id,
            e.event_date,
            e.start_time,
            e.end_time,
            e.guest_count,
            e.venue_address,
            e.package_type,
            e.total_amount,
            e.special_requests,
            e.status,
            e.created_at,
            e.updated_at,
            ep.name as package_name,
            ep.description as package_description,
            ep.included_items,
            epay.payment_method,
            epay.payment_status,
            epay.transaction_id
        FROM events e
        LEFT JOIN event_packages ep ON e.package_type = ep.name
        LEFT JOIN event_payments epay ON e.id = epay.event_id
        WHERE e.user_id = ? AND e.is_deleted = 0
        ORDER BY e.event_date DESC
    ');

    $stmt->execute([$userId]);
    $allBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialize arrays for upcoming and past bookings
    $bookings = [
        'upcoming' => [],
        'past' => []
    ];

    // Sort bookings into upcoming and past based on status
    foreach ($allBookings as $booking) {
        // Parse special_requests JSON if it exists
        if ($booking['special_requests']) {
            $booking['special_requests_data'] = json_decode($booking['special_requests'], true);
        }

        // Parse included_items JSON if it exists
        if ($booking['included_items']) {
            $booking['included_items_data'] = json_decode($booking['included_items'], true);
        }

        // Add to appropriate array based on status
        if ($booking['status'] === 'Completed') {
            $bookings['past'][] = $booking;
        } else {
            $bookings['upcoming'][] = $booking;
        }
    }

    // Return the bookings data
    echo json_encode([
        'success' => true,
        'bookings' => $bookings
    ]);

} catch (Exception $e) {
    error_log('fetchUserBookings.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while fetching your bookings',
        'details' => $e->getMessage()
    ]);
}
?>
