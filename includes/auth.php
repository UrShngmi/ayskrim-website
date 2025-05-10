<?php
// Session handling and role validation for Ayskrim E-Commerce
// Usage: require_once __DIR__ . '/config.php'; require_once __DIR__ . '/db.php'; require_once __DIR__ . '/auth.php';

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// Start session with secure settings
function startSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name('AyskrimSession'); // Fixed: using a simple string instead of potentially problematic APP_NAME
        session_start([
            'cookie_httponly' => true,
            'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'cookie_lifetime' => SESSION_TIMEOUT,
            'use_strict_mode' => true,
        ]);
    }
}

// Log in a user by ID, store minimal info in session
function loginUser(int $userId): void
{
    startSession();
    $_SESSION['user_id'] = $userId;
    $_SESSION['last_activity'] = time();
}

// Log out current user
function logoutUser(): void
{
    startSession();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

// Check if user is logged in
function isLoggedIn(): bool
{
    startSession();
    return isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id']);
}

// Get current user as array (fetch from DB)
function getCurrentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }
    $pdo = DB::getConnection();
    try {
        // Try the full query with active/deleted flags
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND is_active = 1 AND is_deleted = 0 LIMIT 1');
    } catch (PDOException $e) {
        // Fallback if columns don't exist - just get by ID
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    }
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

// Require user to be logged in, redirect if not
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/landingPage/home/home.php');
        exit;
    }
}

// Require user to have a specific role
function requireRole(string $role): void
{
    $user = getCurrentUser();
    if (!$user || $user['role'] !== $role) {
        header('HTTP/1.1 403 Forbidden');
        exit('Access denied.');
    }
}

function getCurrentUserId() {
    if (!isLoggedIn()) {
        return null;
    }
    return $_SESSION['user_id'] ?? null;
}
