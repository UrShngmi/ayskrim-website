<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';

// Force log out any logged-in users who try to access the home page
if (isLoggedIn()) {
    // Log the user out
    logoutUser();
}

// Set variables for a guest user only
$isLoggedIn = false;
$customer = null;
$isLandingPage = true;
$page = 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sweet Scoops Catering - Premium Ice Cream Catering">
    <meta name="keywords" content="ice cream, catering, events, premium, sweet scoops">
    <title>Sweet Scoops Catering - Home</title>
    <link rel="icon" href="/ayskrimWebsite/assets/images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="/ayskrimWebsite/shared/navbar/navbar.css">
    <link rel="stylesheet" href="/ayskrimWebsite/shared/header/header.css">
    <link rel="stylesheet" href="/ayskrimWebsite/shared/footer/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Fredoka:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.lordicon.com/lordicon.js"></script>
    <meta name="theme-color" content="#ec4899">
</head>
<body>
    <div class="min-h-screen">
        <?php include __DIR__ . '/../../shared/header/header.php'; ?>
        <?php include __DIR__ . '/../../shared/navbar/navbar.php'; ?>
        
<!-- Hero Section -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="container">
        <div class="hero-grid">
            <div class="hero-content">
                <span class="hero-badge">Premium Ice Cream Catering</span>
                <h1 class="hero-title">
                    Make Your Event
                    <span class="gradient-text">Sweeter</span>
                    with Our Catering
                </h1>
                <p class="hero-text">
                    Delight your guests with premium ice cream catering for birthdays, weddings, corporate events, and more.
                </p>
                <div class="hero-buttons">
                    <a href="booking.php" class="btn btn-primary">Book Now</a>
                    <a href="menu.php" class="btn btn-outline">View Packages</a>
                </div>
            </div>
            <div class="hero-image-container">
                <div class="hero-image-bg"></div>
                <img src="assets/images/ice-cream-cart.png" alt="Ice cream cart for events" class="hero-image animate-float">
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="services">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title wavy-border">Our Catering Services</h2>
            <p class="section-description">
                We offer premium ice cream catering services for all types of events
            </p>
        </div>

        <div class="services-grid">
            <div class="service-card glass-card">
                <div class="service-icon-container">
                    <i class="fa-solid fa-party-horn service-icon"></i>
                </div>
                <h3 class="service-title">Private Parties</h3>
                <p class="service-description">
                    Perfect for birthdays, anniversaries, and family gatherings. Our ice cream cart will be the highlight of your celebration.
                </p>
            </div>

            <div class="service-card glass-card">
                <div class="service-icon-container">
                    <i class="fa-solid fa-users service-icon"></i>
                </div>
                <h3 class="service-title">Corporate Events</h3>
                <p class="service-description">
                    Impress your clients and reward your team with our professional ice cream catering for corporate events and office parties.
                </p>
            </div>

            <div class="service-card glass-card">
                <div class="service-icon-container">
                    <i class="fa-solid fa-calendar service-icon"></i>
                </div>
                <h3 class="service-title">Weddings</h3>
                <p class="service-description">
                    Add a sweet touch to your special day with our elegant ice cream cart service, customized to match your wedding theme.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Packages -->
<section class="packages gradient-bg">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title wavy-border">Featured Packages</h2>
            <p class="section-description">
                Choose from our popular catering packages or contact us for custom options
            </p>
        </div>

        <div class="packages-grid">
            <!-- Package 1 -->
            <div class="package-card glass-card">
                <div class="package-header pink-gradient"></div>
                <div class="package-content">
                    <h3 class="package-title">Basic Package</h3>
                    <p class="package-subtitle">Perfect for small to medium events</p>
                    <div class="package-price">₱3,000</div>
                    <ul class="package-features">
                        <li><i class="fa-solid fa-check"></i> Max of 2 Flavors</li>
                        <li><i class="fa-solid fa-check"></i> 100 Pax</li>
                        <li><i class="fa-solid fa-check"></i> Free 100 Cones</li>
                        <li><i class="fa-solid fa-check"></i> Includes Staff/Attendant</li>
                    </ul>
                </div>
                <div class="package-footer">
                    <a href="menu.php" class="btn btn-primary btn-full">View Details</a>
                </div>
            </div>

            <!-- Package 2 -->
            <div class="package-card glass-card">
                <div class="package-header brown-gradient"></div>
                <div class="package-content">
                    <div class="package-title-container">
                        <h3 class="package-title">Premium Package</h3>
                        <span class="package-badge">Popular</span>
                    </div>
                    <p class="package-subtitle">Ideal for medium to large events</p>
                    <div class="package-price">₱5,000</div>
                    <ul class="package-features">
                        <li><i class="fa-solid fa-check"></i> Max of 4 Flavors</li>
                        <li><i class="fa-solid fa-check"></i> 150–300 Pax</li>
                        <li><i class="fa-solid fa-check"></i> Free 200 Cones</li>
                        <li><i class="fa-solid fa-check"></i> Free Delivery</li>
                        <li><i class="fa-solid fa-check"></i> Includes Staff/Attendant</li>
                    </ul>
                </div>
                <div class="package-footer">
                    <a href="menu.php" class="btn btn-primary btn-full">View Details</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Menu Section -->
<section class="flavors">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title wavy-border">Our Delicious Flavors</h2>
            <p class="section-description">
                Choose from our wide selection of handcrafted ice cream flavors for your event
            </p>
        </div>

        <div class="flavors-grid">
            <?php
            // Sample flavor data
            $flavors = [
                ['name' => 'Strawberry', 'bestseller' => true, 'image' => 'strawberry.png'],
                ['name' => 'Chocolate', 'bestseller' => true, 'image' => 'chocolate.png'],
                ['name' => 'Vanilla Bean', 'bestseller' => false, 'image' => 'vanilla.png'],
                ['name' => 'Mango Graham', 'bestseller' => true, 'image' => 'mango-graham.png'],
                ['name' => 'Cookies & Cream', 'bestseller' => true, 'image' => 'cookies-cream.png'],
                ['name' => 'Ube', 'bestseller' => true, 'image' => 'ube.png'],
                ['name' => 'Buko Pandan', 'bestseller' => false, 'image' => 'buko-pandan.png'],
                ['name' => 'Avocado', 'new' => true, 'image' => 'avocado.png'],
            ];

            foreach ($flavors as $flavor) {
                echo '<div class="flavor-card glass-card">';
                echo '<div class="flavor-image-container">';
                echo "<img src=\"assets/images/flavors/{$flavor['image']}\" alt=\"{$flavor['name']} ice cream\" class=\"flavor-image\">";

                if (isset($flavor['bestseller']) && $flavor['bestseller']) {
                    echo '<span class="flavor-badge bestseller">Best Seller</span>';
                }

                if (isset($flavor['new']) && $flavor['new']) {
                    echo '<span class="flavor-badge new">New</span>';
                }

                echo '</div>';
                echo '<div class="flavor-content">';
                echo "<h3 class=\"flavor-name\">{$flavor['name']}</h3>";
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>

        <div class="flavors-footer">
            <a href="menu.php" class="btn btn-outline">View All Flavors</a>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="how-it-works">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title wavy-border">How It Works</h2>
            <p class="section-description">
                Booking our ice cream catering service is easy and hassle-free
            </p>
        </div>

        <div class="steps-container">
            <?php
            $steps = [
                [
                    'number' => '1',
                    'title' => 'Choose Your Package',
                    'description' => 'Select from our catering packages or request a custom quote for your event.'
                ],
                [
                    'number' => '2',
                    'title' => 'Pick Your Flavors',
                    'description' => 'Choose from our wide selection of regular and special ice cream flavors.'
                ],
                [
                    'number' => '3',
                    'title' => 'Book Your Date',
                    'description' => 'Secure your event date with a deposit and confirm all the details.'
                ],
                [
                    'number' => '4',
                    'title' => 'Enjoy Your Event',
                    'description' => 'We\'ll arrive on time, set up, and serve delicious ice cream to your guests.'
                ]
            ];

            foreach ($steps as $index => $step) {
                echo '<div class="step-card glass-card">';
                echo "<div class=\"step-number\">{$step['number']}</div>";
                echo "<h3 class=\"step-title\">{$step['title']}</h3>";
                echo "<p class=\"step-description\">{$step['description']}</p>";
                echo '</div>';

                // Add connector line between steps (except after the last one)
                if ($index < count($steps) - 1) {
                    echo '<div class="step-connector"></div>';
                }
            }
            ?>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials gradient-bg">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title wavy-border">What Our Clients Say</h2>
            <p class="section-description">
                Hear from our satisfied customers about their experience with our ice cream catering
            </p>
        </div>

        <div class="testimonials-grid">
            <?php
            $testimonials = [
                [
                    'name' => 'Maria Santos',
                    'role' => 'Birthday Party Host',
                    'quote' => 'Sweet Scoops made my daughter\'s birthday party unforgettable! The ice cream was delicious and the service was impeccable.',
                    'rating' => 5,
                    'image' => 'maria.jpg'
                ],
                [
                    'name' => 'James Wilson',
                    'role' => 'Corporate Event Planner',
                    'quote' => 'Our company event was a hit thanks to Sweet Scoops. The variety of flavors pleased everyone and the setup was professional.',
                    'rating' => 5,
                    'image' => 'james.jpg'
                ],
                [
                    'name' => 'Sophia Chen',
                    'role' => 'Wedding Coordinator',
                    'quote' => 'The ice cream cart was the highlight of the wedding reception! Guests are still talking about it months later.',
                    'rating' => 5,
                    'image' => 'sophia.jpg'
                ]
            ];

            foreach ($testimonials as $testimonial) {
                echo '<div class="testimonial-card glass-card">';
                echo '<div class="testimonial-header">';
                echo '<div class="testimonial-image-container">';
                echo "<img src=\"assets/images/testimonials/{$testimonial['image']}\" alt=\"{$testimonial['name']}\" class=\"testimonial-image\">";
                echo '<div class="testimonial-badge"><i class="fa-solid fa-star"></i></div>';
                echo '</div>';
                echo '<div class="testimonial-rating">';

                for ($i = 0; $i < $testimonial['rating']; $i++) {
                    echo '<i class="fa-solid fa-star"></i>';
                }

                echo '</div>';
                echo '</div>';
                echo "<p class=\"testimonial-quote\">\"{$testimonial['quote']}\"</p>";
                echo "<h3 class=\"testimonial-name\">{$testimonial['name']}</h3>";
                echo "<p class=\"testimonial-role\">{$testimonial['role']}</p>";
                echo '</div>';
            }
            ?>
        </div>
    </div>
</section>

<!-- Gallery -->
<section class="gallery">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title wavy-border">Event Gallery</h2>
            <p class="section-description">
                See our ice cream catering service in action at various events
            </p>
        </div>

        <div class="carousel-container">
            <div class="carousel" id="galleryCarousel">
                <div class="carousel-track">
                    <?php
                    $galleryImages = [
                        ['src' => 'corporate-event.jpg', 'alt' => 'Corporate event ice cream service'],
                        ['src' => 'wedding.jpg', 'alt' => 'Wedding ice cream cart'],
                        ['src' => 'birthday.jpg', 'alt' => 'Birthday party ice cream station'],
                        ['src' => 'school-event.jpg', 'alt' => 'School event ice cream service']
                    ];

                    foreach ($galleryImages as $image) {
                        echo '<div class="carousel-slide">';
                        echo '<div class="gallery-image-container">';
                        echo "<img src=\"assets/images/gallery/{$image['src']}\" alt=\"{$image['alt']}\" class=\"gallery-image\">";
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
                <button class="carousel-button prev" id="prevButton">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button class="carousel-button next" id="nextButton">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Contact/Booking CTA -->
<section class="booking gradient-bg">
    <div class="container">
        <div class="booking-container glass-card">
            <div class="booking-grid">
                <div class="booking-info">
                    <h2 class="booking-title">Ready to Sweeten Your Event?</h2>
                    <p class="booking-description">
                        Contact us today to book your ice cream catering package or request a custom quote for your special event.
                    </p>
                    <div class="contact-info">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <div class="contact-text">
                                <p class="contact-label">Call Us</p>
                                <p class="contact-value">(555) 123-4567</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fa-solid fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-text">
                                <p class="contact-label">Visit Us</p>
                                <p class="contact-value">123 Ice Cream Lane, Sweet City</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="booking-form-container">
                    <h3 class="form-title">Book Your Event</h3>
                    <form class="booking-form" id="bookingForm" action="process-booking.php" method="post">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name" class="form-label">Your Name</label>
                                <input type="text" id="name" name="name" class="form-input" placeholder="John Doe" required>
                            </div>
                            <div class="form-group">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" id="email" name="email" class="form-input" placeholder="john@example.com" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="event-date" class="form-label">Event Date</label>
                            <input type="date" id="event-date" name="event_date" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label for="package" class="form-label">Package</label>
                            <select id="package" name="package" class="form-select" required>
                                <option value="">Select a package</option>
                                <option value="basic">Basic Package (₱3,000)</option>
                                <option value="premium">Premium Package (₱5,000)</option>
                                <option value="custom">Custom Package</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="message" class="form-label">Additional Information</label>
                            <textarea id="message" name="message" class="form-textarea" rows="3" placeholder="Tell us more about your event..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-full">Submit Booking Request</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="features">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title wavy-border">Why Choose Us</h2>
            <p class="section-description">
                What makes our ice cream catering service special
            </p>
        </div>

        <div class="features-grid">
            <?php
            $features = [
                [
                    'icon' => 'fa-ice-cream',
                    'title' => 'Premium Quality',
                    'description' => 'We use only the finest ingredients for our handcrafted ice cream.'
                ],
                [
                    'icon' => 'fa-truck',
                    'title' => 'Reliable Service',
                    'description' => 'We\'re always on time and professional for your peace of mind.'
                ],
                [
                    'icon' => 'fa-users',
                    'title' => 'Experienced Team',
                    'description' => 'Our friendly staff ensures a smooth and enjoyable experience.'
                ],
                [
                    'icon' => 'fa-party-horn',
                    'title' => 'Memorable Experience',
                    'description' => 'We create sweet memories that your guests will talk about.'
                ]
            ];

            foreach ($features as $feature) {
                echo '<div class="feature-item">';
                echo '<div class="feature-icon-container">';
                echo "<i class=\"fa-solid {$feature['icon']}\"></i>";
                echo '</div>';
                echo "<h3 class=\"feature-title\">{$feature['title']}</h3>";
                echo "<p class=\"feature-description\">{$feature['description']}</p>";
                echo '</div>';
            }
            ?>
        </div>
    </div>
</section>
        <!-- Notification container for product actions -->
        <div class="notification" id="notification"></div>

        <?php include __DIR__ . '/../../shared/footer/footer.php'; ?>
    </div>
    <script src="home.js"></script>
    <script src="/ayskrimWebsite/shared/navbar/navbar.js"></script>
    <script src="/ayskrimWebsite/shared/navbar/navbar-lottie.js"></script>
    <script src="/ayskrimWebsite/shared/scripts/cart-transfer.js"></script>
    <script>
        // Initialize guest cart functionality
        window.isLoggedIn = false;
        window.guestToken = <?php echo json_encode($_COOKIE['guest_token'] ?? ''); ?>;

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