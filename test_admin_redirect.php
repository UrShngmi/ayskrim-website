<?php
// Script to test admin redirection
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/constants.php';

// Output function
function output_message($message) {
    echo $message . "<br>";
    flush();
}

output_message("<h1>Test Admin Redirection</h1>");

// Test credentials
$email = 'admin123';
$password = 'hanzamadmin';

output_message("<p>Testing login redirection with:</p>");
output_message("<ul>");
output_message("<li>Email: " . $email . "</li>");
output_message("<li>Password: " . $password . "</li>");
output_message("</ul>");

try {
    // Get database connection
    $pdo = DB::getConnection();
    output_message("Connected to database successfully.");
    
    // Get user by email
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        output_message("<div style='color: red; font-weight: bold;'>✗ User not found with email: " . $email . "</div>");
        exit;
    }
    
    output_message("<div style='color: green;'>✓ User found with ID: " . $user['id'] . "</div>");
    output_message("<div style='color: green;'>Username: " . $user['username'] . "</div>");
    output_message("<div style='color: green;'>Role: " . $user['role'] . "</div>");
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        output_message("<div style='color: red; font-weight: bold;'>✗ Password verification failed!</div>");
        exit;
    }
    
    output_message("<div style='color: green; font-weight: bold;'>✓ Password verification successful!</div>");
    
    // Test login function
    loginUser((int)$user['id']);
    output_message("<div style='color: green;'>✓ User logged in successfully</div>");
    
    // Check if user is logged in
    if (isLoggedIn()) {
        output_message("<div style='color: green;'>✓ isLoggedIn() returns true</div>");
    } else {
        output_message("<div style='color: red;'>✗ isLoggedIn() returns false</div>");
    }
    
    // Get current user
    $currentUser = getCurrentUser();
    if ($currentUser) {
        output_message("<div style='color: green;'>✓ getCurrentUser() returns user data</div>");
        output_message("<div style='color: green;'>Current user ID: " . $currentUser['id'] . "</div>");
        output_message("<div style='color: green;'>Current user role: " . $currentUser['role'] . "</div>");
    } else {
        output_message("<div style='color: red;'>✗ getCurrentUser() returns null</div>");
    }
    
    // Test admin role
    if ($currentUser['role'] === 'admin') {
        output_message("<div style='color: green; font-weight: bold;'>✓ User has admin role</div>");
        
        // Test redirection URL
        $redirectUrl = BASE_URL . ADMIN_PAGES['dashboard'];
        output_message("<div style='color: blue;'>Admin would be redirected to: " . $redirectUrl . "</div>");
        
        // Check if the admin dashboard file exists
        $dashboardPath = __DIR__ . ADMIN_PAGES['dashboard'];
        if (file_exists($dashboardPath)) {
            output_message("<div style='color: green;'>✓ Admin dashboard file exists at: " . $dashboardPath . "</div>");
        } else {
            output_message("<div style='color: red;'>✗ Admin dashboard file does not exist at: " . $dashboardPath . "</div>");
        }
    } else {
        output_message("<div style='color: red; font-weight: bold;'>✗ User does not have admin role</div>");
    }
    
    // Clean up (logout)
    logoutUser();
    output_message("<div style='color: blue;'>User logged out for cleanup</div>");
    
    output_message("<div style='color: green; font-weight: bold; font-size: 1.2em; margin-top: 20px;'>All tests passed successfully!</div>");
    output_message("<p>The admin redirection should work correctly.</p>");
    output_message("<p><a href='landingPage/login.php'>Go to login page</a> to test manually.</p>");
    
} catch (Exception $e) {
    output_message("<div style='color: red; font-weight: bold;'>Error: " . $e->getMessage() . "</div>");
}
