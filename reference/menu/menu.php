<?php
// Determine which page to display
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Sample product data for menu page
$categories = ["All Categories", "Bestseller", "New Arrivals", "Seasonal", "Limited Edition"];

$products = [
  [
    "id" => 1,
    "name" => "Strawberry Dream",
    "description" => "Sweet strawberry ice cream with fresh berries",
    "price" => "4.99",
    "image" => "placeholder.jpg",
    "badge" => "Popular",
    "rating" => 4.8,
  ],
  [
    "id" => 2,
    "name" => "Forest Mama",
    "description" => "Blueberry and raspberry blend with cream",
    "price" => "5.49",
    "image" => "placeholder.jpg",
    "badge" => "New",
    "rating" => 4.6,
  ],
  [
    "id" => 3,
    "name" => "Strawberry Dream",
    "description" => "Sweet strawberry ice cream with fresh berries",
    "price" => "4.99",
    "image" => "placeholder.jpg",
    "rating" => 4.9,
  ],
  [
    "id" => 4,
    "name" => "Strawberry Dream",
    "description" => "Sweet strawberry ice cream with fresh berries",
    "price" => "4.99",
    "image" => "placeholder.jpg",
    "badge" => "Limited",
    "rating" => 4.7,
  ],
  [
    "id" => 5,
    "name" => "Strawberry Dream",
    "description" => "Sweet strawberry ice cream with fresh berries",
    "price" => "4.99",
    "image" => "placeholder.jpg",
    "rating" => 4.5,
  ],
  [
    "id" => 6,
    "name" => "Strawberry Dream",
    "description" => "Sweet strawberry ice cream with fresh berries",
    "price" => "4.99",
    "image" => "placeholder.jpg",
    "rating" => 4.8,
  ],
  [
    "id" => 7,
    "name" => "Strawberry Dream",
    "description" => "Sweet strawberry ice cream with fresh berries",
    "price" => "4.99",
    "image" => "placeholder.jpg",
    "badge" => "Popular",
    "rating" => 4.9,
  ],
  [
    "id" => 8,
    "name" => "Strawberry Dream",
    "description" => "Sweet strawberry ice cream with fresh berries",
    "price" => "4.99",
    "image" => "placeholder.jpg",
    "rating" => 4.7,
  ],
];

// Sample bestsellers data for home page
$bestsellers = [
  [
    "id" => 1,
    "name" => "Flavor 1",
    "image" => "product1.jpg",
  ],
  [
    "id" => 2,
    "name" => "Flavor 2",
    "image" => "product2.jpg",
  ],
  [
    "id" => 3,
    "name" => "Flavor 3",
    "image" => "product3.jpg",
  ],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page === 'home' ? "Rey's Ice Cream Shop - Home" : "Sweet Delights - Menu"; ?></title>
    <link rel="stylesheet" href="menu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="<?php echo $page === 'home' ? 'home-page' : 'min-h-screen'; ?>">
        <!-- Header -->
        <header class="site-header">
            <div class="container">
                <div class="logo">
                    <a href="menu.php" class="logo-link">
                        <div class="logo-text">
                            Sweet<br>Delights
                        </div>
                    </a>
                </div>

                <nav class="desktop-nav">
                    <a href="menu.php" class="nav-link <?php echo $page === 'home' ? 'active' : ''; ?>">Home</a>
                    <a href="menu.php?page=menu" class="nav-link <?php echo $page === 'menu' ? 'active' : ''; ?>">Menu</a>
                    <a href="<?php echo $page === 'home' ? '#about' : 'menu.php#about'; ?>" class="nav-link">About</a>
                    <a href="<?php echo $page === 'home' ? '#contact' : 'menu.php#contact'; ?>" class="nav-link">Contact</a>
                </nav>

                <div class="header-actions">
                    <button class="icon-button">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="icon-button cart-button">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">2</span>
                    </button>
                    <?php if ($page === 'home'): ?>
                    <button class="login-button">Log in</button>
                    <button class="signup-button">Sign up</button>
                    <?php else: ?>
                    <button class="icon-button">
                        <i class="fas fa-user"></i>
                    </button>
                    <?php endif; ?>
                    <button class="mobile-menu-button">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </header>

        <!-- Mobile Navigation -->
        <div class="mobile-nav" id="mobileNav">
            <div class="mobile-nav-container">
                <button class="close-nav-button">
                    <i class="fas fa-times"></i>
                </button>
                <nav class="mobile-nav-links">
                    <a href="menu.php" class="nav-link <?php echo $page === 'home' ? 'active' : ''; ?>">Home</a>
                    <a href="menu.php?page=menu" class="nav-link <?php echo $page === 'menu' ? 'active' : ''; ?>">Menu</a>
                    <a href="<?php echo $page === 'home' ? '#about' : 'menu.php#about'; ?>" class="nav-link">About</a>
                    <a href="<?php echo $page === 'home' ? '#contact' : 'menu.php#contact'; ?>" class="nav-link">Contact</a>
                </nav>
            </div>
        </div>

        <?php if ($page === 'home'): ?>
        <!-- HOME PAGE CONTENT -->
        
        <!-- Hero Section -->
        <section class="hero-container">
            <div class="hero-content">
                <div class="ice-cream-decoration left">
                    <img src="images/ice-cream-deco-left.png" alt="Ice cream decoration">
                </div>
                <div class="hero-text animate-fade-in">
                    <h1>REY'S ICE CREAM SHOP</h1>
                    <p>Lorem ipsum dolor sit amet</p>
                    <button class="cta-button" onclick="window.location.href='menu.php?page=menu'">Shop Now</button>
                </div>
                <div class="ice-cream-decoration right">
                    <img src="images/ice-cream-deco-right.png" alt="Ice cream decoration">
                </div>
            </div>
            <div class="hero-mountains">
                <img src="images/ice-cream-mountains.png" alt="Ice cream mountains" class="mountains-img">
                <div class="clouds-container">
                    <img src="images/cloud.png" alt="Cloud" class="cloud cloud-1">
                    <img src="images/cloud.png" alt="Cloud" class="cloud cloud-2">
                    <img src="images/cloud.png" alt="Cloud" class="cloud cloud-3">
                </div>
            </div>
        </section>

        <!-- Component Section -->
        <section class="component-section">
            <div class="container">
                <div class="component-badge">
                    <span>✦ Component</span>
                </div>
            </div>
        </section>

        <!-- About Us Section -->
        <section class="about-section" id="about">
            <div class="container">
                <div class="about-content">
                    <div class="about-text">
                        <h2>ABOUT US</h2>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo. Sed non mauris vitae erat consequat auctor eu in elit. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos.</p>
                    </div>
                    <div class="about-image">
                        <img src="images/ice-cream-shop.png" alt="Ice cream shop" class="shop-image">
                    </div>
                </div>
            </div>
        </section>

        <!-- Best Sellers Section -->
        <section class="bestsellers-section">
            <div class="container">
                <div class="bestsellers-badge">
                    <span>Best sellers!</span>
                </div>
                
                <div class="bestsellers-islands">
                    <?php foreach ($bestsellers as $index => $product): ?>
                        <div class="flavor-island island-<?php echo $index + 1; ?>">
                            <div class="island-content">
                                <img src="images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="flavor-image">
                                <p class="flavor-name"><?php echo $product['name']; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Contact Us Section -->
        <section class="contact-section" id="contact">
            <div class="container">
                <h2>Contact Us</h2>
                <div class="contact-content">
                    <form class="contact-form">
                        <input type="email" placeholder="Enter your email address" class="email-input" required>
                        <button type="submit" class="submit-button">Submit</button>
                    </form>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-pinterest-p"></i></a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="site-footer">
            <div class="footer-mountains">
                <img src="images/footer-mountains.png" alt="Footer mountains" class="footer-mountains-img">
            </div>
        </footer>
        
        <?php else: ?>
        <!-- MENU PAGE CONTENT -->
        
        <!-- Hero Section -->
        <div class="hero-section">
            <div class="container">
                <div class="hero-content animate-fade-in">
                    <h1 class="hero-title">Our Menu</h1>
                    <p class="hero-subtitle">Explore our delicious collection of handcrafted treats</p>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="container main-content">
            <!-- Filter Section -->
            <div class="filter-section animate-fade-in">
                <div class="filter-container">
                    <span class="filter-label">Popular Flavors</span>
                    <div class="filter-buttons">
                        <?php foreach ($categories as $category): ?>
                            <button class="filter-button <?php echo $category === 'All Categories' ? 'active' : ''; ?>" 
                                    data-category="<?php echo $category; ?>">
                                <?php echo $category; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="products-grid">
                <?php foreach ($products as $index => $product): ?>
                    <div class="product-card animate-fade-in" style="animation-delay: <?php echo 0.1 * $index; ?>s">
                        <div class="product-image-container">
                            <img src="images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="product-image">
                            <?php if (isset($product['badge'])): ?>
                                <span class="product-badge"><?php echo $product['badge']; ?></span>
                            <?php endif; ?>
                            <button class="favorite-button">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                        <div class="product-details">
                            <div class="product-header">
                                <h3 class="product-title"><?php echo $product['name']; ?></h3>
                                <div class="product-rating">
                                    <span class="star">★</span> <?php echo $product['rating']; ?>
                                </div>
                            </div>
                            <p class="product-description"><?php echo $product['description']; ?></p>
                            <div class="product-footer">
                                <span class="product-price">₱<?php echo $product['price']; ?></span>
                                <button class="add-to-cart-button">Add to cart</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>

        <!-- Footer Decoration -->
        <div class="footer-decoration">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" class="wave-svg">
                <path fill="#fbcfe8" fill-opacity="1" d="M0,224L48,213.3C96,203,192,181,288,181.3C384,181,480,203,576,202.7C672,203,768,181,864,181.3C960,181,1056,203,1152,208C1248,213,1344,203,1392,197.3L1440,192L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
            <div class="footer-image">
                <img src="images/footer-decoration.png" alt="Decorative footer">
            </div>
        </div>
        
        <?php endif; ?>
    </div>

    <script src="menu.js"></script>
</body>
</html>
