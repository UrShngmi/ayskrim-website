<?php
// API endpoint: check admin credentials
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/constants.php';

header('Content-Type: application/json');
startSession();

// Check if user is logged in and is admin
$isAdmin = false;
$message = 'Not logged in';

if (isLoggedIn()) {
    $user = getCurrentUser();
    if ($user && $user['role'] === ROLE_ADMIN) {
        $isAdmin = true;
        $message = 'Logged in as admin: ' . $user['full_name'];
    } else {
        $message = 'Logged in but not as admin';
    }
}

echo json_encode([
    'success' => $isAdmin,
    'message' => $message
]);
