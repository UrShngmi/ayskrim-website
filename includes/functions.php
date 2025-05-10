<?php
// Utility functions for Ayskrim E-Commerce
// Usage: require_once __DIR__ . '/functions.php';

/**
 * Sanitize user input for safe output (HTML context)
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Hash a password using bcrypt (configurable cost)
 */
function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
}

/**
 * Verify a password against a hash
 */
function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

/**
 * Set a flash message (type: success, error, info)
 */
function setFlash(string $type, string $message): void
{
    startSession();
    $_SESSION['flash'][$type][] = $message;
}

/**
 * Get and clear flash messages
 */
function getFlash(string $type): array
{
    startSession();
    $messages = $_SESSION['flash'][$type] ?? [];
    unset($_SESSION['flash'][$type]);
    return $messages;
}
