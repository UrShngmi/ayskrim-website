<?php
// PDO connection script for Ayskrim E-Commerce
// Usage: require_once __DIR__ . '/config.php'; require_once __DIR__ . '/db.php';
// Call DB::getConnection() to get a PDO instance

require_once __DIR__ . '/config.php';

class DB
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            try {
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // In production, log error and show generic message
                exit('Database connection failed.');
            }
        }
        return self::$pdo;
    }
}
