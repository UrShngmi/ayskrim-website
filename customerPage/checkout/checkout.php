<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/constants.php';

// Start session and check authentication
startSession();
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/landingPage/login.php');
    exit;
}

// Get user data
$user = getCurrentUser();

// Initialize session variables for promo
if (!isset($_SESSION['promo_code'])) {
    $_SESSION['promo_code'] = '';
}
if (!isset($_SESSION['is_promo_applied'])) {
    $_SESSION['is_promo_applied'] = false;
}
if (!isset($_SESSION['discount'])) {
    $_SESSION['discount'] = 0;
}

// Fetch cart items
$cartItems = [];
$subtotal = 0;
$totalItems = 0;
$pdo = DB::getConnection();

$userId = getCurrentUserId();
$stmt = $pdo->prepare('
    SELECT ci.product_id as id, ci.quantity, p.name, p.price, p.image_url
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.user_id = ? AND p.is_deleted = 0 AND p.is_active = 1
');
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $totalItems += $item['quantity'];
}

$shipping = 50; // ₱50 delivery fee
$discount = $_SESSION['discount'];
$total = $subtotal + $shipping - $discount;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Ayskrim</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="checkout.css">
</head>
<body>
    <main class="checkout-container">
        <div class="checkout-grid">
            <!-- Left Column: Order Summary -->
            <div class="order-summary">
                <div class="summary-header">
                    <h2>Order Summary</h2>
                    <span class="item-count"><?php echo $totalItems; ?> items</span>
                </div>
                <!-- Modern Promo Code Section moved back above totals -->
                <div class="promo-code modern-promo">
                    <h3 class="subsection-title">Promo Code</h3>
                    <div class="promo-input">
                        <input type="text" id="promo-code" placeholder="Enter code" value="<?php echo htmlspecialchars($_SESSION['promo_code']); ?>">
                        <button class="btn outline-btn" id="apply-promo"><i class="fas fa-gift"></i> Apply</button>
                    </div>
                    <?php if ($_SESSION['is_promo_applied']): ?>
                        <p class="promo-success">
                            <i class="fas fa-gift"></i> Promo code applied successfully!
                        </p>
                    <?php endif; ?>
                </div>
                <!-- Remove DEBUG output and fix cart item display -->
                <div class="cart-items receipt-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item receipt-row" 
                             data-product-id="<?php echo htmlspecialchars($item['id']); ?>"
                             style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #f3f4f6;">
                            <img src="/ayskrimWebsite/assets/images/<?php echo htmlspecialchars($item['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 class="item-thumb" 
                                 style="width:38px;height:38px;border-radius:8px;object-fit:cover;flex-shrink:0;">
                            <div class="item-details" style="flex:1;min-width:0;">
                                <div class="item-name" style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </div>
                                <div class="item-quantity" style="color:#6b7280;font-size:0.95rem;">
                                    x<?php echo $item['quantity']; ?>
                                </div>
                            </div>
                            <div class="item-price" style="color:#ec4899;font-weight:600;min-width:70px;text-align:right;">
                                ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="summary-totals">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span class="subtotal">₱<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <?php if ($_SESSION['is_promo_applied']): ?>
                        <div class="summary-row discount">
                            <span>
                                <i class="fas fa-gift"></i>
                                Discount
                            </span>
                            <span class="discount-value">-₱<?php echo number_format($discount, 2); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="summary-row">
                        <span>Delivery Fee</span>
                        <span class="delivery-fee">₱<?php echo number_format($shipping, 2); ?></span>
                    </div>
                    <div class="summary-row total" style="font-size:1.5rem;font-weight:700;">
                        <span style="font-size:1.25rem;">Total</span>
                        <span class="total-amount" style="color:var(--pink-500);font-size:1.5rem;font-weight:800;">₱<?php echo number_format($subtotal + $shipping - $discount, 2); ?></span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Delivery Information -->
            <div class="delivery-info">
                <div class="info-section">
                    <h2>Delivery Information</h2>
                    <form id="deliveryForm" class="delivery-form">
                        <div class="form-group">
                            <label for="fullName">Full Name</label>
                            <input type="text" id="fullName" name="fullName" required 
                                   value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required 
                                   pattern="[0-9]{11}" placeholder="09XXXXXXXXX"
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            <small class="form-hint">Format: 09XXXXXXXXX</small>
                        </div>

                        <div class="form-group">
                            <label for="address">Delivery Address</label>
                            <div class="address-input-group">
                                <div id="addressDisplay" class="address-display-card">No location selected</div>
                                <button type="button" id="pickLocationBtn" class="location-picker-btn">
                                    <i class="fas fa-map-marker-alt"></i> Pick Location
                                </button>
                            </div>
                            <input type="hidden" id="address" name="address">
                            <input type="hidden" id="latitude" name="latitude">
                            <input type="hidden" id="longitude" name="longitude">
                        </div>

                        <div class="form-group">
                            <label for="instructions">Delivery Instructions (Optional)</label>
                            <textarea id="instructions" name="instructions" rows="2" 
                                      placeholder="Any special instructions for delivery?"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="paymentMethod">Payment Method</label>
                            <div class="payment-options">
                                <label class="payment-option">
                                    <input type="radio" name="paymentMethod" value="cod" checked>
                                    <span class="option-content">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <span>Cash on Delivery</span>
                                    </span>
                                </label>
                                <label class="payment-option">
                                    <input type="radio" name="paymentMethod" value="gcash">
                                    <span class="option-content">
                                        <i class="fas fa-mobile-alt"></i>
                                        <span>GCash</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="checkout-button">
                            <span>Place Order</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Loading Overlay -->
    <div class="loading-overlay">
        <div class="loading-spinner"></div>
        <p>Processing your order...</p>
    </div>

    <!-- Success Modal -->
    <div class="modal" id="successModal">
        <div class="modal-content">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3>Order Placed Successfully!</h3>
            <p>Your order has been received and is being processed.</p>
            <div class="modal-buttons">
                <a href="/ayskrimWebsite/customerPage/orders/orders.php" class="btn-primary">View Orders</a>
                <a href="/ayskrimWebsite/customerPage/menu/menu.php" class="btn-outline">Continue Shopping</a>
            </div>
        </div>
    </div>

    <!-- Location Picker Modal -->
    <div class="modal" id="locationPickerModal">
        <div class="modal-content location-picker-content">
            <div class="modal-header">
                <h3>Pick Your Delivery Location</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="address-search-container">
                    <input type="text" id="addressSearchBox" class="address-search-box" placeholder="Search for an address or place..." autocomplete="off" />
                    <div id="searchSuggestions" class="search-suggestions"></div>
                </div>
                <div id="map" class="enhanced-map"></div>
                <div id="mapLoadingOverlay" class="map-loading-overlay">
                    <div class="location-loading"></div>
                    <p>Getting your current location...</p>
                    <button type="button" id="retryGeolocationBtn" class="btn-secondary" style="display:none;margin-top:1rem;">Retry</button>
                </div>
                <div class="location-status">
                    <p id="locationStatus"></p>
                </div>
                <div class="selected-address-card">
                    <h4>Selected Address:</h4>
                    <p id="selectedAddress">No location selected</p>
                    <div id="coordsDisplay" style="color:#888;font-size:0.95em;margin-top:0.5em;"></div>
                </div>
            </div>
            <div class="modal-footer enhanced-footer">
                <button type="button" class="btn-secondary large-btn" id="useCurrentLocation">
                    <i class="fas fa-location-arrow"></i> Use Current Location
                </button>
                <button type="button" class="btn-primary large-btn" id="confirmLocation" disabled>
                    <i class="fas fa-check-circle"></i> Confirm Location
                </button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="checkout.js"></script>
</body>
</html>
