<?php
// Middleware for session, role, permission for Ayskrim E-Commerce
// Usage: require_once __DIR__ . '/middleware.php';

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

/**
 * Require user to be a customer
 */
function requireCustomer(): void
{
    requireLogin();
    $user = getCurrentUser();
    if (!$user || $user['role'] !== 'customer') {
        header('HTTP/1.1 403 Forbidden');
        exit('Customer access required.');
    }
}

// Note: requireAdmin() is already defined in auth.php

/**
 * Require user to be a guest (not logged in)
 */
function requireGuest(): void
{
    startSession();
    if (isLoggedIn()) {
        header('Location: ' . BASE_URL . '/customerPage/profile/profile.php');
        exit;
    }
}

/**
 * CSRF protection middleware
 */
function csrfProtect(): void
{
    startSession();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || !hash_equals($_SESSION[CSRF_TOKEN_KEY] ?? '', $token)) {
            header('HTTP/1.1 419 Authentication Timeout');
            exit('Invalid CSRF token.');
        }
    }
}
