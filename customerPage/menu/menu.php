<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/db.php';

startSession();
$isLoggedIn = isLoggedIn();

// Generate or retrieve guest token
if (!$isLoggedIn && !isset($_COOKIE['guest_token'])) {
    $guestToken = bin2hex(random_bytes(16));
    setcookie('guest_token', $guestToken, time() + (48 * 3600), '/');
}

// Set appropriate variables based on login status
if ($isLoggedIn) {
    $customer = getCurrentUser();
    $isLandingPage = false;
} else {
    $customer = null;
    $isLandingPage = true;
}

// Set page to menu for navbar highlighting
$page = 'menu';

// Fetch categories from the database
$pdo = DB::getConnection();
$categories = ["All Categories"];
$categoryStmt = $pdo->query("SELECT name FROM categories WHERE is_active = 1 AND is_deleted = 0 ORDER BY name");
while ($row = $categoryStmt->fetch()) {
    $categories[] = $row['name'];
}

// Fetch products from the database
$productStmt = $pdo->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_deleted = 0 AND p.is_active = 1 ORDER BY p.name");
$products = $productStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Ayskrim Ice Cream Shop - Delicious handcrafted ice cream treats">
    <meta name="keywords" content="ice cream, dessert, sweet treats, ayskrim, handcrafted">
    <title>Ayskrim - Ice Cream Menu</title>
    <link rel="icon" href="/ayskrimWebsite/assets/images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/ayskrimWebsite/customerPage/menu/menu.css">
    <link rel="stylesheet" href="/ayskrimWebsite/shared/navbar/navbar.css">
    <link rel="stylesheet" href="/ayskrimWebsite/shared/footer/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.lordicon.com/lordicon.js"></script>
    <meta name="theme-color" content="#ec4899">
</head>
<body>
    <div class="min-h-screen">
        <?php include __DIR__ . '/../../shared/header/header.php'; ?>
        <?php include __DIR__ . '/../../shared/navbar/navbar.php'; ?>

        <!-- MENU PAGE CONTENT -->
        <div class="hero-section">
            <div class="container">
                <div class="hero-content animate-fade-in">
                    <h1 class="hero-title">Our Menu</h1>
                    <p class="hero-subtitle">Explore our delicious collection of handcrafted ice cream treats</p>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="container main-content">
            <!-- Filter Section -->
            <div class="filter-section animate-fade-in">
                <div class="filter-container">
                    <span class="filter-label">Our Flavors</span>
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
                    <div class="product-card animate-fade-in hover-ready" data-category="<?php echo $product['category_name'] ?? ''; ?>" data-product-id="<?php echo $product['id']; ?>" style="animation-delay: <?php echo 0.1 * $index; ?>s">
                        <div class="product-image-container">
                            <img src="/ayskrimWebsite/assets/images/<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" class="product-image">
                            <button class="favorite-button">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                        <div class="product-details">
                            <h3 class="product-title"><?php echo $product['name']; ?></h3>
                            <p class="product-description"><?php echo $product['description']; ?></p>
                            <div class="product-footer">
                                <div class="product-price">₱<?php echo $product['price']; ?></div>
                                <button class="add-to-cart-button" data-product-id="<?php echo $product['id']; ?>">
                                    <span>Add to Cart</span>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>

        <?php include __DIR__ . '/../../shared/footer/footer.php'; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/ayskrimWebsite/customerPage/menu/menu.js"></script>
    <script src="/ayskrimWebsite/shared/navbar/navbar.js"></script>
    <script src="/ayskrimWebsite/shared/scripts/cart-transfer.js"></script>
    <script>
        window.isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;
        window.guestToken = <?php echo json_encode($_COOKIE['guest_token'] ?? ''); ?>;

        // Add to cart handler
        $(document).on('click', '.add-to-cart-button', function() {
            const productId = parseInt($(this).data('product-id'));
            if (!productId) {
                showNotification('Invalid product', 'error');
                return;
            }

            if (window.isLoggedIn) {
                // Create the data object
                const data = {
                    product_id: productId,
                    quantity: 1
                };

                // Log the data being sent (for debugging)
                console.log('Sending data:', data);

                $.ajax({
                    url: '/ayskrimWebsite/api/orders/addToCart.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    processData: false,
                    success: function(response) {
                        console.log('Response:', response);
                        if (response.success) {
                            // Update cart count immediately
                            window.updateCartCount();
                            showNotification('Product added to cart!', 'success');
                        } else {
                            showNotification(response.error || 'Failed to add product', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', {xhr, status, error});
                        let errorMessage = 'Failed to add product';
                        try {
                            const response = xhr.responseJSON;
                            if (response && response.error) {
                                errorMessage = response.error;
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                        }
                        showNotification(errorMessage, 'error');
                    }
                });
            } else {
                // Handle guest cart
                let cart = JSON.parse(sessionStorage.getItem('guestCart') || '[]');
                let item = cart.find(i => i.product_id === productId);
                if (item) {
                    item.quantity += 1;
                } else {
                    cart.push({ product_id: productId, quantity: 1 });
                }
                sessionStorage.setItem('guestCart', JSON.stringify(cart));
                
                // Update cart count immediately
                window.updateCartCount();
                
                const data = {
                    token: window.guestToken,
                    cart: cart
                };

                // Log the data being sent (for debugging)
                console.log('Sending guest cart data:', data);

                $.ajax({
                    url: '/ayskrimWebsite/api/orders/updateGuestCart.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    processData: false,
                    success: function(response) {
                        console.log('Guest cart response:', response);
                        if (response.success) {
                            showNotification('Product added to cart!', 'success');
                        } else {
                            showNotification(response.error || 'Failed to sync cart', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Guest cart error:', {xhr, status, error});
                        let errorMessage = 'Failed to sync cart';
                        try {
                            const response = xhr.responseJSON;
                            if (response && response.error) {
                                errorMessage = response.error;
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                        }
                        showNotification(errorMessage, 'error');
                    }
                });
            }
        });

        // Initial cart count load
        window.updateCartCount();

        // Show notification function
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <span>${message}</span>
                    <button class="notification-close"><i class="fas fa-times"></i></button>
                </div>
            `;

            document.body.appendChild(notification);
            setTimeout(() => notification.classList.add('active'), 10);

            const closeButton = notification.querySelector('.notification-close');
            closeButton.addEventListener('click', () => {
                notification.classList.remove('active');
                setTimeout(() => notification.remove(), 300);
            });

            setTimeout(() => {
                notification.classList.remove('active');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>