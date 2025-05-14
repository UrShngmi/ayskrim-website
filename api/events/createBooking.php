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
    echo json_encode(['success' => false, 'error' => 'Please log in to book an event']);
    exit;
}

// Get the current user ID
$userId = getCurrentUserId();

// Get and validate input data
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$requiredFields = ['package_type', 'package_price', 'event_date', 'start_time', 'end_time', 'venue_address', 'flavors', 'guest_count', 'payment_method'];
foreach ($requiredFields as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
        exit;
    }
}

// Validate payment method
$paymentMethod = filter_var($input['payment_method'], FILTER_SANITIZE_STRING);
// Ensure payment method matches exactly what's in the database enum
$validPaymentMethods = ['Credit Card', 'COD', 'PayPal', 'GCash'];
if (!in_array($paymentMethod, $validPaymentMethods)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid payment method']);
    exit;
}

// Validate package type
$packageType = filter_var($input['package_type'], FILTER_SANITIZE_STRING);
if ($packageType !== '1' && $packageType !== '2') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid package type']);
    exit;
}

// Validate package price
$packagePrice = filter_var($input['package_price'], FILTER_VALIDATE_FLOAT);
if (!$packagePrice) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid package price']);
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

// Validate venue address
$venueAddress = filter_var($input['venue_address'], FILTER_SANITIZE_STRING);

// Validate guest count
$guestCount = filter_var($input['guest_count'], FILTER_VALIDATE_INT);
if (!$guestCount) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid guest count']);
    exit;
}

// Validate flavors
$flavors = $input['flavors'];
if (!is_array($flavors) || empty($flavors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Please select at least one flavor']);
    exit;
}

// Validate flavor limit based on package
$flavorLimit = ($packageType === '1') ? 2 : 4;
if (count($flavors) > $flavorLimit) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => "You can only select up to $flavorLimit flavors for this package"]);
    exit;
}

// Get special requests (optional)
$specialRequests = isset($input['special_requests']) ? filter_var($input['special_requests'], FILTER_SANITIZE_STRING) : null;

try {
    $pdo = DB::getConnection();
    $pdo->beginTransaction();

    // Insert into events table
    $stmt = $pdo->prepare('
        INSERT INTO events (
            user_id,
            event_date,
            start_time,
            end_time,
            guest_count,
            venue_address,
            package_type,
            total_amount,
            special_requests,
            status,
            created_at,
            updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ');

    // Map package type to enum value
    $packageTypeEnum = ($packageType === '1') ? 'Basic' : 'Premium';

    $stmt->execute([
        $userId,
        $eventDate,
        $startTime,
        $endTime,
        $guestCount,
        $venueAddress,
        $packageTypeEnum,
        $packagePrice,
        $specialRequests,
        'Pending'
    ]);

    $eventId = $pdo->lastInsertId();

    // Store selected flavors in the special_requests field as JSON
    $flavorInfo = json_encode(['selected_flavors' => $flavors]);
    $stmt = $pdo->prepare('UPDATE events SET special_requests = JSON_MERGE_PATCH(COALESCE(special_requests, "{}"), ?) WHERE id = ?');
    $stmt->execute([$flavorInfo, $eventId]);

    // Generate transaction ID
    $transactionId = 'EVT' . date('YmdHis') . rand(1000, 9999);

    // Create payment record in the dedicated event_payments table
    $stmt = $pdo->prepare('
        INSERT INTO event_payments (
            event_id,
            user_id,
            amount,
            payment_method,
            transaction_id,
            payment_status,
            payment_details
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ');

    // Prepare payment details
    $paymentDetails = json_encode([
        'payment_method' => $paymentMethod,
        'payment_date' => date('Y-m-d H:i:s'),
        'event_date' => $eventDate,
        'package_type' => $packageTypeEnum,
        'venue_address' => $venueAddress,
        'guest_count' => $guestCount
    ]);

    $stmt->execute([
        $eventId,
        $userId,
        $packagePrice,
        $paymentMethod,
        $transactionId,
        'Pending',
        $paymentDetails
    ]);

    $pdo->commit();

    echo json_encode(['success' => true, 'event_id' => $eventId]);
} catch (Exception $e) {
    $pdo->rollBack();
    $errorMessage = $e->getMessage();
    $errorCode = $e->getCode();
    error_log('createBooking.php Error: ' . $errorMessage . ' (Code: ' . $errorCode . ')');

    // In development environment, return the actual error message for debugging
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'An error occurred: ' . $errorMessage,
            'code' => $errorCode,
            'trace' => $e->getTraceAsString()
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'An error occurred while processing your booking']);
    }
}
