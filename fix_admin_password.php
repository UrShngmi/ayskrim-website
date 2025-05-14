<?php
// Script to fix admin password with proper hashing
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Output function
function output_message($message) {
    echo $message . "<br>";
    flush();
}

output_message("<h1>Fix Admin Password</h1>");
output_message("<p>Updating admin user password with proper hashing...</p>");

try {
    // Get database connection
    $pdo = DB::getConnection();
    output_message("Connected to database successfully.");
    
    // First check all users to see what we're working with
    $allUsers = $pdo->query("SELECT id, email, username, password, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
    output_message("<h3>Current Users in Database:</h3>");
    output_message("<ul>");
    foreach ($allUsers as $user) {
        output_message("<li>ID: {$user['id']}, Email: {$user['email']}, Username: {$user['username']}, Role: {$user['role']}</li>");
    }
    output_message("</ul>");
    
    // Check if admin user with email 'admin123' exists
    $checkStmt = $pdo->prepare("SELECT id, email, username, password FROM users WHERE email = ?");
    $checkStmt->execute(['admin123']);
    $adminUser = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($adminUser) {
        // Admin with email 'admin123' exists, update password with proper hashing
        output_message("Admin user found with ID: " . $adminUser['id']);
        output_message("Current email: " . $adminUser['email']);
        output_message("Current username: " . $adminUser['username']);
        
        // Check if the password is already hashed
        $isHashed = strlen($adminUser['password']) > 20;
        output_message("Current password is " . ($isHashed ? "hashed" : "plaintext"));
        
        // Update admin password
        $newPassword = 'hanzamadmin'; // As per user's memory
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $result = $stmt->execute([
            $hashedPassword,
            $adminUser['id']
        ]);
        
        if ($result) {
            output_message("<div style='color: green; font-weight: bold;'>✓ Admin password updated successfully!</div>");
            output_message("<div style='color: green;'>Email: " . $adminUser['email'] . "</div>");
            output_message("<div style='color: green;'>Username: " . $adminUser['username'] . "</div>");
            output_message("<div style='color: green;'>New password: " . $newPassword . "</div>");
            output_message("<div style='color: green;'>Password hash: " . substr($hashedPassword, 0, 10) . "..." . "</div>");
        } else {
            output_message("<div style='color: red; font-weight: bold;'>✗ Failed to update admin password.</div>");
        }
    } else {
        // Check if there's an admin user with username 'admin'
        $checkStmt = $pdo->prepare("SELECT id, email, username, password FROM users WHERE username = ? AND role = 'admin'");
        $checkStmt->execute(['admin']);
        $adminUser = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($adminUser) {
            // Admin with username 'admin' exists, update credentials
            output_message("Admin user found with ID: " . $adminUser['id']);
            output_message("Current email: " . $adminUser['email']);
            output_message("Current username: " . $adminUser['username']);
            
            // Check if the password is already hashed
            $isHashed = strlen($adminUser['password']) > 20;
            output_message("Current password is " . ($isHashed ? "hashed" : "plaintext"));
            
            // Update admin credentials
            $newEmail = 'admin123'; // As per user's memory
            $newPassword = 'hanzamadmin'; // As per user's memory
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("UPDATE users SET email = ?, password = ? WHERE id = ?");
            $result = $stmt->execute([
                $newEmail,
                $hashedPassword,
                $adminUser['id']
            ]);
            
            if ($result) {
                output_message("<div style='color: green; font-weight: bold;'>✓ Admin credentials updated successfully!</div>");
                output_message("<div style='color: green;'>New email: " . $newEmail . "</div>");
                output_message("<div style='color: green;'>Username: " . $adminUser['username'] . "</div>");
                output_message("<div style='color: green;'>New password: " . $newPassword . "</div>");
                output_message("<div style='color: green;'>Password hash: " . substr($hashedPassword, 0, 10) . "..." . "</div>");
            } else {
                output_message("<div style='color: red; font-weight: bold;'>✗ Failed to update admin credentials.</div>");
            }
        } else {
            output_message("<div style='color: orange; font-weight: bold;'>No admin user found with email 'admin123' or username 'admin'.</div>");
            output_message("Please check the database and update the script accordingly.");
        }
    }
    
    output_message("<p><a href='landingPage/login.php'>Go to login page</a></p>");
    
} catch (PDOException $e) {
    output_message("<div style='color: red; font-weight: bold;'>Database error: " . $e->getMessage() . "</div>");
}
