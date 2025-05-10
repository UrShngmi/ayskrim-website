<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');
startSession();

echo json_encode([
    'success' => true,
    'isLoggedIn' => isLoggedIn()
]);
?>