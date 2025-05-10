<?php
// API endpoint: checkSession
// This file checks if the user is still logged in and the session is valid

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

// Set JSON response header
header('Content-Type: application/json');

// Start the session
startSession();

// Session timeout check - if the last activity was more than SESSION_TIMEOUT ago, log out
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    logoutUser();
    echo json_encode(['authenticated' => false, 'reason' => 'session_timeout']);
    exit;
}

// Update last activity time
if (isLoggedIn()) {
    $_SESSION['last_activity'] = time();
}

// Check if user is logged in and get user data
$isAuthenticated = isLoggedIn();
$userData = null;

if ($isAuthenticated) {
    // Get basic user data (without sensitive information)
    $user = getCurrentUser();
    
    if ($user) {
        $userData = [
            'id' => $user['id'],
            'username' => $user['username'] ?? '',
            'full_name' => $user['full_name'] ?? '',
            'email' => $user['email'] ?? '',
            'role' => $user['role'] ?? ''
        ];
    } else {
        // User ID in session but not found in database - invalid session
        $isAuthenticated = false;
        logoutUser();
    }
}

echo json_encode([
    'authenticated' => $isAuthenticated,
    'user' => $userData
]);
exit; 