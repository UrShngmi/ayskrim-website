<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/middleware.php';

// Set variables for a guest user
$isLoggedIn = isLoggedIn();
$customer = null;
$page = 'booking';

// If user is logged in, redirect to the customer booking page
if ($isLoggedIn) {
    header('Location: /ayskrimWebsite/customerPage/bookings/bookings.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Book Ice Cream Catering for Your Event - Sweet Scoops">
    <meta name="keywords" content="ice cream, catering, events, booking, sweet scoops">
    <title>Book an Event | Ayskrim</title>
    <link rel="icon" href="/ayskrimWebsite/assets/images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/ayskrimWebsite/shared/navbar/navbar.css">
    <link rel="stylesheet" href="/ayskrimWebsite/shared/header/header.css">
    <link rel="stylesheet" href="/ayskrimWebsite/shared/footer/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.lordicon.com/lordicon.js"></script>
    <meta name="theme-color" content="#ec4899">
    <style>
        .booking-container {
            padding: 2rem;
            margin-top: 120px;
            margin-bottom: 3rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .booking-form-container {
            background-color: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .form-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--brown-900);
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .form-subtitle {
            text-align: center;
            color: var(--muted-foreground);
            margin-bottom: 2rem;
        }
        
        .login-prompt {
            background-color: var(--pink-50);
            border: 1px solid var(--pink-200);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .auth-buttons {
            display: flex;
            align-items: center;
            margin-left: auto;
            margin-right: 5px;
            padding-right: 0;
            position: relative;
            right: 0;
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
</head>
<body>
    <div class="min-h-screen">
        <?php include __DIR__ . '/../shared/header/header.php'; ?>
        <?php include __DIR__ . '/../shared/navbar/navbar.php'; ?>
        
        <div class="booking-container">
            <div class="booking-form-container">
                <h1 class="form-title">Book Ice Cream Catering for Your Event</h1>
                <p class="form-subtitle">Fill out the form below to request a booking for your special event</p>
                
                <div class="login-prompt">
                    <div>
                        <p><strong>Already have an account?</strong> Log in for faster booking and to manage your events.</p>
                    </div>
                    <a href="/ayskrimWebsite/landingPage/login.php" class="btn btn-primary btn-sm">Sign In</a>
                </div>
                
                <form action="/ayskrimWebsite/api/booking/create_guest_booking.php" method="POST" class="booking-form">
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
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-input" placeholder="+63 912 345 6789" required>
                        </div>
                        <div class="form-group">
                            <label for="event_date" class="form-label">Event Date</label>
                            <input type="date" id="event_date" name="event_date" class="form-input" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="event_type" class="form-label">Event Type</label>
                            <select id="event_type" name="event_type" class="form-select" required>
                                <option value="">Select event type</option>
                                <option value="birthday">Birthday Party</option>
                                <option value="wedding">Wedding</option>
                                <option value="corporate">Corporate Event</option>
                                <option value="school">School Event</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="guests" class="form-label">Number of Guests</label>
                            <input type="number" id="guests" name="guests" min="10" class="form-input" placeholder="50" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="package" class="form-label">Preferred Package</label>
                        <select id="package" name="package" class="form-select" required>
                            <option value="">Select a package</option>
                            <option value="basic">Basic Package (₱3,000)</option>
                            <option value="premium">Premium Package (₱5,000)</option>
                            <option value="custom">Custom Package</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message" class="form-label">Additional Information</label>
                        <textarea id="message" name="message" class="form-textarea" rows="4" placeholder="Tell us more about your event, special requests, or questions..."></textarea>
                    </div>
                    
                    <div class="form-group" style="margin-top: 1.5rem;">
                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem;">Submit Booking Request</button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php include __DIR__ . '/../shared/footer/footer.php'; ?>
    </div>
    
    <script src="/ayskrimWebsite/shared/navbar/navbar.js"></script>
    <script>
        // Add date validation (can't book for dates in the past)
        const eventDateInput = document.getElementById('event_date');
        if (eventDateInput) {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            const formattedToday = `${yyyy}-${mm}-${dd}`;
            
            eventDateInput.setAttribute('min', formattedToday);
            
            // Set default date to 7 days from now
            const nextWeek = new Date();
            nextWeek.setDate(today.getDate() + 7);
            const nextWeekYYYY = nextWeek.getFullYear();
            const nextWeekMM = String(nextWeek.getMonth() + 1).padStart(2, '0');
            const nextWeekDD = String(nextWeek.getDate()).padStart(2, '0');
            const formattedNextWeek = `${nextWeekYYYY}-${nextWeekMM}-${nextWeekDD}`;
            
            eventDateInput.value = formattedNextWeek;
        }
    </script>
</body>
</html> 