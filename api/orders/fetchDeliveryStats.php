<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

startSession();
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $pdo = DB::getConnection();
    $stmt = $pdo->prepare('
        SELECT o.total, o.discount, o.created_at, o.delivered_at, o.tracking_info, o.delivery_address
        FROM orders o
        WHERE o.status = "delivered"
        AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ');
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalOrders = count($orders);
    $totalTravelTime = 0;
    $totalDistance = 0;
    $totalPrepTime = 0;

    // Store coordinates (fixed for demo purposes)
    $storeCoords = ['latitude' => 14.5995, 'longitude' => 120.9842];

    foreach ($orders as $order) {
        $trackingInfo = json_decode($order['tracking_info'] ?? '{}', true);
        $deliveryCoords = [
            'latitude' => $trackingInfo['latitude'] ?? 14.5995,
            'longitude' => $trackingInfo['longitude'] ?? 120.9842
        ];

        // Calculate distance using Haversine formula
        $distance = calculateHaversineDistance(
            $storeCoords['latitude'],
            $storeCoords['longitude'],
            $deliveryCoords['latitude'],
            $deliveryCoords['longitude']
        );

        // Calculate travel time (in hours)
        $createdAt = new DateTime($order['created_at']);
        $deliveredAt = new DateTime($order['delivered_at']);
        $travelTime = ($deliveredAt->getTimestamp() - $createdAt->getTimestamp()) / 3600; // Convert to hours

        // Assume preparation time is 15 minutes if not available
        $prepTime = 0.25; // 15 minutes in hours

        $totalDistance += $distance;
        $totalTravelTime += $travelTime;
        $totalPrepTime += $prepTime;
    }

    // Compute averages
    $avgSpeed = $totalOrders > 0 ? ($totalDistance / $totalTravelTime) : 20; // km/h, default 20 km/h
    $avgPrepTime = $totalOrders > 0 ? ($totalPrepTime / $totalOrders) : 0.25; // hours, default 15 min
    $bufferTime = 0.167; // 10 minutes in hours

    echo json_encode([
        'success' => true,
        'avg_speed' => $avgSpeed,
        'avg_prep_time' => $avgPrepTime,
        'buffer_time' => $bufferTime
    ]);
} catch (PDOException $e) {
    error_log('Fetch delivery stats error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to fetch delivery stats']);
}

function calculateHaversineDistance($lat1, $lon1, $lat2, $lon2) {
    $R = 6371; // Earth's radius in km
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);

    $dLat = $lat2 - $lat1;
    $dLon = $lon2 - $lon1;

    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos($lat1) * cos($lat2) * sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * asin(sqrt($a));
    return $R * $c; // Distance in km
}
?>