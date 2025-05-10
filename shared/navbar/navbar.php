<?php
// Standard navbar
// Robustly handle missing $customer variable
if (!isset($customer) || !is_array($customer)) {
    $customer = [
        'profile_picture' => '',
        'full_name' => 'Guest'
    ];
}

// Detect if we're in a landing page
$isLandingPage = isset($isLandingPage) ? $isLandingPage : false;

// Set cart URL based on whether it's landing page or customer page
$cartUrl = '/ayskrimWebsite/customerPage/cart/cart.php';

$profilePicture = isset($customer['profile_picture']) && !empty($customer['profile_picture'])
    ? '/ayskrimWebsite/assets/images/profiles/' . htmlspecialchars($customer['profile_picture'])
    : '/ayskrimWebsite/assets/images/default.png';
?>

<!-- Floating Fixed Header with Glassmorphism Effect -->
<header class="fixed-header">
    <div class="navbar-anchor">
        <div class="navbar-bg">
            <div class="container">
                <div class="navbar-content">
                     <!-- Left Section (Logo & Search Icon) -->
                     <div class="logo-wrapper">
                        <div class="logo-search-container">
                            <!-- Logo -->
                            <div class="logo-container" id="logoContainer">
                                <div class="logo-circle">
                                    <div class="logo-inner">
                                        <div class="logo-border">
                                            <div class="logo-text">
                                                <img src="/ayskrimWebsite/assets/images/logo.png" alt="Ayskrim Logo" class="logo">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Search Icon and Input -->
                            <div class="search-wrapper">
                                <div class="search-icon" id="searchIcon" aria-label="Search" role="button" tabindex="0">
                                    
                                    <lord-icon
                                        src="https://cdn.lordicon.com/wjyqkiew.json"
                                        trigger="loop-on-hover"
                                        stroke="bold"
                                        state="loop-spin"
                                        colors="primary:#ee66aa,secondary:#f49cc8"
                                        style="width:35px;height:35px"
                                        data-search-active="false">
                                    </lord-icon>
                                </div>
                                <div class="search-input-wrapper">
                                    <input type="text" class="search-input" id="searchInput" placeholder="Search..." aria-label="Search input">
                                    <div class="close-search" id="closeSearch" aria-label="Close search" role="button" tabindex="0">
                                        <i class="fas fa-times" style="font-size: 1rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop Navigation Links (Center) -->
                    <div class="nav-links-container">
                        <nav class="desktop-nav">
                            <?php if ($isLandingPage): ?>
                                <!-- Landing Page Links (For Guests) -->
                                <a href="/ayskrimWebsite/landingPage/home/home.php" class="nav-link <?php echo $page === 'home' ? 'active' : ''; ?>">
                                    <img src="/ayskrimWebsite/assets/icons/home.png" alt="Home Icon" class="nav-icon" />
                                    <span>Home</span>
                                </a>
                            <?php else: ?>
                                <!-- Customer Dashboard Link (For Logged-in Users) -->
                                <a href="/ayskrimWebsite/customerPage/dashboard/dashboard.php" class="nav-link <?php echo $page === 'dashboard' ? 'active' : ''; ?>">
                                    <img src="/ayskrimWebsite/assets/icons/dashboard.png" alt="Dashboard Icon" class="nav-icon" />
                                    <span>Dashboard</span>
                                </a>
                            <?php endif; ?>
                            
                            <!-- Menu Link (Common) -->
                            <a href="/ayskrimWebsite/customerPage/menu/menu.php" class="nav-link <?php echo $page === 'menu' ? 'active' : ''; ?>">
                                <img src="/ayskrimWebsite/assets/icons/menu.png" alt="Menu Icon" class="nav-icon" />
                                <span>Menu</span>
                            </a>
                            
                            <!-- Orders & Bookings Link -->
                            <?php if ($isLandingPage): ?>
                                <a href="/ayskrimWebsite/landingPage/login.php?redirect=orders" class="nav-link <?php echo $page === 'orders' ? 'active' : ''; ?>">
                                    <img src="/ayskrimWebsite/assets/icons/orders.png" alt="Orders Icon" class="nav-icon" />
                                    <span>Orders & Bookings</span>
                                </a>
                            <?php else: ?>
                                <a href="/ayskrimWebsite/customerPage/orders/orders.php" class="nav-link <?php echo $page === 'orders' ? 'active' : ''; ?>">
                                    <img src="/ayskrimWebsite/assets/icons/orders.png" alt="Orders Icon" class="nav-icon" />
                                    <span>Orders & Bookings</span>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>

                    <!-- Right Section: Cart and Profile/Sign In -->
                    <div class="right-section">
                        <!-- Cart Icon with Lord Icon -->
                        <a href="<?php echo $cartUrl; ?>" class="cart-icon" aria-label="Shopping Cart">
                            <lord-icon
                                src="https://cdn.lordicon.com/ggirntso.json"
                                trigger="hover"
                                stroke="bold"
                                colors="primary:#ee66aa,secondary:#f49cc8"
                                style="width:38px;height:38px">
                            </lord-icon>
                            <span class="cart-count">0</span>
                        </a>
                        
                        <?php if ($isLandingPage): ?>
                            <!-- Sign In Button for Landing Page -->
                            <div class="auth-buttons">
                                <a href="/ayskrimWebsite/landingPage/login.php" class="btn btn-primary btn-sm">Sign In</a>
                            </div>
                        <?php else: ?>
                            <!-- Profile Dropdown for Customer Pages -->
                            <div class="profile-dropdown" id="profileDropdown">
                                <button class="profile-button" aria-expanded="false" aria-haspopup="true">
                                    <img src="<?php echo $profilePicture; ?>" alt="Profile" class="profile-image" width="32" height="32">
                                    <span class="profile-name"><?php echo isset($customer['full_name']) ? htmlspecialchars($customer['full_name']) : 'Guest'; ?></span>
                                    <i class="fas fa-chevron-down" aria-hidden="true"></i>
                                </button>
                                
                                <div class="dropdown-menu" id="profileMenu" aria-labelledby="profileDropdown">
                                    <a href="/ayskrimWebsite/customerPage/profile/profile.php" class="dropdown-item">
                                        <i class="fas fa-user"></i> My Profile
                                    </a>
                                    <a href="/ayskrimWebsite/customerPage/settings/settings.php" class="dropdown-item">
                                        <i class="fas fa-cog"></i> Settings
                                    </a>
                                    <a href="/ayskrimWebsite/customerPage/support/support.php" class="dropdown-item">
                                        <i class="fas fa-question-circle"></i> Support / Help Center
                                    </a>
                                    <form action="/ayskrimWebsite/api/auth/logoutApi.php" method="post" class="dropdown-item logout-form">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION[CSRF_TOKEN_KEY] ?? ''); ?>">
                                        <button type="submit" class="logout-btn">
                                            <i class="fas fa-sign-out-alt"></i> Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<?php if ($isLandingPage): ?>
<style>
    /* Auth buttons styling for landing page */
    .auth-buttons {
        display: flex;
        align-items: center;
        margin-left: auto;
        margin-right: 5px;
        padding-right: 0;
        position: relative;
        right: 0;
    }
    
    .right-section {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding-right: 0;
        margin-right: 0;
    }
    
    .btn {
        border-radius: 12px;
        font-weight: 500;
        transition: all 0.2s ease;
        font-family: 'Poppins', sans-serif;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .btn-sm {
        padding: 10px 24px;
        font-size: 0.95rem;
    }
    
    .btn-primary {
        background-color: #F04C99;
        color: white;
        border: 1px solid #F04C99;
    }
    
    .btn-primary:hover {
        background-color: #E33A8A;
        border-color: #E33A8A;
        box-shadow: 0 4px 8px rgba(240, 76, 153, 0.3);
    }
</style>
<?php endif; ?>