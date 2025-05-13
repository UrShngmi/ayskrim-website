<?php
// Enhanced data structure to match React application model
$orders = [
    'active' => [
        [
            'id' => 'ORD-1234',
            'date' => 'April 20, 2025',
            'status' => 'Preparing',
            'progress' => 40,
            'eta' => '15-20 minutes',
            'price' => 59.99,
            'items' => [
                ['name' => 'Strawberry Delight', 'quantity' => 2],
                ['name' => 'Chocolate Fudge', 'quantity' => 1]
            ],
            'deliveryAddress' => '123 Cherry Lane, Sweet City, NY 10001',
            'driver' => [
                'name' => 'Michael Johnson',
                'rating' => 4.9,
                'phone' => '+1 (555) 123-4567', 
                'eta' => '15 minutes'
            ],
            'paymentMethod' => 'Credit Card (•••• 4242)',
            'orderTime' => '10:30 AM',
            'specialInstructions' => 'Please include extra napkins and spoons.',
            'timeline' => [
                ['time' => '10:30 AM', 'status' => 'Order Placed', 'description' => 'Your order has been received'],
                ['time' => '10:35 AM', 'status' => 'Order Confirmed', 'description' => 'Payment processed successfully'],
                ['time' => '10:45 AM', 'status' => 'Preparing', 'description' => 'Your ice cream is being prepared', 'current' => true],
                ['time' => null, 'status' => 'Ready for Pickup', 'description' => 'Your order is ready for pickup'],
                ['time' => null, 'status' => 'Out for Delivery', 'description' => 'Driver is on the way'],
                ['time' => null, 'status' => 'Delivered', 'description' => 'Enjoy your ice cream!']
            ]
        ],
        [
            'id' => 'ORD-1235',
            'date' => 'April 20, 2025',
            'status' => 'Out for Delivery',
            'progress' => 75,
            'eta' => '5-10 minutes',
            'price' => 21.50,
            'items' => [
                ['name' => 'Vanilla Bean', 'quantity' => 1],
                ['name' => 'Mint Chocolate Chip', 'quantity' => 2]
            ],
            'deliveryAddress' => '456 Maple Avenue, Sweet City, NY 10002',
            'driver' => [
                'name' => 'Sarah Williams',
                'rating' => 5.0,
                'phone' => '+1 (555) 987-6543', 
                'eta' => '5 minutes'
            ],
            'paymentMethod' => 'PayPal',
            'orderTime' => '11:15 AM',
            'specialInstructions' => 'Please ring doorbell upon arrival.',
            'timeline' => [
                ['time' => '11:15 AM', 'status' => 'Order Placed', 'description' => 'Your order has been received'],
                ['time' => '11:20 AM', 'status' => 'Order Confirmed', 'description' => 'Payment processed successfully'],
                ['time' => '11:30 AM', 'status' => 'Preparing', 'description' => 'Your ice cream is being prepared'],
                ['time' => '11:45 AM', 'status' => 'Ready for Pickup', 'description' => 'Your order is ready for pickup'],
                ['time' => '11:50 AM', 'status' => 'Out for Delivery', 'description' => 'Driver is on the way', 'current' => true],
                ['time' => null, 'status' => 'Delivered', 'description' => 'Enjoy your ice cream!']
            ]
        ],
        [
            'id' => 'ORD-1236',
            'date' => 'April 20, 2025',
            'status' => 'Preparing',
            'progress' => 100,
            'eta' => 'Completed',
            'price' => 34.75,
            'items' => [
                ['name' => 'Chocolate Chip Cookie Dough', 'quantity' => 2],
                ['name' => 'Rocky Road', 'quantity' => 1]
            ],
            'deliveryAddress' => '789 Oakwood Street, Sweet City, NY 10003',
            'driver' => [
                'name' => 'David Thompson',
                'rating' => 4.8,
                'phone' => '+1 (555) 456-7890', 
                'eta' => 'Delivered'
            ],
            'paymentMethod' => 'Apple Pay',
            'orderTime' => '11:00 AM',
            'specialInstructions' => 'Leave at door, please.',
            'timeline' => [
                ['time' => '11:00 AM', 'status' => 'Order Placed', 'description' => 'Your order has been received'],
                ['time' => '11:05 AM', 'status' => 'Order Confirmed', 'description' => 'Payment processed successfully'],
                ['time' => '11:15 AM', 'status' => 'Preparing', 'description' => 'Your ice cream is being prepared'],
                ['time' => '11:30 AM', 'status' => 'Ready for Pickup', 'description' => 'Your order is ready for pickup'],
                ['time' => '11:35 AM', 'status' => 'Out for Delivery', 'description' => 'Driver is on the way'],
                ['time' => '11:50 AM', 'status' => 'Delivered', 'description' => 'Enjoy your ice cream!', 'current' => true]
            ]
        ],
        [
            'id' => 'ORD-1237',
            'date' => 'April 20, 2025',
            'status' => 'Preparing',
            'progress' => 30,
            'eta' => '20-25 minutes',
            'price' => 42.99,
            'items' => [
                ['name' => 'Strawberry Cheesecake', 'quantity' => 1],
                ['name' => 'Butter Pecan', 'quantity' => 1],
                ['name' => 'Cookies & Cream', 'quantity' => 2]
            ],
            'deliveryAddress' => '321 Pine Street, Sweet City, NY 10004',
            'driver' => [
                'name' => 'Jessica Martinez',
                'rating' => 4.7,
                'phone' => '+1 (555) 234-5678', 
                'eta' => '20 minutes'
            ],
            'paymentMethod' => 'Credit Card (•••• 1234)',
            'orderTime' => '12:05 PM',
            'specialInstructions' => 'Extra spoons, please.',
            'timeline' => [
                ['time' => '12:05 PM', 'status' => 'Order Placed', 'description' => 'Your order has been received'],
                ['time' => '12:10 PM', 'status' => 'Order Confirmed', 'description' => 'Payment processed successfully'],
                ['time' => '12:20 PM', 'status' => 'Preparing', 'description' => 'Your ice cream is being prepared', 'current' => true],
                ['time' => null, 'status' => 'Ready for Pickup', 'description' => 'Your order is ready for pickup'],
                ['time' => null, 'status' => 'Out for Delivery', 'description' => 'Driver is on the way'],
                ['time' => null, 'status' => 'Delivered', 'description' => 'Enjoy your ice cream!']
            ]
        ]
    ],
    'past' => [
        [
            'id' => 'ORD-1200',
            'date' => 'April 15, 2025',
            'items' => [
                ['name' => 'Vanilla Bean', 'quantity' => 2],
                ['name' => 'Chocolate Fudge', 'quantity' => 1]
            ],
            'status' => 'Delivered',
            'total' => 16.99,
            'deliveryAddress' => '123 Cherry Lane, Sweet City, NY 10001',
            'deliveryTime' => 'April 15, 2025, 2:45 PM',
            'rating' => 5,
            'review' => 'Delicious as always! The ice cream was perfectly frozen and the delivery was quick.'
        ],
        [
            'id' => 'ORD-1180',
            'date' => 'April 10, 2025',
            'items' => [
                ['name' => 'Strawberry Delight', 'quantity' => 1],
                ['name' => 'Mint Chocolate Chip', 'quantity' => 1]
            ],
            'status' => 'Delivered',
            'total' => 14.50,
            'deliveryAddress' => '789 Oak Street, Sweet City, NY 10003',
            'deliveryTime' => 'April 10, 2025, 1:30 PM',
            'rating' => 4,
            'review' => 'Great taste but the delivery took a bit longer than expected.'
        ],
        [
            'id' => 'ORD-1150',
            'date' => 'April 5, 2025',
            'items' => [
                ['name' => 'Bubblegum Blast', 'quantity' => 2],
                ['name' => 'Caramel Swirl', 'quantity' => 1]
            ],
            'status' => 'Delivered',
            'total' => 19.79,
            'deliveryAddress' => '321 Pine Road, Sweet City, NY 10004',
            'deliveryTime' => 'April 5, 2025, 3:15 PM',
            'rating' => 5,
            'review' => 'Loved the Bubblegum Blast! Will definitely order again.'
        ],
        [
            'id' => 'ORD-1120',
            'date' => 'March 28, 2025',
            'items' => [
                ['name' => 'Coffee Crunch', 'quantity' => 1],
                ['name' => 'Vanilla Bean', 'quantity' => 1]
            ],
            'status' => 'Delivered',
            'total' => 12.99,
            'deliveryAddress' => '456 Maple Avenue, Sweet City, NY 10002',
            'deliveryTime' => 'March 28, 2025, 4:20 PM',
            'rating' => 5,
            'review' => 'Perfect attention to detail! The Coffee Crunch was amazing.'
        ]
    ]
];

$bookings = [
    'upcoming' => [
        [
            'id' => 'BKG-001',
            'event' => "Sophie's Birthday Party",
            'date' => 'May 15, 2025',
            'time' => '3:00 PM - 5:00 PM',
            'location' => '123 Cherry Lane, Sweet City',
            'guests' => '25 people',
            'services' => ['Ice Cream Bar', 'Custom Flavors', 'Toppings Station'],
            'category' => 'Princess Party',
            'price' => 350.00,
            'status' => 'Confirmed'
        ],
        [
            'id' => 'BKG-002',
            'event' => 'Johnson Corporate Event',
            'date' => 'May 20, 2025',
            'time' => '1:00 PM - 3:00 PM',
            'location' => '456 Business Ave, Sweet City',
            'guests' => '50 people',
            'services' => ['Ice Cream Truck', 'Premium Flavors'],
            'category' => 'Corporate Appreciation',
            'price' => 650.00,
            'status' => 'Pending'
        ],
        [
            'id' => 'BKG-003',
            'event' => 'Summer Block Party',
            'date' => 'June 15, 2025',
            'time' => '12:00 PM - 4:00 PM',
            'location' => 'Sunshine Park, Sweet City',
            'guests' => '100 people',
            'services' => ['Ice Cream Truck', 'Popsicles', 'Frozen Yogurt'],
            'category' => 'Summer Fun',
            'price' => 850.00,
            'status' => 'Confirmed'
        ]
    ],
    'past' => [
        [
            'id' => 'BKG-101',
            'event' => 'Taylor Wedding Reception',
            'date' => 'March 25, 2025',
            'time' => '6:00 PM - 8:00 PM',
            'location' => 'Sweet Dream Venue',
            'guests' => '100 people',
            'review' => 'The ice cream bar was the highlight of our reception! Everyone loved it.',
            'rating' => 5,
            'category' => 'Elegant Wedding',
            'price' => 950.00,
            'status' => 'Completed'
        ],
        [
            'id' => 'BKG-102',
            'event' => 'School Fundraiser',
            'date' => 'March 10, 2025',
            'time' => '12:00 PM - 3:00 PM',
            'location' => 'Sunshine Elementary',
            'guests' => '200 people',
            'review' => 'The kids had a blast! Would have liked a few more flavor options.',
            'rating' => 4,
            'category' => 'School Spirit',
            'price' => 750.00,
            'status' => 'Completed'
        ],
        [
            'id' => 'BKG-103',
            'event' => 'Tech Startup Launch',
            'date' => 'February 20, 2025',
            'time' => '5:00 PM - 8:00 PM',
            'location' => 'Innovation Hub',
            'guests' => '75 people',
            'review' => 'The custom flavors were a hit! Everyone loved the creative presentation.',
            'rating' => 5,
            'category' => 'Innovation Celebration',
            'price' => 850.00,
            'status' => 'Completed'
        ]
    ]
];

$metrics = [
    'active' => count($orders['active']),
    'total' => count($orders['active']) + count($orders['past']),
    'upcoming' => count($bookings['upcoming']),
    'avgDelivery' => '18 min'
];

$recommendedItems = [
    ['name' => 'Strawberry Delight', 'price' => 6.99],
    ['name' => 'Chocolate Fudge', 'price' => 5.99],
    ['name' => 'Mint Chocolate Chip', 'price' => 6.99],
    ['name' => 'Vanilla Bean', 'price' => 5.49],
    ['name' => 'Bubblegum Blast', 'price' => 6.99],
    ['name' => 'Caramel Swirl', 'price' => 6.99]
];

$mainTab = isset($_GET['mainTab']) ? $_GET['mainTab'] : 'orders';
$secondaryTab = isset($_GET['secondaryTab']) ? $_GET['secondaryTab'] : ($mainTab === 'orders' ? 'active' : 'upcoming');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders & Bookings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="orders.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen bg-primary-lightest">
    <div class="container mx-auto px-4 py-8">
        <!-- Header with main tabs and view controls -->
        <div class="header-container">
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

            <!-- View Controls moved to individual sections -->
        </div>

        <!-- Tab content -->
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
                            <span class="orders-count"><?php echo count($orders['active']); ?> Orders</span>
                        </div>
                        <div class="order-list">
                            <?php foreach ($orders['active'] as $order): ?>
                                            <div class="order-item">
                                    <div class="order-header">
                                                    <div>
                                        <span class="order-id"><?php echo $order['id']; ?></span>
                                                        <span class="order-date"><?php echo $order['date']; ?></span>
                                                    </div>
                                                    <span class="order-status <?php echo str_replace(' ', '-', strtolower($order['status'])); ?>">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </div>
                                                
                                                <div class="order-progress">
                                                    <div class="order-progress-bar" style="width: <?php echo $order['progress']; ?>%"></div>
                                    </div>
                                                
                                                <div class="order-info">
                                                    <div class="order-eta">
                                                        <i class="far fa-clock"></i>
                                                        ETA: <?php echo $order['eta']; ?>
                                                    </div>
                                                    <div class="order-price">
                                                        $<?php echo number_format($order['price'], 2); ?>
                                                    </div>
                                                </div>
                                            
                                            <div class="order-actions">
                                                <button class="order-action-button track-button">
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
                                                        Last updated: 2 mins ago
                                        </div>
                                                    <a href="#" class="details-link">Details</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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
                                    <div class="past-orders-actions">
                                        <div class="view-controls">
                                            <button class="view-btn active" data-view="grid" title="Grid View">
                                                <i class="fas fa-th-large"></i>
                                            </button>
                                            <button class="view-btn" data-view="list" title="List View">
                                                <i class="fas fa-list"></i>
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
                                                $<?php echo number_format($order['total'], 2); ?>
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
                                        <div class="view-controls">
                                            <button class="view-btn active" data-view="grid" title="Grid View">
                                                <i class="fas fa-th-large"></i>
                                            </button>
                                            <button class="view-btn" data-view="list" title="List View">
                                                <i class="fas fa-list"></i>
                                            </button>
                                        </div>
                                        <button class="calendar-view-btn">
                                            <i class="fas fa-calendar-week"></i>
                                            <span>Calendar View</span>
                                        </button>
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
                                    <!-- Booking Card 1 -->
                                    <div class="booking-card">
                                        <div class="booking-image">
                                            <div class="image-placeholder">
                                                <i class="fas fa-image"></i>
                                            </div>
                                            <div class="booking-title-overlay">
                                                <h4>Sophie's Birthday Party</h4>
                                                <p>May 5, 2025</p>
                                                <span class="status-badge confirmed">Confirmed</span>
                                            </div>
                                        </div>
                                        
                                        <div class="booking-info">
                                            <div class="booking-detail">
                                                <i class="fas fa-clock"></i>
                                                <span>3:00 PM - 5:00 PM</span>
                                            </div>
                                            
                                            <div class="booking-detail">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span>123 Cherry Lane, Sweet City</span>
                                            </div>
                                            
                                            <div class="booking-detail">
                                                <i class="fas fa-users"></i>
                                                <span>25 guests</span>
                                            </div>
                                            
                                            <div class="booking-services">
                                                <span class="service-tag">Ice Cream Bar</span>
                                                <span class="service-tag">Custom Flavors</span>
                                                <span class="service-tag">Toppings Station</span>
                                            </div>
                                            
                                            <div class="view-details-link">
                                                <a href="#" class="toggle-details" data-booking="1">View details</a>
                                                <i class="fas fa-chevron-right"></i>
                                            </div>
                                        </div>
                                        
                                        <div class="booking-details-expanded" id="booking-details-1">
                                            <div class="detail-section">
                                                <h5>Contact Person</h5>
                                                <div class="contact-detail">
                                                    <i class="fas fa-user"></i>
                                                    <span>Robert Johnson</span>
                                                </div>
                                                <div class="contact-detail">
                                                    <i class="fas fa-phone"></i>
                                                    <span>+1 (555) 456-7890</span>
                                                </div>
                                            </div>
                                            
                                            <div class="detail-section">
                                                <h5>Booking Details</h5>
                                                <div class="booking-detail-row">
                                                    <span class="detail-label">Total Amount:</span>
                                                    <span class="detail-value price">$650.00</span>
                                                </div>
                                                <div class="booking-detail-row">
                                                    <span class="detail-label">Payment Status:</span>
                                                    <span class="detail-value status paid">Paid</span>
                                                </div>
                                            </div>
                                            
                                            <div class="detail-section">
                                                <h5>Special Requests</h5>
                                                <div class="special-requests">
                                                    <p>Dairy-free options required for 5 guests.</p>
                                                </div>
                                            </div>
                                            
                                            <a href="#" class="hide-details-link" data-booking="1">
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
                                    
                                    <!-- Booking Card 2 -->
                                    <div class="booking-card">
                                        <div class="booking-image">
                                            <div class="image-placeholder">
                                                <i class="fas fa-image"></i>
                                            </div>
                                            <div class="booking-title-overlay">
                                                <h4>Johnson Corporate Event</h4>
                                                <p>May 12, 2025</p>
                                                <span class="status-badge pending">Pending</span>
                                            </div>
                                        </div>
                                        
                                        <div class="booking-info">
                                            <div class="booking-detail">
                                                <i class="fas fa-clock"></i>
                                                <span>1:00 PM - 3:00 PM</span>
                                            </div>
                                            
                                            <div class="booking-detail">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span>456 Business Ave, Sweet City</span>
                                            </div>
                                            
                                            <div class="booking-detail">
                                                <i class="fas fa-users"></i>
                                                <span>50 guests</span>
                                            </div>
                                            
                                            <div class="booking-services">
                                                <span class="service-tag">Ice Cream Truck</span>
                                                <span class="service-tag">Premium Flavors</span>
                                            </div>
                                            
                                            <div class="view-details-link">
                                                <a href="#" class="toggle-details" data-booking="2">View details</a>
                                                <i class="fas fa-chevron-right"></i>
                                            </div>
                                        </div>
                                        
                                        <div class="booking-details-expanded" id="booking-details-2">
                                            <div class="detail-section">
                                                <h5>Contact Person</h5>
                                                <div class="contact-detail">
                                                    <i class="fas fa-user"></i>
                                                    <span>Robert Johnson</span>
                                                </div>
                                                <div class="contact-detail">
                                                    <i class="fas fa-phone"></i>
                                                    <span>+1 (555) 456-7890</span>
                                                </div>
                                            </div>
                                            
                                            <div class="detail-section">
                                                <h5>Booking Details</h5>
                                                <div class="booking-detail-row">
                                                    <span class="detail-label">Total Amount:</span>
                                                    <span class="detail-value price">$650.00</span>
                                                </div>
                                                <div class="booking-detail-row">
                                                    <span class="detail-label">Payment Status:</span>
                                                    <span class="detail-value status paid">Paid</span>
                                                </div>
                                            </div>
                                            
                                            <div class="detail-section">
                                                <h5>Special Requests</h5>
                                                <div class="special-requests">
                                                    <p>Dairy-free options required for 5 guests.</p>
                                                </div>
                                            </div>
                                            
                                            <a href="#" class="hide-details-link" data-booking="2">
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
                                    
                                    <!-- Booking Card 3 -->
                                    <div class="booking-card">
                                        <div class="booking-image">
                                            <div class="image-placeholder">
                                                <i class="fas fa-image"></i>
                                            </div>
                                            <div class="booking-title-overlay">
                                                <h4>Summer Block Party</h4>
                                                <p>June 15, 2025</p>
                                                <span class="status-badge confirmed">Confirmed</span>
                                            </div>
                                        </div>
                                        
                                        <div class="booking-info">
                                            <div class="booking-detail">
                                                <i class="fas fa-clock"></i>
                                                <span>12:00 PM - 4:00 PM</span>
                                            </div>
                                            
                                            <div class="booking-detail">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span>Sunshine Park, Sweet City</span>
                                            </div>
                                            
                                            <div class="booking-detail">
                                                <i class="fas fa-users"></i>
                                                <span>100 guests</span>
                                            </div>
                                            
                                            <div class="booking-services">
                                                <span class="service-tag">Ice Cream Truck</span>
                                                <span class="service-tag">Popsicles</span>
                                                <span class="service-tag">Frozen Yogurt</span>
                                            </div>
                                            
                                            <div class="view-details-link">
                                                <a href="#" class="toggle-details" data-booking="3">View details</a>
                                                <i class="fas fa-chevron-right"></i>
                                            </div>
                                        </div>
                                        
                                        <div class="booking-details-expanded" id="booking-details-3">
                                            <div class="detail-section">
                                                <h5>Contact Person</h5>
                                                <div class="contact-detail">
                                                    <i class="fas fa-user"></i>
                                                    <span>Robert Johnson</span>
                                                </div>
                                                <div class="contact-detail">
                                                    <i class="fas fa-phone"></i>
                                                    <span>+1 (555) 456-7890</span>
                                                </div>
                                            </div>
                                            
                                            <div class="detail-section">
                                                <h5>Booking Details</h5>
                                                <div class="booking-detail-row">
                                                    <span class="detail-label">Total Amount:</span>
                                                    <span class="detail-value price">$650.00</span>
                                                </div>
                                                <div class="booking-detail-row">
                                                    <span class="detail-label">Payment Status:</span>
                                                    <span class="detail-value status paid">Paid</span>
                                                </div>
                                            </div>
                                            
                                            <div class="detail-section">
                                                <h5>Special Requests</h5>
                                                <div class="special-requests">
                                                    <p>Dairy-free options required for 5 guests.</p>
                                                </div>
                                            </div>
                                            
                                            <a href="#" class="hide-details-link" data-booking="3">
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
                                        <div class="view-controls">
                                            <button class="view-btn active" data-view="grid" title="Grid View">
                                                <i class="fas fa-th-large"></i>
                                            </button>
                                            <button class="view-btn" data-view="list" title="List View">
                                                <i class="fas fa-list"></i>
                                            </button>
                                        </div>
                                        <button class="calendar-view-btn">
                                            <i class="fas fa-calendar-week"></i>
                                            <span>Calendar View</span>
                                        </button>
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
                                    <!-- Past Booking Card -->
                                    <div class="booking-card">
                                        <div class="booking-image">
                                            <div class="image-placeholder">
                                                <i class="fas fa-image"></i>
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
                                                    <span class="detail-value price">$<?php echo number_format($booking['price'], 2); ?></span>
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

        <!-- Quick Actions Section -->
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

        <!-- Recommended Items Section -->
        <div class="recommended-items">
            <div class="items-heading">
                <h3>Recommended For You</h3>
                <div class="item-navigation">
                    <button class="nav-button prev-button">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="nav-button next-button">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            
            <div class="items-grid">
                <div class="item-card">
                    <button class="item-favorite">
                        <i class="fas fa-heart"></i>
                    </button>
                    <div class="item-image">
                        <!-- Empty light gray background placeholder -->
                    </div>
                    <span class="item-title">Strawberry Delight</span>
                    <div class="item-price-row">
                        <span class="item-price">$6.99</span>
                        <button class="item-add">
                            <i class="fas fa-shopping-cart"></i>
                        </button>
                    </div>
                </div>
                
                <div class="item-card">
                    <button class="item-favorite">
                        <i class="fas fa-heart"></i>
                    </button>
                    <div class="item-image">
                        <!-- Empty light gray background placeholder -->
                    </div>
                    <span class="item-title">Chocolate Fudge</span>
                    <div class="item-price-row">
                        <span class="item-price">$5.99</span>
                        <button class="item-add">
                            <i class="fas fa-shopping-cart"></i>
                        </button>
                    </div>
                </div>
                
                <div class="item-card">
                    <button class="item-favorite">
                        <i class="fas fa-heart"></i>
                    </button>
                    <div class="item-image">
                        <!-- Empty light gray background placeholder -->
                    </div>
                    <span class="item-title">Mint Chocolate Chip</span>
                    <div class="item-price-row">
                        <span class="item-price">$6.99</span>
                        <button class="item-add">
                            <i class="fas fa-shopping-cart"></i>
                        </button>
                    </div>
                </div>
                
                <div class="item-card">
                    <button class="item-favorite">
                        <i class="fas fa-heart"></i>
                    </button>
                    <div class="item-image">
                        <!-- Empty light gray background placeholder -->
                    </div>
                    <span class="item-title">Vanilla Bean</span>
                    <div class="item-price-row">
                        <span class="item-price">$5.99</span>
                        <button class="item-add">
                            <i class="fas fa-shopping-cart"></i>
                        </button>
                    </div>
                </div>
                
                <div class="item-card">
                    <button class="item-favorite">
                        <i class="fas fa-heart"></i>
                    </button>
                    <div class="item-image">
                        <!-- Empty light gray background placeholder -->
                    </div>
                    <span class="item-title">Bubblegum Blast</span>
                    <div class="item-price-row">
                        <span class="item-price">$6.99</span>
                        <button class="item-add">
                            <i class="fas fa-shopping-cart"></i>
                        </button>
                    </div>
                </div>
                
                <div class="item-card">
                    <button class="item-favorite">
                        <i class="fas fa-heart"></i>
                    </button>
                    <div class="item-image">
                        <!-- Empty light gray background placeholder -->
                    </div>
                    <span class="item-title">Caramel Swirl</span>
                    <div class="item-price-row">
                        <span class="item-price">$6.99</span>
                        <button class="item-add">
                            <i class="fas fa-shopping-cart"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.5/gsap.min.js"></script>
    <script src="orders.js"></script>
</body>
</html>
