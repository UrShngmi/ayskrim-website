<?php
// Script to update admin password
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

// Output function
function output_message($message) {
    echo $message . "<br>";
    flush();
}

output_message("<h1>Update Admin Password</h1>");
output_message("<p>Updating admin user password...</p>");

try {
    // Get database connection
    $pdo = DB::getConnection();
    output_message("Connected to database successfully.");
    
    // First check if admin user exists
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $checkStmt->execute(['admin', 'admin@ayskrim.com']);
    $adminExists = $checkStmt->fetch();
    
    if ($adminExists) {
        // Admin exists, update password
        output_message("Admin user found, updating password...");
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ? OR email = ?");
        $result = $stmt->execute([
            'admin123',  // New plaintext password
            'admin',     // Username
            'admin@ayskrim.com' // Email
        ]);
        
        if ($result && $stmt->rowCount() > 0) {
            output_message("<div style='color: green; font-weight: bold;'>✓ Admin password updated successfully!</div>");
        } else {
            output_message("<div style='color: red; font-weight: bold;'>✗ Failed to update admin password.</div>");
        }
    } else {
        // Admin doesn't exist, create new admin user
        output_message("Admin user not found, creating new admin user...");
        $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password, role, verification_status) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            'Admin User',
            'admin',
            'admin@ayskrim.com',
            'admin123', // Plaintext password for demo
            'admin',
            'Verified'
        ]);
        
        if ($result) {
            output_message("<div style='color: green; font-weight: bold;'>✓ Admin user created successfully!</div>");
        } else {
            output_message("<div style='color: red; font-weight: bold;'>✗ Failed to create admin user.</div>");
        }
    }
    
    output_message("<p><a href='../landingPage/home/home.php'>Return to login page</a></p>");
    
} catch (PDOException $e) {
    output_message("<div style='color: red; font-weight: bold;'>ERROR: " . $e->getMessage() . "</div>");
}
?>
