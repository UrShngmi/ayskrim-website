<?php
// Customer orders page
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/functions.php';

// Ensure user is customer
requireCustomer();

// Get current customer user
$customer = getCurrentUser();

// Set page for navbar highlighting
$page = 'orders';

// Initialize data structures
$orders = [
    'active' => [],
    'past' => []
];

$bookings = [
    'upcoming' => [],
    'past' => []
];

$metrics = [
    'active' => 0,
    'total' => 0,
    'upcoming' => 0,
    'avgDelivery' => '0 min'
];

$recommendedItems = [];

// Handle tab navigation
$mainTab = isset($_GET['mainTab']) ? $_GET['mainTab'] : 'orders';
$secondaryTab = isset($_GET['secondaryTab']) ? $_GET['secondaryTab'] : ($mainTab === 'orders' ? 'active' : 'upcoming');

// Get database connection and user ID
$pdo = DB::getConnection();
$userId = getCurrentUserId();

// STEP 1: Get all orders for this user
$stmt = $pdo->prepare('
    SELECT id, tracking_code, shipping_address, created_at, total_amount, order_status, payment_status, estimated_delivery_time
    FROM orders
    WHERE user_id = ?
    ORDER BY id DESC
');
$stmt->execute([$userId]);
$allOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// STEP 2: Initialize arrays for active and past orders
$orders['active'] = [];
$orders['past'] = [];

// STEP 3: Sort orders into active and past based on order_status
foreach ($allOrders as $order) {
    // Debug: Log order status to error log
    error_log("Order ID: " . $order['id'] . ", Status: " . $order['order_status']);

    if ($order['order_status'] === 'Delivered') {
        // This is a past order - set display status to "Completed"
        $order['display_status'] = 'Completed';
        $order['status_class'] = 'completed';
        $orders['past'][] = $order;
    } else {
        // This is an active order - keep original status
        $order['display_status'] = $order['order_status'];
        $order['status_class'] = strtolower(str_replace(' ', '-', $order['order_status']));
        $orders['active'][] = $order;
    }
}

// STEP 4: Get all bookings for this user
try {
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

        // Format date and time for display
        $eventDate = new DateTime($booking['event_date']);
        $startTime = new DateTime($booking['start_time']);
        $endTime = new DateTime($booking['end_time']);

        $booking['formatted_date'] = $eventDate->format('F j, Y');
        $booking['formatted_time'] = $startTime->format('g:i A') . ' - ' . $endTime->format('g:i A');

        // Add to appropriate array based on status
        if ($booking['status'] === 'Completed') {
            $bookings['past'][] = $booking;
        } else {
            $bookings['upcoming'][] = $booking;
        }
    }
} catch (Exception $e) {
    error_log('Error fetching bookings: ' . $e->getMessage());
}

// Update metrics
$metrics['active'] = count($orders['active']);
$metrics['total'] = count($orders['active']) + count($orders['past']);
$metrics['upcoming'] = count($bookings['upcoming']);

// Debug information - will be visible only to admins
if (isset($_GET['debug']) && $_GET['debug'] === 'true') {
    echo '<div style="background: #f8f9fa; padding: 15px; margin: 15px; border: 1px solid #ddd; border-radius: 5px;">';
    echo '<h3>Debug Information</h3>';
    echo '<h4>All Orders:</h4>';
    echo '<pre>';
    foreach ($allOrders as $order) {
        echo "Order ID: {$order['id']}, Status: {$order['order_status']}\n";
    }
    echo '</pre>';

    echo '<h4>Active Orders:</h4>';
    echo '<pre>';
    foreach ($orders['active'] as $order) {
        echo "Order ID: {$order['id']}, Status: {$order['order_status']}, Display Status: {$order['display_status']}\n";
    }
    echo '</pre>';

    echo '<h4>Past Orders:</h4>';
    echo '<pre>';
    foreach ($orders['past'] as $order) {
        echo "Order ID: {$order['id']}, Status: {$order['order_status']}, Display Status: {$order['display_status']}\n";
    }
    echo '</pre>';

    echo '<h4>Upcoming Bookings:</h4>';
    echo '<pre>';
    foreach ($bookings['upcoming'] as $booking) {
        echo "Booking ID: {$booking['id']}, Status: {$booking['status']}, Date: {$booking['formatted_date']}\n";
    }
    echo '</pre>';

    echo '<h4>Past Bookings:</h4>';
    echo '<pre>';
    foreach ($bookings['past'] as $booking) {
        echo "Booking ID: {$booking['id']}, Status: {$booking['status']}, Date: {$booking['formatted_date']}\n";
    }
    echo '</pre>';
    echo '</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Ayskrim Ice Cream Shop - Order tracking and booking management">
    <meta name="keywords" content="ice cream, orders, bookings, ayskrim, delivery, tracking">
    <title>Orders & Bookings - Ayskrim</title>
    <link rel="icon" href="/ayskrimWebsite/assets/images/favicon.ico" type="image/x-icon">
    <meta name="theme-color" content="#ec4899">

    <!-- Shared Styles -->
    <link rel="stylesheet" href="/ayskrimWebsite/shared/header/header.css">
    <link rel="stylesheet" href="/ayskrimWebsite/shared/navbar/navbar.css">
    <link rel="stylesheet" href="/ayskrimWebsite/shared/footer/footer.css">

    <!-- Page-Specific Styles -->
    <link rel="stylesheet" href="calendar.css">
    <link rel="stylesheet" href="orders.css">

    <!-- External Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
</head>
<body>
    <div class="min-h-screen">
        <!-- Header and Navbar -->
        <?php include __DIR__ . '/../../shared/header/header.php'; ?>
        <?php include __DIR__ . '/../../shared/navbar/navbar.php'; ?>

        <!-- Main Content -->
        <div class="order-page">
            <div class="main-content">
                <div class="container mx-auto px-4 py-8">
                    <!-- Main Tabs -->
                    <div class="main-tabs-container">
                        <div class="main-tabs-wrapper">
                            <div class="main-tabs">
                                <div class="main-tabs-slider" id="main-tabs-slider"></div>
                                <button class="main-tab-btn <?php echo $mainTab === 'orders' ? 'active' : ''; ?>" data-tab="orders">
                                    <i class="fas fa-truck"></i>
                                    <span>Orders</span>
                                </button>
                                <button class="main-tab-btn <?php echo $mainTab === 'bookings' ? 'active' : ''; ?>" data-tab="bookings">
                                    <i class="fas fa-calendar"></i>
                                    <span>Bookings</span>
                                </button>
                            </div>
                            <div class="main-tabs-glow"></div>
                        </div>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- Orders Tab -->
                        <div class="tab-pane <?php echo $mainTab === 'orders' ? 'active' : ''; ?>" id="orders-tab">
                            <div class="tab-content-inner">
                                <!-- Order Status Tabs -->
                                <div class="secondary-tabs-container">
                                    <div class="secondary-tabs">
                                        <div class="secondary-tabs-slider" id="orders-tabs-slider"></div>
                                        <button class="secondary-tab-btn <?php echo $secondaryTab === 'active' ? 'active' : ''; ?>" data-tab="active-orders">
                                            <i class="fas fa-truck"></i>
                                            <span>Active Orders</span>
                                            <span class="badge-count"><?php echo count($orders['active']); ?></span>
                                        </button>
                                        <button class="secondary-tab-btn <?php echo $secondaryTab === 'past' ? 'active' : ''; ?>" data-tab="past-orders">
                                            <i class="fas fa-history"></i>
                                            <span>Past Orders</span>
                                            <span class="badge-count"><?php echo count($orders['past']); ?></span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Orders Content -->
                                <div class="secondary-tab-content">
                                    <!-- Active Orders Content -->
                                    <div class="secondary-tab-pane <?php echo $secondaryTab === 'active' ? 'active' : ''; ?>" id="active-orders-tab">
                                        <!-- Order Metrics -->
                                        <div class="metrics-container">
                                            <div class="metric-card">
                                                <div class="metric-content">
                                                    <span class="metric-label">Active Orders</span>
                                                    <span class="metric-value"><?php echo $metrics['active']; ?></span>
                                                    <span class="metric-trend"><span class="trend-up">+20%</span> from last week</span>
                                                </div>
                                                <div class="metric-icon">
                                                    <i class="fas fa-truck fa-lg"></i>
                                                </div>
                                            </div>
                                            <div class="metric-card">
                                                <div class="metric-content">
                                                    <span class="metric-label">Total Orders</span>
                                                    <span class="metric-value"><?php echo $metrics['total']; ?></span>
                                                    <span class="metric-trend"><span class="trend-up">+12%</span> from last month</span>
                                                </div>
                                                <div class="metric-icon">
                                                    <i class="fas fa-boxes fa-lg"></i>
                                                </div>
                                            </div>
                                            <div class="metric-card">
                                                <div class="metric-content">
                                                    <span class="metric-label">Upcoming Events</span>
                                                    <span class="metric-value"><?php echo $metrics['upcoming']; ?></span>
                                                    <span class="metric-trend">Next: May 5, 2025</span>
                                                </div>
                                                <div class="metric-icon">
                                                    <i class="fas fa-calendar-check fa-lg"></i>
                                                </div>
                                            </div>
                                            <div class="metric-card">
                                                <div class="metric-content">
                                                    <span class="metric-label">Avg. Delivery Time</span>
                                                    <span class="metric-value"><?php echo $metrics['avgDelivery']; ?></span>
                                                    <span class="metric-trend"><span class="trend-down">-3 min</span> from last week</span>
                                                </div>
                                                <div class="metric-icon">
                                                    <i class="fas fa-clock fa-lg"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tracking and Orders Section -->
                                        <div class="tracking-and-orders">
                                            <!-- Live Order Tracking -->
                                            <div class="live-tracking">
                                                <div class="live-tracking-header">
                                                    <h3>Live Order Tracking</h3>
                                                    <div class="map-view-options">
                                                        <button class="map-view-option active">Live Map</button>
                                                        <button class="map-view-option">Order Details</button>
                                                    </div>
                                                </div>
                                                <div class="map-container" style="background-color: white;">
                                                    <div class="map-controls">
                                                        <button class="map-view-button active" data-view="standard">
                                                            <span>Standard</span>
                                                        </button>
                                                        <button class="map-view-button" data-view="satellite">
                                                            <span>Satellite</span>
                                                        </button>
                                                        <button class="map-view-button" data-view="traffic">
                                                            <span>Traffic</span>
                                                        </button>
                                                    </div>
                                                    <div id="orderMap" class="order-map" style="height: 100%; width: 100%; position: absolute; top: 0; left: 0; background-color: white;"></div>
                                                    <div class="map-loading-overlay" id="mapLoadingOverlay">
                                                        <div class="map-loading-spinner"></div>
                                                        <div class="map-loading-text">Loading map...</div>
                                                    </div>
                                                    <div id="orderDetails" class="order-details" style="display: none; height: 100%; width: 100%; position: absolute; top: 0; left: 0; background-color: white; overflow-y: auto;">
                                                        <div class="order-details-content">
                                                            <!-- Order details will be loaded here dynamically via JavaScript -->
                                                        </div>
                                                    </div>
                                                    <div class="map-controls-right">
                                                        <button class="map-control-button zoom-in">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                        <button class="map-control-button zoom-out">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                        <div class="map-control-divider"></div>
                                                        <button class="map-control-button center">
                                                            <i class="fas fa-location-arrow"></i>
                                                        </button>
                                                        <button class="map-control-button refresh">
                                                            <i class="fas fa-sync-alt"></i>
                                                        </button>
                                                    </div>
                                                    <div class="map-help">
                                                        <div class="map-help-item">
                                                            <i class="fas fa-arrows-alt"></i> Drag to pan
                                                        </div>
                                                        <div class="map-help-item">
                                                            <i class="fas fa-plus"></i> +/- to zoom
                                                        </div>
                                                    </div>
                                                    <button class="map-fullscreen-button">
                                                        <i class="fas fa-expand"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Active Orders List -->
                                            <div class="active-orders">
                                                <div class="active-orders-header">
                                                    <h3>Active Orders</h3>
                                                    <span class="orders-count">
                                                        <?php
                                                        if (isset($orders['active']) && is_array($orders['active'])) {
                                                            $orderCount = count($orders['active']);
                                                            echo $orderCount . ' Orders';
                                                        } else {
                                                            echo '0 Orders';
                                                        }
                                                        ?>
                                                    </span>
                                                </div>
                                                <div class="order-list">
                                                    <?php foreach ($orders['active'] as $order): ?>
                                                        <div class="order-item" data-order-id="<?php echo $order['id']; ?>">
                                                            <div class="order-header">
                                                                <div>
                                                                    <span class="order-id">ORD-<?php echo htmlspecialchars($order['id']); ?></span>
                                                                    <span class="order-date"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span>
                                                                </div>
                                                                <span class="order-status <?php echo $order['status_class']; ?>"><?php echo $order['display_status']; ?></span>
                                                            </div>
                                                            <div class="order-info">
                                                                <div class="order-eta">
                                                                    <i class="far fa-clock"></i>
                                                                    <?php if ($order['estimated_delivery_time']): ?>
                                                                        ETA: <?php echo date('g:i A', strtotime($order['estimated_delivery_time'])); ?>
                                                                    <?php else: ?>
                                                                        ETA: Processing
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="order-price">
                                                                    ₱<?php echo number_format($order['total_amount'], 2); ?>
                                                                </div>
                                                            </div>
                                                            <div class="order-progress">
                                                                <div class="order-progress-bar <?php echo $order['status_class']; ?>" style="width: 0;"></div>
                                                            </div>
                                                            <div class="order-actions">
                                                                <button class="order-action-button track-button" data-order-id="<?php echo $order['id']; ?>">
                                                                    <i class="fas fa-truck"></i>
                                                                    Track Order
                                                                </button>
                                                                <button class="order-action-button contact-button">
                                                                    <i class="fas fa-phone"></i>
                                                                    Contact
                                                                </button>
                                                            </div>
                                                            <div class="order-footer">
                                                                <div class="last-updated">
                                                                    <i class="fas fa-sync-alt"></i>
                                                                    Last updated: <?php echo getTimeAgo($order['created_at']); ?>
                                                                </div>
                                                                <a href="#" class="details-link" data-order-id="<?php echo $order['id']; ?>">Details</a>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                    <?php if (empty($orders['active'])): ?>
                                                        <div class="no-orders-message">
                                                            <p>You don't have any active orders at the moment.</p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Map Modal -->
                                    <div class="map-modal" id="mapModal">
                                        <div class="map-modal-content">
                                            <div class="map-modal-header">
                                                <h3>Live Order Tracking</h3>
                                                <button class="map-modal-close">&times;</button>
                                            </div>
                                            <div class="map-modal-body">
                                                <div id="modalOrderMap"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Past Orders Content -->
                                    <div class="secondary-tab-pane <?php echo $secondaryTab === 'past' ? 'active' : ''; ?>" id="past-orders-tab">
                                        <div class="past-orders-container">
                                            <div class="past-orders-header">
                                                <div class="past-orders-title">
                                                    <i class="fas fa-history"></i>
                                                    <h3>Past Orders</h3>
                                                </div>
                                                <div class="controls-row">
                                                    <div class="view-controls">
                                                        <button class="view-btn active" data-view="grid" title="Grid View">
                                                            <i class="fas fa-th-large"></i>
                                                        </button>
                                                        <button class="view-btn" data-view="list" title="List View">
                                                            <i class="fas fa-list"></i>
                                                        </button>
                                                        <button class="view-btn" data-view="calendar" title="Calendar View">
                                                            <i class="fas fa-calendar-week"></i>
                                                        </button>
                                                    </div>
                                                    <div class="sort-container">
                                                        <button class="sort-button" id="sortOrdersButton">
                                                            <i class="fas fa-sort"></i>
                                                            <span>Sort</span>
                                                        </button>
                                                        <div class="sort-menu" id="sortOrdersMenu">
                                                            <button class="sort-option" data-sort="date-newest">
                                                                <i class="fas fa-calendar-alt"></i>
                                                                <span>Date (Newest First)</span>
                                                            </button>
                                                            <button class="sort-option" data-sort="date-oldest">
                                                                <i class="fas fa-calendar-alt"></i>
                                                                <span>Date (Oldest First)</span>
                                                            </button>
                                                            <button class="sort-option" data-sort="price-high">
                                                                <i class="fas fa-arrow-up"></i>
                                                                <span>Price (High to Low)</span>
                                                            </button>
                                                            <button class="sort-option" data-sort="price-low">
                                                                <i class="fas fa-arrow-down"></i>
                                                                <span>Price (Low to High)</span>
                                                            </button>
                                                            <div class="sort-divider"></div>
                                                            <button class="sort-option clear-sort" data-sort="clear">
                                                                <i class="fas fa-times"></i>
                                                                <span>Clear Sort</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="search-bar-container">
                                                <div class="search-bar">
                                                    <i class="fas fa-search"></i>
                                                    <input type="text" placeholder="Search past orders..." />
                                                </div>
                                            </div>
                                            <div class="past-order-cards">
                                                <?php if (empty($orders['past'])): ?>
                                                    <div class="no-orders-message">
                                                        <p>You don't have any past orders at the moment.</p>
                                                    </div>
                                                <?php else: ?>
                                                    <?php foreach ($orders['past'] as $order): ?>
                                                        <?php
                                                        // Fetch order items for this order
                                                        $stmt = $pdo->prepare('
                                                            SELECT oi.*, p.name as product_name, p.image_url
                                                            FROM order_items oi
                                                            JOIN products p ON oi.product_id = p.id
                                                            WHERE oi.order_id = ?
                                                        ');
                                                        $stmt->execute([$order['id']]);
                                                        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                        // Check if there's a rating for this order
                                                        $stmt = $pdo->prepare('
                                                            SELECT rating, review
                                                            FROM order_ratings
                                                            WHERE order_id = ?
                                                        ');
                                                        $stmt->execute([$order['id']]);
                                                        $ratingData = $stmt->fetch(PDO::FETCH_ASSOC);
                                                        $rating = $ratingData ? $ratingData['rating'] : 0;
                                                        $review = $ratingData ? $ratingData['review'] : '';
                                                        ?>
                                                        <div class="past-order-card" data-order-id="<?php echo $order['id']; ?>">
                                                            <div class="order-card-header">
                                                                <div class="order-number-date">
                                                                    <h4 class="order-number">ORD-<?php echo htmlspecialchars($order['id']); ?></h4>
                                                                    <p class="order-date"><?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                                                                </div>
                                                                <div class="order-status">
                                                                    <span class="<?php echo $order['status_class']; ?>-badge"><?php echo $order['display_status']; ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="order-address">
                                                                <i class="fas fa-map-marker-alt"></i>
                                                                <span><?php echo htmlspecialchars($order['shipping_address']); ?></span>
                                                            </div>

                                                            <?php if ($orderItems): ?>
                                                            <div class="order-items-preview">
                                                                <h5>Order Items:</h5>
                                                                <div class="order-items-list">
                                                                    <?php foreach (array_slice($orderItems, 0, 2) as $item): ?>
                                                                        <div class="cart-item receipt-row">
                                                                            <?php if (!empty($item['image_url'])): ?>
                                                                            <img src="/ayskrimWebsite/assets/images/<?php echo htmlspecialchars($item['image_url']); ?>"
                                                                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                                                                 class="item-thumb">
                                                                            <?php endif; ?>
                                                                            <div class="item-details">
                                                                                <div class="item-name">
                                                                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                                                                </div>
                                                                                <div class="item-quantity">
                                                                                    x<?php echo $item['quantity']; ?>
                                                                                </div>
                                                                            </div>
                                                                            <div class="item-price">
                                                                                ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                                                            </div>
                                                                        </div>
                                                                    <?php endforeach; ?>

                                                                    <?php if (count($orderItems) > 2): ?>
                                                                        <div class="expandable-items-container">
                                                                            <div class="additional-items" style="display: none;">
                                                                                <?php foreach (array_slice($orderItems, 2) as $item): ?>
                                                                                    <div class="cart-item receipt-row">
                                                                                        <?php if (!empty($item['image_url'])): ?>
                                                                                        <img src="/ayskrimWebsite/assets/images/<?php echo htmlspecialchars($item['image_url']); ?>"
                                                                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                                                                             class="item-thumb">
                                                                                        <?php endif; ?>
                                                                                        <div class="item-details">
                                                                                            <div class="item-name">
                                                                                                <?php echo htmlspecialchars($item['product_name']); ?>
                                                                                            </div>
                                                                                            <div class="item-quantity">
                                                                                                x<?php echo $item['quantity']; ?>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="item-price">
                                                                                            ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                                                                        </div>
                                                                                    </div>
                                                                                <?php endforeach; ?>
                                                                            </div>
                                                                            <a href="#" class="more-items-link" data-order-id="<?php echo $order['id']; ?>" data-total-items="<?php echo count($orderItems); ?>">
                                                                                <div class="more-items">
                                                                                    +<?php echo count($orderItems) - 2; ?> more items
                                                                                </div>
                                                                            </a>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>

                                                            <!-- Order Summary in Card -->
                                                            <div class="order-summary-card">
                                                                <?php
                                                                $subtotal = 0;
                                                                foreach ($orderItems as $item) {
                                                                    $subtotal += $item['price'] * $item['quantity'];
                                                                }
                                                                $shippingFee = $order['total_amount'] - $subtotal;
                                                                ?>
                                                                <div class="summary-row">
                                                                    <span>Subtotal:</span>
                                                                    <span>₱<?php echo number_format($subtotal, 2); ?></span>
                                                                </div>
                                                                <?php if ($shippingFee > 0): ?>
                                                                <div class="summary-row">
                                                                    <span>Shipping Fee:</span>
                                                                    <span>₱<?php echo number_format($shippingFee, 2); ?></span>
                                                                </div>
                                                                <?php endif; ?>
                                                                <div class="summary-row total">
                                                                    <span>Total:</span>
                                                                    <span>₱<?php echo number_format($order['total_amount'], 2); ?></span>
                                                                </div>
                                                            </div>
                                                            <?php endif; ?>

                                                            <!-- Star Rating System -->
                                                            <div class="order-rating-container">
                                                                <div class="star-rating" data-order-id="<?php echo $order['id']; ?>">
                                                                    <div class="star-rating-label">Rate this order:</div>
                                                                    <div class="star-rating-stars">
                                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                            <i class="fas fa-star <?php echo $i <= $rating ? 'active' : ''; ?>" data-rating="<?php echo $i; ?>"></i>
                                                                        <?php endfor; ?>
                                                                    </div>
                                                                    <div class="star-rating-value"><?php echo $rating > 0 ? $rating . '.0' : ''; ?></div>
                                                                </div>

                                                                <div class="review-container <?php echo $review ? 'has-review' : ''; ?>">
                                                                    <?php if ($review): ?>
                                                                    <div class="review-text">
                                                                        "<?php echo htmlspecialchars($review); ?>"
                                                                    </div>
                                                                    <button class="edit-review-btn" data-order-id="<?php echo $order['id']; ?>">
                                                                        <i class="fas fa-edit"></i> Edit Review
                                                                    </button>
                                                                    <?php else: ?>
                                                                    <div class="review-input-container">
                                                                        <textarea class="review-input" placeholder="Add your review here..." rows="2"></textarea>
                                                                        <button class="submit-review-btn" data-order-id="<?php echo $order['id']; ?>">
                                                                            <i class="fas fa-paper-plane"></i> Submit
                                                                        </button>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>

                                                            <!-- Order Details Expandable Section -->
                                                            <div class="order-details-expanded" id="order-details-<?php echo $order['id']; ?>">
                                                                <a href="#" class="hide-details-link" data-order-id="<?php echo $order['id']; ?>">
                                                                    <span>Hide Details</span>
                                                                    <i class="fas fa-chevron-up"></i>
                                                                </a>
                                                            </div>

                                                            <div class="order-card-actions">
                                                                <button class="action-button reorder-button centered" data-order-id="<?php echo $order['id']; ?>">
                                                                    <i class="fas fa-shopping-cart"></i>
                                                                    <span>Order Again</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bookings Tab -->
                        <div class="tab-pane <?php echo $mainTab === 'bookings' ? 'active' : ''; ?>" id="bookings-tab">
                            <div class="tab-content-inner">
                                <div class="container mx-auto">
                                    <!-- Booking Status Tabs -->
                                    <div class="secondary-tabs-container">
                                        <div class="secondary-tabs">
                                            <div class="secondary-tabs-slider" id="bookings-tabs-slider"></div>
                                            <button class="secondary-tab-btn <?php echo $secondaryTab === 'upcoming' ? 'active' : ''; ?>" data-tab="upcoming-bookings">
                                                <i class="fas fa-calendar-days"></i>
                                                <span>Upcoming Bookings</span>
                                                <span class="badge-count"><?php echo count($bookings['upcoming']); ?></span>
                                            </button>
                                            <button class="secondary-tab-btn <?php echo $secondaryTab === 'past' ? 'active' : ''; ?>" data-tab="past-bookings">
                                                <i class="fas fa-history"></i>
                                                <span>Past Bookings</span>
                                                <span class="badge-count"><?php echo count($bookings['past']); ?></span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Bookings Content -->
                                    <div class="secondary-tab-content">
                                        <!-- Upcoming Bookings Content -->
                                        <div class="secondary-tab-pane <?php echo $secondaryTab === 'upcoming' ? 'active' : ''; ?>" id="upcoming-bookings-tab">
                                            <div class="upcoming-bookings-container">
                                                <div class="upcoming-bookings-header">
                                                    <div class="upcoming-bookings-title">
                                                        <i class="fas fa-calendar-alt"></i>
                                                        <h3>Upcoming Bookings</h3>
                                                    </div>
                                                    <div class="bookings-actions">
                                                        <div class="controls-row">
                                                            <div class="view-controls">
                                                                <button class="view-btn active" data-view="grid" title="Grid View">
                                                                    <i class="fas fa-th-large"></i>
                                                                </button>
                                                                <button class="view-btn" data-view="list" title="List View">
                                                                    <i class="fas fa-list"></i>
                                                                </button>
                                                                <button class="view-btn" data-view="calendar" title="Calendar View">
                                                                    <i class="fas fa-calendar-week"></i>
                                                                </button>
                                                            </div>
                                                            <div class="sort-container">
                                                                <button class="sort-button" id="sortUpcomingBookingsButton">
                                                                    <i class="fas fa-sort"></i>
                                                                    <span>Sort</span>
                                                                </button>
                                                                <div class="sort-menu" id="sortUpcomingBookingsMenu">
                                                                    <button class="sort-option" data-sort="date-newest">
                                                                        <i class="fas fa-calendar-alt"></i>
                                                                        <span>Date (Newest First)</span>
                                                                    </button>
                                                                    <button class="sort-option" data-sort="date-oldest">
                                                                        <i class="fas fa-calendar-alt"></i>
                                                                        <span>Date (Oldest First)</span>
                                                                    </button>
                                                                    <button class="sort-option" data-sort="price-high">
                                                                        <i class="fas fa-arrow-up"></i>
                                                                        <span>Price (High to Low)</span>
                                                                    </button>
                                                                    <button class="sort-option" data-sort="price-low">
                                                                        <i class="fas fa-arrow-down"></i>
                                                                        <span>Price (Low to High)</span>
                                                                    </button>
                                                                    <div class="sort-divider"></div>
                                                                    <button class="sort-option clear-sort" data-sort="clear">
                                                                        <i class="fas fa-times"></i>
                                                                        <span>Clear Sort</span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <button class="book-event-btn">
                                                            <i class="fas fa-calendar-plus"></i>
                                                            <span>Book New Event</span>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="search-bar-container">
                                                    <div class="search-bar">
                                                        <i class="fas fa-search"></i>
                                                        <input type="text" placeholder="Search upcoming bookings..." />
                                                    </div>
                                                </div>
                                                <div class="booking-cards">
                                                    <?php if (empty($bookings['upcoming'])): ?>
                                                        <div class="no-orders-message">
                                                            <p>You don't have any upcoming bookings at the moment.</p>
                                                        </div>
                                                    <?php else: ?>
                                                        <?php foreach ($bookings['upcoming'] as $booking): ?>
                                                            <div class="booking-card">
                                                                <div class="booking-image">
                                                                    <div class="image-container">
                                                                        <img src="/ayskrimWebsite/assets/images/events/<?php echo $booking['package_type'] === 'Basic' ? 'basic_package.jpg' : 'premium_package.jpg'; ?>" alt="<?php echo $booking['package_type']; ?> Package" class="booking-img">
                                                                    </div>
                                                                    <div class="booking-title-overlay">
                                                                        <h4><?php echo $booking['package_type']; ?> Ice Cream Package</h4>
                                                                        <p><?php echo $booking['formatted_date']; ?></p>
                                                                        <span class="status-badge <?php echo strtolower($booking['status']); ?>"><?php echo $booking['status']; ?></span>
                                                                    </div>
                                                                </div>
                                                                <div class="booking-info">
                                                                    <div class="booking-detail">
                                                                        <i class="fas fa-clock"></i>
                                                                        <span><?php echo $booking['formatted_time']; ?></span>
                                                                    </div>
                                                                    <div class="booking-detail">
                                                                        <i class="fas fa-map-marker-alt"></i>
                                                                        <span><?php echo htmlspecialchars($booking['venue_address']); ?></span>
                                                                    </div>
                                                                    <div class="booking-detail">
                                                                        <i class="fas fa-users"></i>
                                                                        <span><?php echo $booking['guest_count']; ?> Guests</span>
                                                                    </div>
                                                                    <div class="booking-detail">
                                                                        <i class="fas fa-credit-card"></i>
                                                                        <span>Payment: <?php echo $booking['payment_method'] ? htmlspecialchars($booking['payment_method']) : 'Not specified'; ?></span>
                                                                    </div>
                                                                    <div class="booking-services">
                                                                        <?php
                                                                        // Display selected flavors if available
                                                                        if (isset($booking['special_requests_data']['selected_flavors'])) {
                                                                            foreach ($booking['special_requests_data']['selected_flavors'] as $flavor) {
                                                                                echo '<span class="service-tag">' . htmlspecialchars($flavor) . '</span>';
                                                                            }
                                                                        }
                                                                        ?>
                                                                    </div>
                                                                    <div class="view-details-link">
                                                                        <a href="#" class="toggle-details" data-booking="<?php echo $booking['id']; ?>">View details</a>
                                                                        <i class="fas fa-chevron-right"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="booking-details-expanded" id="booking-details-<?php echo $booking['id']; ?>">
                                                                    <div class="detail-section">
                                                                        <h5>Package Details</h5>
                                                                        <div class="booking-detail-row">
                                                                            <span class="detail-label">Package Type:</span>
                                                                            <span class="detail-value"><?php echo $booking['package_type']; ?></span>
                                                                        </div>
                                                                        <?php if (isset($booking['package_description'])): ?>
                                                                        <div class="booking-detail-row">
                                                                            <span class="detail-label">Description:</span>
                                                                            <span class="detail-value"><?php echo htmlspecialchars($booking['package_description']); ?></span>
                                                                        </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <div class="detail-section">
                                                                        <h5>Booking Details</h5>
                                                                        <div class="booking-detail-row">
                                                                            <span class="detail-label">Total Amount:</span>
                                                                            <span class="detail-value price">₱<?php echo number_format($booking['total_amount'], 2); ?></span>
                                                                        </div>
                                                                        <div class="booking-detail-row">
                                                                            <span class="detail-label">Booking Status:</span>
                                                                            <span class="detail-value status <?php echo strtolower($booking['status']); ?>"><?php echo $booking['status']; ?></span>
                                                                        </div>
                                                                        <div class="booking-detail-row">
                                                                            <span class="detail-label">Payment Method:</span>
                                                                            <span class="detail-value"><?php echo $booking['payment_method'] ? htmlspecialchars($booking['payment_method']) : 'Not specified'; ?></span>
                                                                        </div>
                                                                        <div class="booking-detail-row">
                                                                            <span class="detail-label">Payment Status:</span>
                                                                            <span class="detail-value status <?php echo strtolower($booking['payment_status'] ?? 'pending'); ?>"><?php echo $booking['payment_status'] ?? 'Pending'; ?></span>
                                                                        </div>
                                                                        <div class="booking-detail-row">
                                                                            <span class="detail-label">Booked On:</span>
                                                                            <span class="detail-value"><?php echo date('F j, Y', strtotime($booking['created_at'])); ?></span>
                                                                        </div>
                                                                    </div>
                                                                    <?php if (!empty($booking['special_requests'])): ?>
                                                                        <div class="detail-section">
                                                                            <h5>Special Requests</h5>
                                                                            <div class="special-requests">
                                                                                <p><?php echo htmlspecialchars($booking['special_requests']); ?></p>
                                                                            </div>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <a href="#" class="hide-details-link" data-booking="<?php echo $booking['id']; ?>">
                                                                        <span>Hide details</span>
                                                                        <i class="fas fa-chevron-up"></i>
                                                                    </a>
                                                                </div>
                                                                <div class="booking-actions">
                                                                    <button class="action-button reschedule-btn">
                                                                        <i class="fas fa-calendar"></i>
                                                                        <span>Reschedule</span>
                                                                    </button>
                                                                    <button class="action-button cancel-btn">
                                                                        <i class="fas fa-times"></i>
                                                                        <span>Cancel</span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Past Bookings Content -->
                                        <div class="secondary-tab-pane <?php echo $secondaryTab === 'past' ? 'active' : ''; ?>" id="past-bookings-tab">
                                            <div class="past-bookings-container">
                                                <div class="past-bookings-header">
                                                    <div class="past-bookings-title">
                                                        <i class="fas fa-history"></i>
                                                        <h3>Past Bookings</h3>
                                                    </div>
                                                    <div class="bookings-actions">
                                                        <div class="controls-row">
                                                            <div class="view-controls">
                                                                <button class="view-btn active" data-view="grid" title="Grid View">
                                                                    <i class="fas fa-th-large"></i>
                                                                </button>
                                                                <button class="view-btn" data-view="list" title="List View">
                                                                    <i class="fas fa-list"></i>
                                                                </button>
                                                                <button class="view-btn" data-view="calendar" title="Calendar View">
                                                                    <i class="fas fa-calendar-week"></i>
                                                                </button>
                                                            </div>
                                                            <div class="sort-container">
                                                                <button class="sort-button" id="sortPastBookingsButton">
                                                                    <i class="fas fa-sort"></i>
                                                                    <span>Sort</span>
                                                                </button>
                                                                <div class="sort-menu" id="sortPastBookingsMenu">
                                                                    <button class="sort-option" data-sort="date-newest">
                                                                        <i class="fas fa-calendar-alt"></i>
                                                                        <span>Date (Newest First)</span>
                                                                    </button>
                                                                    <button class="sort-option" data-sort="date-oldest">
                                                                        <i class="fas fa-calendar-alt"></i>
                                                                        <span>Date (Oldest First)</span>
                                                                    </button>
                                                                    <button class="sort-option" data-sort="price-high">
                                                                        <i class="fas fa-arrow-up"></i>
                                                                        <span>Price (High to Low)</span>
                                                                    </button>
                                                                    <button class="sort-option" data-sort="price-low">
                                                                        <i class="fas fa-arrow-down"></i>
                                                                        <span>Price (Low to High)</span>
                                                                    </button>
                                                                    <div class="sort-divider"></div>
                                                                    <button class="sort-option clear-sort" data-sort="clear">
                                                                        <i class="fas fa-times"></i>
                                                                        <span>Clear Sort</span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <button class="book-event-btn">
                                                            <i class="fas fa-calendar-plus"></i>
                                                            <span>Book New Event</span>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="search-bar-container">
                                                    <div class="search-bar">
                                                        <i class="fas fa-search"></i>
                                                        <input type="text" placeholder="Search past bookings..." />
                                                    </div>
                                                </div>
                                                <div class="booking-cards">
                                                    <?php if (empty($bookings['past'])): ?>
                                                        <div class="no-orders-message">
                                                            <p>You don't have any past bookings at the moment.</p>
                                                        </div>
                                                    <?php else: ?>
                                                        <?php foreach ($bookings['past'] as $booking): ?>
                                                            <div class="booking-card">
                                                                <div class="booking-image">
                                                                    <div class="image-container">
                                                                        <img src="/ayskrimWebsite/assets/images/events/<?php echo $booking['package_type'] === 'Basic' ? 'basic_package.jpg' : 'premium_package.jpg'; ?>" alt="<?php echo $booking['package_type']; ?> Package" class="booking-img">
                                                                    </div>
                                                                    <div class="booking-title-overlay">
                                                                        <h4><?php echo $booking['package_type']; ?> Ice Cream Package</h4>
                                                                        <p><?php echo $booking['formatted_date']; ?></p>
                                                                        <span class="status-badge completed">Completed</span>
                                                                    </div>
                                                                </div>
                                                                <div class="booking-info">
                                                                    <div class="booking-detail">
                                                                        <i class="fas fa-clock"></i>
                                                                        <span><?php echo $booking['formatted_time']; ?></span>
                                                                    </div>
                                                                    <div class="booking-detail">
                                                                        <i class="fas fa-map-marker-alt"></i>
                                                                        <span><?php echo htmlspecialchars($booking['venue_address']); ?></span>
                                                                    </div>
                                                                    <div class="booking-detail">
                                                                        <i class="fas fa-users"></i>
                                                                        <span><?php echo $booking['guest_count']; ?> Guests</span>
                                                                    </div>
                                                                    <div class="booking-detail">
                                                                        <i class="fas fa-credit-card"></i>
                                                                        <span>Payment: <?php echo $booking['payment_method'] ? htmlspecialchars($booking['payment_method']) : 'Not specified'; ?></span>
                                                                    </div>
                                                                    <div class="booking-services">
                                                                        <?php
                                                                        // Display selected flavors if available
                                                                        if (isset($booking['special_requests_data']['selected_flavors'])) {
                                                                            foreach ($booking['special_requests_data']['selected_flavors'] as $flavor) {
                                                                                echo '<span class="service-tag">' . htmlspecialchars($flavor) . '</span>';
                                                                            }
                                                                        }
                                                                        ?>
                                                                    </div>
                                                                    <div class="view-details-link">
                                                                        <a href="#" class="toggle-details" data-booking="past-<?php echo $booking['id']; ?>">View details</a>
                                                                        <i class="fas fa-chevron-right"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="booking-details-expanded" id="booking-details-past-<?php echo $booking['id']; ?>">
                                                                    <div class="detail-section">
                                                                        <h5>Package Details</h5>
                                                                        <div class="booking-detail-row">
                                                                            <span class="detail-label">Package Type:</span>
                                                                            <span class="detail-value"><?php echo $booking['package_type']; ?></span>
                                                                        </div>
                                                                        <?php if (isset($booking['package_description'])): ?>
                                                                        <div class="booking-detail-row">
                                                                            <span class="detail-label">Description:</span>
                                                                            <span class="detail-value"><?php echo htmlspecialchars($booking['package_description']); ?></span>
                                                                        </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <div class="detail-section">
                                                                        <h5>Booking Details</h5>
                                                                        <div class="booking-detail-row">
                                                                            <span class="detail-label">Total Amount:</span>
                                                                            <span class="detail-value price">₱<?php echo number_format($booking['total_amount'], 2); ?></span>
                                                                        </div>
                                                                        <div class="booking-detail-row">
                                                                            <span class="detail-label">Booking Status:</span>
                                                                            <span class="detail-value status completed">Completed</span>
                                                                        </div>
                                                                        <div class="booking-detail-row">
                                                                            <span class="detail-label">Payment Method:</span>
                                                                            <span class="detail-value"><?php echo $booking['payment_method'] ? htmlspecialchars($booking['payment_method']) : 'Not specified'; ?></span>
                                                                        </div>
                                                                        <div class="booking-detail-row">
                                                                            <span class="detail-label">Payment Status:</span>
                                                                            <span class="detail-value status <?php echo strtolower($booking['payment_status'] ?? 'pending'); ?>"><?php echo $booking['payment_status'] ?? 'Pending'; ?></span>
                                                                        </div>
                                                                        <div class="booking-detail-row">
                                                                            <span class="detail-label">Booked On:</span>
                                                                            <span class="detail-value"><?php echo date('F j, Y', strtotime($booking['created_at'])); ?></span>
                                                                        </div>
                                                                    </div>
                                                                    <?php if (!empty($booking['special_requests'])): ?>
                                                                        <div class="detail-section">
                                                                            <h5>Special Requests</h5>
                                                                            <div class="special-requests">
                                                                                <p><?php echo htmlspecialchars($booking['special_requests']); ?></p>
                                                                            </div>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <a href="#" class="hide-details-link" data-booking="past-<?php echo $booking['id']; ?>">
                                                                        <span>Hide details</span>
                                                                        <i class="fas fa-chevron-up"></i>
                                                                    </a>
                                                                </div>
                                                                <div class="booking-actions">
                                                                    <button class="action-button book-again-btn">
                                                                        <i class="fas fa-calendar-plus"></i>
                                                                        <span>Book Again</span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Section -->
                    <div class="container mx-auto">
                        <div class="quick-actions">
                            <h3>Quick Actions</h3>
                            <div class="actions-grid">
                                <div class="action-card">
                                    <div class="action-icon">
                                        <i class="fas fa-shopping-bag"></i>
                                    </div>
                                    <div class="action-text">
                                        <h4 class="action-title">New Order</h4>
                                        <p class="action-description">Order your favorites</p>
                                    </div>
                                    <button class="action-button">Order Now</button>
                                </div>
                                <div class="action-card">
                                    <div class="action-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="action-text">
                                        <h4 class="action-title">Book Event</h4>
                                        <p class="action-description">Plan your celebration</p>
                                    </div>
                                    <button class="action-button">Book Now</button>
                                </div>
                                <div class="action-card">
                                    <div class="action-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="action-text">
                                        <h4 class="action-title">Find Stores</h4>
                                        <p class="action-description">Locate nearby shops</p>
                                    </div>
                                    <button class="action-button">View Map</button>
                                </div>
                                <div class="action-card">
                                    <div class="action-icon">
                                        <i class="fas fa-heart"></i>
                                    </div>
                                    <div class="action-text">
                                        <h4 class="action-title">Favorites</h4>
                                        <p class="action-description">Your saved items</p>
                                    </div>
                                    <button class="action-button">View All</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recommended Items Section -->
                    <div class="container mx-auto">
                        <div class="recommended-items">
                            <div class="items-heading">
                                <h3>Recommended For You</h3>
                                <div class="item-navigation">
                                    <button class="item-nav-button prev-button">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <button class="item-nav-button next-button">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="items-grid">
                                <?php foreach ($recommendedItems as $item): ?>
                                    <div class="item-card">
                                        <button class="item-favorite">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                        <div class="item-image">
                                            <img src="<?php echo $item['image'] ?? ''; ?>" alt="<?php echo $item['name']; ?>" class="item-img">
                                        </div>
                                        <span class="item-title"><?php echo $item['name']; ?></span>
                                        <div class="item-price-row">
                                            <span class="item-price">₱<?php echo number_format($item['price'], 2); ?></span>
                                            <button class="item-add">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <?php include __DIR__ . '/../../shared/footer/footer.php'; ?>
    </div>

    <!-- Reschedule Booking Modal -->
    <div id="rescheduleModal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <div class="modal-title">
                    <i class="fas fa-calendar-alt"></i>
                    <h3 class="modal-title-text">Reschedule Booking</h3>
                </div>
                <button id="close-reschedule-modal" class="close-modal-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-content">
                <form id="rescheduleForm">
                    <input type="hidden" id="reschedule_event_id" name="event_id">

                    <div class="form-group">
                        <label for="reschedule_event_date">Event Date</label>
                        <input type="date" id="reschedule_event_date" name="event_date" class="form-control" required>
                        <div class="form-error"></div>
                    </div>

                    <div class="form-row">
                        <div class="form-group half">
                            <label for="reschedule_start_time">Start Time</label>
                            <input type="time" id="reschedule_start_time" name="start_time" class="form-control" required>
                            <div class="form-error"></div>
                        </div>
                        <div class="form-group half">
                            <label for="reschedule_end_time">End Time</label>
                            <input type="time" id="reschedule_end_time" name="end_time" class="form-control" required>
                            <div class="form-error"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <p class="note">
                            <i class="fas fa-info-circle"></i>
                            Note: Rescheduling is subject to availability. We will contact you to confirm the new date and time.
                        </p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button id="cancel-reschedule-btn" class="btn btn-outline">Cancel</button>
                <button id="confirm-reschedule-btn" class="btn btn-pink">Confirm Reschedule</button>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <div class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3 class="modal-title-text">Confirm Action</h3>
                </div>
                <button id="close-confirmation-modal" class="close-modal-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-content">
                <p id="confirmation-message">Are you sure you want to proceed with this action?</p>
            </div>
            <div class="modal-footer">
                <button id="cancel-confirmation-btn" class="btn btn-outline">Cancel</button>
                <button id="confirm-action-btn" class="btn btn-pink">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.5/gsap.min.js"></script>
    <script src="https://cdn.lordicon.com/lordicon.js"></script>
    <script src="/ayskrimWebsite/shared/navbar/navbar.js"></script>
    <script src="/ayskrimWebsite/shared/navbar/navbar-lottie.js"></script>
    <script src="orders.js"></script>
    <script src="calendar.js"></script>
    <script src="/ayskrimWebsite/shared/footer/footer.js"></script>

    <style>
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 1rem;
        }

        .modal-container {
            background-color: white;
            border-radius: 0.75rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f3f4f6;
        }

        .modal-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .modal-title i {
            color: #ec4899;
            font-size: 1.25rem;
        }

        .modal-title-text {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
            margin: 0;
        }

        .close-modal-btn {
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            font-size: 1.25rem;
            padding: 0.25rem;
            transition: color 0.2s;
        }

        .close-modal-btn:hover {
            color: #111827;
        }

        .modal-content {
            padding: 1.5rem;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            border-top: 1px solid #f3f4f6;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .form-group.half {
            flex: 1;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: #ec4899;
            outline: none;
        }

        .form-error {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            min-height: 1.25rem;
        }

        .note {
            background-color: #fdf2f8;
            border-left: 4px solid #ec4899;
            padding: 0.75rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            color: #831843;
        }

        .note i {
            margin-right: 0.5rem;
        }

        /* Button Styles */
        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 0.375rem;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .btn-outline {
            background-color: white;
            border: 1px solid #d1d5db;
            color: #4b5563;
        }

        .btn-outline:hover {
            background-color: #f9fafb;
        }

        .btn-pink {
            background-color: #ec4899;
            color: white;
        }

        .btn-pink:hover {
            background-color: #db2777;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</body>
</html>