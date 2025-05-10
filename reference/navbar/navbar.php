<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ice Cream Shop</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="fixed-header">
        <div class="navbar-bg">
            <div class="container">
                <div class="navbar-content">
                    <!-- Logo Wrapper -->
                    <div class="logo-wrapper">
                        <div class="logo-container" id="logoContainer">
                            <div class="logo-circle">
                                <div class="logo-inner">
                                    <div class="logo-border">
                                        <div class="logo-text">
                                            <div class="brand-name">ICE CREAM</div>
                                            <div class="logo-svg">
                                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M12 17L7 22H17L12 17Z" fill="#C71585" />
                                                    <path
                                                        d="M7 12C7 8.13401 10.134 5 14 5C17.866 5 21 8.13401 21 12C21 15.866 17.866 19 14 19H7V12Z"
                                                        fill="#C71585" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop navigation links (centered) -->
                    <nav class="desktop-nav">
                        <a href="#" class="nav-item">
                            <i class="fas fa-home"></i>
                            <span>Home</span>
                        </a>
                        <a href="#" class="nav-item">
                            <i class="fas fa-utensils"></i>
                            <span>Menu</span>
                        </a>
                        <a href="#" class="nav-item">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Orders</span>
                        </a>
                        <a href="#" class="nav-item">
                            <i class="fas fa-calendar"></i>
                            <span>Bookings</span>
                        </a>
                        <a href="#" class="nav-item">
                            <i class="fas fa-heart"></i>
                            <span>Wishlist</span>
                        </a>
                        <a href="#" class="nav-item">
                            <i class="fas fa-star"></i>
                            <span>Reviews</span>
                        </a>
                    </nav>

                    <!-- Right-side icons -->
                    <div class="right-section">
                        <!-- Mobile menu button would go here, excluded for desktop focus -->
                        <a href="#" class="cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count">3</span>
                            <span class="sr-only">Cart</span>
                        </a>
                        <div class="profile-section">
                            <div class="profile-image">
                                <img src="placeholder.svg" alt="Profile">
                            </div>
                            <span class="profile-name">John Doe</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="content-placeholder">
        <h1>Scroll down to see the logo resize while the navbar stays fixed.</h1>
    </div>

    <script src="navbar.js"></script>
</body>
</html>