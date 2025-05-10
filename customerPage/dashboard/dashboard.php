<?php
// Include the session initializer to ensure user is logged in
require_once __DIR__ . '/../../shared/session_init.php';

// Sample data - in a real application, this would come from your database
$activeNav = "home";
$page = 'dashboard'; // Set current page for navbar active state

$wishlistItems = [
    [
        "id" => 1,
        "name" => "Strawberry Dream",
        "description" => "Sweet strawberry ice cream with fresh berries",
        "price" => "$4.99",
        "image" => "placeholder.jpg",
        "label" => "Popular"
    ],
    [
        "id" => 2,
        "name" => "Forest Mama",
        "description" => "Blueberry and raspberry blend with cream",
        "price" => "$5.49",
        "image" => "placeholder.jpg",
        "label" => "New"
    ],
    [
        "id" => 3,
        "name" => "Forest Prince",
        "description" => "Mint chocolate-infused ice cream",
        "price" => "$5.49",
        "image" => "placeholder.jpg",
        "label" => "New"
    ],
    [
        "id" => 4,
        "name" => "Purple Paradise",
        "description" => "Creamy vanilla with ube swirl and coconut",
        "price" => "$5.99",
        "image" => "placeholder.jpg",
        "label" => "Limited"
    ],
    [
        "id" => 5,
        "name" => "Mango Tango",
        "description" => "Alphonso mango with a hint of lime",
        "price" => "$4.99",
        "image" => "placeholder.jpg"
    ],
    [
        "id" => 6,
        "name" => "Cherry On Top",
        "description" => "Cherry ice cream with chocolate chunks",
        "price" => "$4.79",
        "image" => "placeholder.jpg",
        "label" => "New"
    ]
];

$reviews = [
    [
        "id" => 1,
        "product" => "Vanilla Bean Supreme",
        "productImage" => "placeholder.jpg",
        "rating" => 5,
        "text" => "This is the best vanilla ice cream I've ever tasted! So creamy and the vanilla flavor is perfect.",
        "date" => "May 2, 2025"
    ],
    [
        "id" => 2,
        "product" => "Chocolate Fudge Delight",
        "productImage" => "placeholder.jpg",
        "rating" => 4,
        "text" => "Delicious chocolate flavor with generous fudge swirls. Could use more fudge chunks though.",
        "date" => "April 28, 2025"
    ]
];

function renderStars($rating) {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $html .= '<svg class="star filled" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" /></svg>';
        } else {
            $html .= '<svg class="star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" /></svg>';
        }
    }
    return $html;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="session-timeout" content="<?php echo SESSION_TIMEOUT; ?>">
    <title>Scoop Space - Ice Cream Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="/ayskrimWebsite/shared/navbar/navbar.css">
    <link rel="stylesheet" href="/ayskrimWebsite/shared/header/header.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Lord Icon for animated icons -->
    <script src="https://cdn.lordicon.com/xdjxvujz.js"></script>
    <!-- Shared script for page transitions -->
    <script src="/ayskrimWebsite/shared/script.js"></script>
</head>
<body>
    <!-- Include components in correct order: header first, then navbar -->
    <?php include_once(__DIR__ . '/../../shared/header/header.php'); ?>
    <?php include_once(__DIR__ . '/../../shared/navbar/navbar.php'); ?>
    
    <div class="dashboard">
        <!-- Main Content -->
        <main>
            <div class="container">
                <!-- Header with notification icon - changed class to dashboard-header -->
                <div class="dashboard-header">
                    <div>
                        <h1>Welcome back, <?php echo htmlspecialchars($customer['full_name']); ?>! <span>👋</span></h1>
                        <p>Ready to explore your flavor journey?</p>
                    </div>
                    <div class="notification-wrapper">
                        <button class="notification-btn">
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        </button>
                        <span class="notification-badge"></span>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="summary-cards">
                    <div class="card">
                        <div class="card-content">
                            <div>
                                <h3>Rewards Points</h3>
                                <p class="value">750</p>
                                <p class="subtitle">250 points until next reward</p>
                            </div>
                            <div class="icon-container pink">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 12 20 22 4 22 4 12"></polyline><rect x="2" y="7" width="20" height="5"></rect><line x1="12" y1="22" x2="12" y2="7"></line><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-content">
                            <div>
                                <h3>Wishlist Items</h3>
                                <p class="value">8</p>
                            </div>
                            <div class="icon-container green">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-content">
                            <div>
                                <h3>Reviews Posted</h3>
                                <p class="value">12</p>
                            </div>
                            <div class="icon-container purple">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-content dual-content">
                            <div class="orders-section">
                                <div>
                                    <h3>Total Orders</h3>
                                    <p class="value">16</p>
                                </div>
                                <div class="icon-container blue">
                                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16.5 9.4l-9-5.19M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"></path><path d="M3.27 6.96L12 12.01l8.73-5.05M12 22.08V12"></path></svg>
                                </div>
                            </div>
                            <div class="bookings-section">
                                <div>
                                    <h3>Total Bookings</h3>
                                    <p class="value">8</p>
                                </div>
                                <div class="icon-container yellow">
                                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Two Column Layout -->
                <div class="two-column">
                    <!-- Rewards & Coupons - More compact -->
                    <section class="section">
                        <div class="section-header">
                            <h2>Rewards & Coupons</h2>
                            <a href="#" class="view-all">View All ›</a>
                        </div>

                        <div class="points-card">
                            <div class="points-icon">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 12 20 22 4 22 4 12"></polyline><rect x="2" y="7" width="20" height="5"></rect><line x1="12" y1="22" x2="12" y2="7"></line><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path></svg>
                            </div>
                            <div>
                                <div class="points-label">Total Points</div>
                                <div class="points-value">750</div>
                            </div>
                        </div>

                        <div class="progress-container">
                            <div class="progress-label">Next Reward: Free Scoop</div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 75%"></div>
                            </div>
                        </div>

                        <div class="coupons">
                            <div class="coupon">
                                <div>
                                    <h3>Buy 1 Get 1 Free</h3>
                                    <p>Expires in 5 days</p>
                                </div>
                                <button class="use-btn">Use</button>
                            </div>

                            <div class="coupon">
                                <div>
                                    <h3>20% Off Next Order</h3>
                                    <p>Expires in 12 days</p>
                                </div>
                                <button class="use-btn">Use</button>
                            </div>
                        </div>
                    </section>

                    <!-- Wishlist - Updated product cards with half image, half content layout -->
                    <section class="section">
                        <div class="section-header">
                            <h2>Wishlist</h2>
                            <a href="#" class="view-all">View All ›</a>
                        </div>

                        <div class="product-grid">
                            <?php
                            $displayCount = min(count($wishlistItems), 3);
                            for ($i = 0; $i < $displayCount; $i++) {
                                $item = $wishlistItems[$i];
                            ?>
                            <div class="product-card">
                                <div class="product-image-container">
                                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="product-image">
                                    <?php if (isset($item['label'])) { ?>
                                        <div class="product-label <?php echo strtolower($item['label']); ?>"><?php echo $item['label']; ?></div>
                                    <?php } ?>
                                    <button class="wishlist-btn">
                                        <svg class="icon heart-filled" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                    </button>
                                </div>
                                <div class="product-info">
                                    <h3><?php echo $item['name']; ?></h3>
                                    <p><?php echo $item['description']; ?></p>
                                    <div class="product-footer">
                                        <span class="product-price"><?php echo $item['price']; ?></span>
                                        <button class="add-to-cart-btn">Add to Cart</button>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>

                            <?php if (count($wishlistItems) > 3) { ?>
                            <div class="product-card more-card">
                                <div class="more-overlay">
                                    <p>+<?php echo count($wishlistItems) - 3; ?> more</p>
                                </div>
                                <div class="stacked-cards">
                                    <div class="stacked-card card-1"></div>
                                    <div class="stacked-card card-2"></div>
                                    <div class="product-card-inner">
                                        <div class="product-image-container">
                                            <img src="<?php echo $wishlistItems[3]['image']; ?>" alt="<?php echo $wishlistItems[3]['name']; ?>" class="product-image">
                                            <?php if (isset($wishlistItems[3]['label'])) { ?>
                                                <div class="product-label <?php echo strtolower($wishlistItems[3]['label']); ?>"><?php echo $wishlistItems[3]['label']; ?></div>
                                            <?php } ?>
                                        </div>
                                        <div class="product-info">
                                            <h3><?php echo $wishlistItems[3]['name']; ?></h3>
                                            <p><?php echo $wishlistItems[3]['description']; ?></p>
                                            <div class="product-footer">
                                                <span class="product-price"><?php echo $wishlistItems[3]['price']; ?></span>
                                                <button class="add-to-cart-btn">Add to Cart</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </section>
                </div>

                <!-- Reviews -->
                <section class="section reviews-section">
                    <div class="section-header">
                        <h2>Your Reviews</h2>
                        <a href="#" class="view-all">View All ›</a>
                    </div>

                    <div class="reviews-grid">
                        <?php foreach ($reviews as $review) { ?>
                        <div class="review-card">
                            <div class="review-content">
                                <div class="product-thumbnail">
                                    <img src="<?php echo $review['productImage']; ?>" alt="<?php echo $review['product']; ?>">
                                </div>
                                <div class="review-details">
                                    <div class="review-header">
                                        <h3><?php echo $review['product']; ?></h3>
                                        <div class="review-rating">
                                            <?php echo renderStars($review['rating']); ?>
                                        </div>
                                    </div>
                                    <p class="review-text"><?php echo $review['text']; ?></p>
                                    <p class="review-date"><?php echo $review['date']; ?></p>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </section>
            </div>
        </main>

        <!-- Floating Vertical Navigation Bar -->
        <nav class="sidebar">
            <div class="nav-container">
                <div class="nav-item <?php echo $activeNav === 'home' ? 'active' : ''; ?>" data-tooltip="Scoop Space">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </div>
                <div class="nav-item <?php echo $activeNav === 'rewards' ? 'active' : ''; ?>" data-tooltip="Rewards">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 12 20 22 4 22 4 12"></polyline><rect x="2" y="7" width="20" height="5"></rect><line x1="12" y1="22" x2="12" y2="7"></line><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path></svg>
                </div>
                <div class="nav-item <?php echo $activeNav === 'wishlist' ? 'active' : ''; ?>" data-tooltip="Wishlist">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                </div>
                <div class="nav-item <?php echo $activeNav === 'reviews' ? 'active' : ''; ?>" data-tooltip="Reviews">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                </div>
                <div class="nav-item <?php echo $activeNav === 'history' ? 'active' : ''; ?>" data-tooltip="History">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
                <div class="nav-item <?php echo $activeNav === 'achievements' ? 'active' : ''; ?>" data-tooltip="Achievements">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>
                </div>
                <div class="nav-item <?php echo $activeNav === 'referrals' ? 'active' : ''; ?>" data-tooltip="Referrals">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
            </div>
        </nav>
    </div>

    <script src="dashboard.js"></script>
    <script src="/ayskrimWebsite/shared/navbar/navbar.js"></script>
    <script src="/ayskrimWebsite/shared/navbar/navbar-lottie.js"></script>
    <script src="/ayskrimWebsite/shared/js/session.js"></script>
</body>
</html>
