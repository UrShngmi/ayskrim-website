<?php
// API endpoint: logout
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';

header('Content-Type: application/json');
startSession();
csrfProtect();

logoutUser();

// AJAX: Return JSON, else redirect to homepage
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    echo json_encode(['success' => true]);
    exit;
}
header('Location: ' . BASE_URL . '/landingPage/home/home.php');
exit;
