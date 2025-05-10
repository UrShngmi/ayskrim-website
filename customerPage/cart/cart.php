<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

startSession();
$isLoggedIn = isLoggedIn();

// Generate or retrieve guest token
if (!$isLoggedIn && !isset($_COOKIE['guest_token'])) {
    $guestToken = bin2hex(random_bytes(16));
    setcookie('guest_token', $guestToken, time() + (48 * 3600), '/');
} else {
    $guestToken = $_COOKIE['guest_token'] ?? '';
}

// Fetch cart items
$cartItems = [];
$subtotal = 0;
$totalItems = 0;
$pdo = DB::getConnection();

if ($isLoggedIn) {
    $userId = getCurrentUserId();
    $stmt = $pdo->prepare('
        SELECT ci.product_id as id, ci.quantity, p.name, p.price, p.image_url
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.user_id = ? AND p.is_deleted = 0 AND p.is_active = 1
    ');
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    if ($guestToken) {
        $stmt = $pdo->prepare('SELECT cart_data FROM guest_carts WHERE token = ?');
        $stmt->execute([$guestToken]);
        $cartData = $stmt->fetch(PDO::FETCH_ASSOC);
        $cart = $cartData ? json_decode($cartData['cart_data'], true) : [];
        foreach ($cart as $item) {
            $stmt = $pdo->prepare('
                SELECT id, name, price, image_url
                FROM products 
                WHERE id = ? AND is_deleted = 0 AND is_active = 1
            ');
            $stmt->execute([$item['product_id']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($product) {
                $cartItems[] = [
                    'id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'image_url' => $product['image_url']
                ];
            }
        }
    }
}

// Calculate totals
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $totalItems += $item['quantity'];
}

$total = $subtotal;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="cart.css">
</head>
<body>
    <div class="cart-container">
        <div class="container">
            <h1 class="page-title">
                <i class="fas fa-shopping-bag"></i>
                Your Cart
            </h1>
            <div class="cart-grid">
                <!-- Cart Items Section -->
                <div class="cart-items-section">
                    <h2 class="section-title">
                        Cart Items <span class="item-count"><?php echo $isLoggedIn ? $totalItems : ''; ?></span>
                    </h2>
                    <div class="cart-items-container">
                        <div class="cart-items">
                            <?php if ($isLoggedIn): ?>
                                <?php foreach ($cartItems as $product): ?>
                                    <div class="cart-item" data-id="<?php echo $product['id']; ?>">
                                        <div class="product-image">
                                            <img src="/ayskrimWebsite/assets/images/<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                        </div>
                                        <div class="product-info">
                                            <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                            <p class="product-price">₱<?php echo number_format($product['price'], 2); ?></p>
                                        </div>
                                        <div class="quantity-controls">
                                            <button class="quantity-btn decrease" data-id="<?php echo $product['id']; ?>">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <span class="quantity"><?php echo $product['quantity']; ?></span>
                                            <button class="quantity-btn increase" data-id="<?php echo $product['id']; ?>">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                        <div class="product-total">
                                            <p class="total-price price-animate">₱<?php echo number_format($product['price'] * $product['quantity'], 2); ?></p>
                                            <button class="remove-btn" data-id="<?php echo $product['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php foreach ($cartItems as $product): ?>
                                    <div class="cart-item" data-id="<?php echo $product['id']; ?>">
                                        <div class="product-image">
                                            <img src="/ayskrimWebsite/assets/images/<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                        </div>
                                        <div class="product-info">
                                            <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                            <p class="product-price">₱<?php echo number_format($product['price'], 2); ?></p>
                                        </div>
                                        <div class="quantity-controls">
                                            <button class="quantity-btn decrease" data-id="<?php echo $product['id']; ?>">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <span class="quantity"><?php echo $product['quantity']; ?></span>
                                            <button class="quantity-btn increase" data-id="<?php echo $product['id']; ?>">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                        <div class="product-total">
                                            <p class="total-price price-animate">₱<?php echo number_format($product['price'] * $product['quantity'], 2); ?></p>
                                            <button class="remove-btn" data-id="<?php echo $product['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($cartItems)): ?>
                            <div class="cart-actions-separator">
                                <div class="cart-actions">
                                    <button class="btn outline-btn" onclick="window.history.back()">
                                        Continue Shopping
                                    </button>
                                    <button class="btn ghost-btn" id="clear-cart">
                                        Clear Cart
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!$isLoggedIn && !empty($cartItems)): ?>
                        <p id="guest-cart-warning" style="color: orange;">
                            Your cart will be saved for 48 hours unless you sign in.
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Order Summary Section -->
                <div class="order-summary-section">
                    <h2 class="section-title">Order Summary</h2>
                    <div class="summary-items">
                        <div class="summary-item">
                            <span class="summary-label">Subtotal</span>
                            <span class="summary-value price-animate">₱<?php echo number_format($subtotal, 2); ?></span>
                        </div>
         
                        <div class="summary-total">
                            <span class="total-label">Total</span>
                            <span class="total-value price-animate">₱<?php echo number_format($total, 2); ?></span>
                        </div>

                    </div>
                    <button class="btn primary-btn checkout-btn" <?php echo empty($cartItems) ? 'disabled' : ''; ?>>
                        Proceed to Checkout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="cart.js"></script>
    <script>
        // Initialize guest cart functionality
        window.isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;
        window.guestToken = <?php echo json_encode($guestToken); ?>;

        // Update cart count on page load
        if (window.updateCartCount) {
            window.updateCartCount();
        }

        // Update cart count when storage changes
        window.addEventListener('storage', (e) => {
            if (e.key === 'guestCart' && window.updateCartCount) {
                window.updateCartCount();
            }
        });

        // Update cart count when page is shown (for browser navigation)
        window.addEventListener('pageshow', (event) => {
            if (event.persisted && window.updateCartCount) {
                window.updateCartCount();
            }
        });
    </script>
</body>
</html>