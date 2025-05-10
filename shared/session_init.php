<?php
/**
 * Session Initializer for Customer Pages
 * 
 * This file should be included at the top of all customer pages to:
 * 1. Check if the user is logged in and redirect to login if not
 * 2. Get current user data from the database
 * 3. Set the $customer variable for navbar display
 * 4. Update the last activity timestamp
 */

// Include essential files for authentication and session management
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/middleware.php';

// Require user to be logged in, will redirect to login page if not
requireLogin();

// Get current user data
$user = getCurrentUser();

// Update the last activity timestamp
$_SESSION['last_activity'] = time();

// Set customer variable for navbar
$customer = [
    'id' => $user['id'] ?? 0,
    'profile_picture' => $user['profile_picture'] ?? '',
    'full_name' => $user['full_name'] ?? 'User',
    'username' => $user['username'] ?? '',
    'email' => $user['email'] ?? ''
];

// If CSRF token doesn't exist, generate it
if (!isset($_SESSION[CSRF_TOKEN_KEY])) {
    $_SESSION[CSRF_TOKEN_KEY] = bin2hex(random_bytes(32));
} 