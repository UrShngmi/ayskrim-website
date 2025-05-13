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

// Fetch active orders
$pdo = DB::getConnection();
$userId = getCurrentUserId();
$stmt = $pdo->prepare('SELECT id, tracking_code, shipping_address, created_at, total_amount, order_status, payment_status, estimated_delivery_time FROM orders WHERE user_id = ? AND order_status NOT IN ("Delivered", "Cancelled") ORDER BY id ASC');
$stmt->execute([$userId]);
$orders['active'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch past orders
$stmt = $pdo->prepare('SELECT id, tracking_code, shipping_address, created_at, total_amount, order_status, payment_status, estimated_delivery_time FROM orders WHERE user_id = ? AND order_status IN ("Delivered", "Cancelled") ORDER BY id ASC');
$stmt->execute([$userId]);
$orders['past'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Update metrics
$metrics['active'] = count($orders['active']);
$metrics['total'] = count($orders['active']) + count($orders['past']);
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
                                                                <span class="order-status <?php echo str_replace(' ', '-', strtolower($order['order_status'])); ?>"><?php echo $order['order_status']; ?></span>
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
                                                                <div class="order-progress-bar <?php echo str_replace(' ', '-', strtolower($order['order_status'])); ?>" style="width: 0;"></div>
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
                                                <?php foreach ($orders['past'] as $order): ?>
                                                    <div class="past-order-card">
                                                        <div class="order-card-header">
                                                            <div class="order-number-date">
                                                                <h4 class="order-number"><?php echo $order['id']; ?></h4>
                                                                <p class="order-date"><?php echo $order['date']; ?></p>
                                                            </div>
                                                            <div class="order-status">
                                                                <span class="delivered-badge">Delivered</span>
                                                            </div>
                                                        </div>
                                                        <div class="order-price">
                                                            ₱<?php echo number_format($order['total'], 2); ?>
                                                        </div>
                                                        <div class="order-address">
                                                            <i class="fas fa-map-marker-alt"></i>
                                                            <span><?php echo $order['deliveryAddress']; ?></span>
                                                        </div>
                                                        <div class="order-rating">
                                                            <div class="rating-stars">
                                                                <?php for ($i = 0; $i < 5; $i++): ?>
                                                                    <i class="fas fa-star <?php echo $i < $order['rating'] ? '' : 'star-inactive'; ?>"></i>
                                                                <?php endfor; ?>
                                                                <span><?php echo $order['rating']; ?>.0</span>
                                                            </div>
                                                            <span class="rating-label">Your Rating</span>
                                                        </div>
                                                        <div class="order-comment">
                                                            "<?php echo $order['review']; ?>"
                                                        </div>
                                                        <div class="order-card-actions">
                                                            <button class="action-button details-button">
                                                                <i class="fas fa-clipboard-list"></i>
                                                                <span>Details</span>
                                                            </button>
                                                            <button class="action-button reorder-button">
                                                                <i class="fas fa-shopping-cart"></i>
                                                                <span>Order Again</span>
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
                                                    <?php foreach ($bookings['upcoming'] as $booking): ?>
                                                        <div class="booking-card">
                                                            <div class="booking-image">
                                                                <div class="image-container">
                                                                    <img src="<?php echo $booking['image'] ?? ''; ?>" alt="<?php echo $booking['event']; ?>" class="booking-img">
                                                                </div>
                                                                <div class="booking-title-overlay">
                                                                    <h4><?php echo $booking['event']; ?></h4>
                                                                    <p><?php echo $booking['date']; ?></p>
                                                                    <span class="status-badge <?php echo strtolower($booking['status']); ?>"><?php echo $booking['status']; ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="booking-info">
                                                                <div class="booking-detail">
                                                                    <i class="fas fa-clock"></i>
                                                                    <span><?php echo $booking['time']; ?></span>
                                                                </div>
                                                                <div class="booking-detail">
                                                                    <i class="fas fa-map-marker-alt"></i>
                                                                    <span><?php echo $booking['location']; ?></span>
                                                                </div>
                                                                <div class="booking-detail">
                                                                    <i class="fas fa-users"></i>
                                                                    <span><?php echo $booking['guests']; ?></span>
                                                                </div>
                                                                <div class="booking-services">
                                                                    <?php foreach ($booking['services'] as $service): ?>
                                                                        <span class="service-tag"><?php echo $service; ?></span>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                                <div class="view-details-link">
                                                                    <a href="#" class="toggle-details" data-booking="<?php echo $booking['id']; ?>">View details</a>
                                                                    <i class="fas fa-chevron-right"></i>
                                                                </div>
                                                            </div>
                                                            <div class="booking-details-expanded" id="booking-details-<?php echo $booking['id']; ?>">
                                                                <div class="detail-section">
                                                                    <h5>Contact Person</h5>
                                                                    <div class="contact-detail">
                                                                        <i class="fas fa-user"></i>
                                                                        <span><?php echo $booking['contact_name'] ?? 'N/A'; ?></span>
                                                                    </div>
                                                                    <div class="contact-detail">
                                                                        <i class="fas fa-phone"></i>
                                                                        <span><?php echo $booking['contact_phone'] ?? 'N/A'; ?></span>
                                                                    </div>
                                                                </div>
                                                                <div class="detail-section">
                                                                    <h5>Booking Details</h5>
                                                                    <div class="booking-detail-row">
                                                                        <span class="detail-label">Total Amount:</span>
                                                                        <span class="detail-value price">₱<?php echo number_format($booking['price'], 2); ?></span>
                                                                    </div>
                                                                    <div class="booking-detail-row">
                                                                        <span class="detail-label">Payment Status:</span>
                                                                        <span class="detail-value status <?php echo strtolower($booking['payment_status'] ?? 'pending'); ?>"><?php echo $booking['payment_status'] ?? 'Pending'; ?></span>
                                                                    </div>
                                                                </div>
                                                                <?php if (!empty($booking['special_requests'])): ?>
                                                                    <div class="detail-section">
                                                                        <h5>Special Requests</h5>
                                                                        <div class="special-requests">
                                                                            <p><?php echo $booking['special_requests']; ?></p>
                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <a href="#" class="hide-details-link" data-booking="<?php echo $booking['id']; ?>">
                                                                    <span>Hide details</span>
                                                                    <i class="fas fa-chevron-up"></i>
                                                                </a>
                                                            </div>
                                                            <div class="booking-actions">
                                                                <button class="action-btn details-btn">
                                                                    <i class="fas fa-eye"></i>
                                                                    <span>View Details</span>
                                                                </button>
                                                                <button class="action-btn reschedule-btn">
                                                                    <i class="fas fa-calendar"></i>
                                                                    <span>Reschedule</span>
                                                                </button>
                                                                <button class="action-btn cancel-btn">
                                                                    <i class="fas fa-times"></i>
                                                                    <span>Cancel</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
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
                                                    <?php foreach ($bookings['past'] as $index => $booking): ?>
                                                        <div class="booking-card">
                                                            <div class="booking-image">
                                                                <div class="image-container">
                                                                    <img src="<?php echo $booking['image'] ?? ''; ?>" alt="<?php echo $booking['event']; ?>" class="booking-img">
                                                                </div>
                                                                <div class="booking-title-overlay">
                                                                    <h4><?php echo $booking['event']; ?></h4>
                                                                    <p><?php echo $booking['date']; ?></p>
                                                                    <span class="status-badge completed">Completed</span>
                                                                </div>
                                                            </div>
                                                            <div class="booking-info">
                                                                <div class="booking-detail">
                                                                    <i class="fas fa-clock"></i>
                                                                    <span><?php echo $booking['time']; ?></span>
                                                                </div>
                                                                <div class="booking-detail">
                                                                    <i class="fas fa-map-marker-alt"></i>
                                                                    <span><?php echo $booking['location']; ?></span>
                                                                </div>
                                                                <div class="booking-detail">
                                                                    <i class="fas fa-users"></i>
                                                                    <span><?php echo $booking['guests']; ?></span>
                                                                </div>
                                                                <div class="rating-stars-container">
                                                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                                                        <i class="fas fa-star <?php echo $i < $booking['rating'] ? 'active' : ''; ?>"></i>
                                                                    <?php endfor; ?>
                                                                    <span class="rating-text">Your Rating</span>
                                                                </div>
                                                                <div class="booking-review">
                                                                    <p>"<?php echo $booking['review']; ?>"</p>
                                                                </div>
                                                                <div class="view-details-link">
                                                                    <a href="#" class="toggle-details" data-booking="past-<?php echo $index; ?>">View details</a>
                                                                    <i class="fas fa-chevron-right"></i>
                                                                </div>
                                                            </div>
                                                            <div class="booking-details-expanded" id="booking-details-past-<?php echo $index; ?>">
                                                                <div class="detail-section">
                                                                    <h5>Contact Person</h5>
                                                                    <div class="contact-detail">
                                                                        <i class="fas fa-user"></i>
                                                                        <span>Emily Taylor</span>
                                                                    </div>
                                                                    <div class="contact-detail">
                                                                        <i class="fas fa-phone"></i>
                                                                        <span>+1 (555) 789-1234</span>
                                                                    </div>
                                                                </div>
                                                                <div class="detail-section">
                                                                    <h5>Booking Details</h5>
                                                                    <div class="booking-detail-row">
                                                                        <span class="detail-label">Total Amount:</span>
                                                                        <span class="detail-value price">₱<?php echo number_format($booking['price'], 2); ?></span>
                                                                    </div>
                                                                    <div class="booking-detail-row">
                                                                        <span class="detail-label">Payment Status:</span>
                                                                        <span class="detail-value status paid">Paid</span>
                                                                    </div>
                                                                </div>
                                                                <div class="detail-section">
                                                                    <h5>Services Provided</h5>
                                                                    <div class="booking-services">
                                                                        <span class="service-tag">Ice Cream Bar</span>
                                                                        <span class="service-tag">Wedding Cake</span>
                                                                        <span class="service-tag">Custom Flavors</span>
                                                                    </div>
                                                                </div>
                                                                <a href="#" class="hide-details-link" data-booking="past-<?php echo $index; ?>">
                                                                    <span>Hide details</span>
                                                                    <i class="fas fa-chevron-up"></i>
                                                                </a>
                                                            </div>
                                                            <div class="booking-actions">
                                                                <button class="action-btn details-btn">
                                                                    <i class="fas fa-eye"></i>
                                                                    <span>View Details</span>
                                                                </button>
                                                                <button class="action-btn book-again-btn">
                                                                    <i class="fas fa-calendar-plus"></i>
                                                                    <span>Book Again</span>
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
    </style>
</body>
</html>