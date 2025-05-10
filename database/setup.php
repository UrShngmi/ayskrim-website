<?php
// Database setup script for Ayskrim Ice Cream Website
// This will create necessary tables and seed data

// Include database connection
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

// Function to log messages
function setup_log($message) {
    echo $message . "<br>";
    flush();
    ob_flush();
}

setup_log("<h1>Ayskrim Database Setup</h1>");
setup_log("<p>Starting database setup process...</p>");

try {
    // Get PDO connection
    $pdo = DB::getConnection();
    setup_log("Connected to database successfully.");
    
    // Create users table if not exists
    setup_log("<h2>Setting up users table...</h2>");
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `full_name` varchar(100) NOT NULL,
        `username` varchar(50) NOT NULL,
        `email` varchar(100) NOT NULL,
        `password` varchar(255) NOT NULL,
        `phone` varchar(20) DEFAULT NULL,
        `address` text DEFAULT NULL,
        `role` enum('admin','customer') NOT NULL DEFAULT 'customer',
        `profile_picture` varchar(255) DEFAULT 'default.png',
        `verification_status` enum('Unverified','Verified') DEFAULT 'Unverified',
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `email` (`email`),
        UNIQUE KEY `username` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    setup_log("Users table created or already exists.");

    // Check if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    
    // Seed admin user if no users exist
    if ($userCount == 0) {
        setup_log("<h2>Seeding admin user...</h2>");
        
        // Create admin with plaintext password for demo
        $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password, role, verification_status) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'Admin User',
            'admin',
            'admin@ayskrim.com',
            'admin123', // Using plaintext for demo only
            'admin',
            'Verified'
        ]);
        
        setup_log("Admin user created with credentials:");
        setup_log("- Username: admin");
        setup_log("- Email: admin@ayskrim.com");
        setup_log("- Password: adminpwseed");
    } else {
        setup_log("Users already exist in the database. Skipping seed data.");
    }
    
    setup_log("<h2>Database setup complete!</h2>");
    setup_log("<p>You can now <a href='../landingPage/home/home.php'>login to the system</a>.</p>");
    
} catch (PDOException $e) {
    setup_log("<div style='color: red; font-weight: bold;'>ERROR: " . $e->getMessage() . "</div>");
}
?>
