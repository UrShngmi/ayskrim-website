<?php
// String, array, date helpers for Ayskrim E-Commerce
// Usage: require_once __DIR__ . '/helpers.php';

/**
 * Convert a string to a URL-friendly slug
 */
function slugify(string $text): string
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = strtolower($text);
    $text = preg_replace('~[^-a-z0-9]+~', '', $text);
    return $text ?: 'n-a';
}

/**
 * Format a date for display
 */
function formatDate(string $date, string $format = 'M d, Y H:i'): string
{
    return date($format, strtotime($date));
}

/**
 * Flatten a multidimensional array
 */
function array_flatten(array $array): array
{
    $result = [];
    array_walk_recursive($array, function ($a) use (&$result) { $result[] = $a; });
    return $result;
}

/**
 * Check if a string is valid JSON
 */
function isJson(string $string): bool
{
    json_decode($string);
    return (json_last_error() === JSON_ERROR_NONE);
}

/**
 * Generate a random string (for tokens, etc.)
 */
function randomString(int $length = 16): string
{
    return bin2hex(random_bytes($length / 2));
}
