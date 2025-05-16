<?php
// Script to fix admin password for deployment
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Output function
function output_message($message) {
    echo $message . "<br>";
    flush();
}

output_message("<h1>Fix Admin Password for Deployment</h1>");
output_message("<p>This script ensures the admin password is properly hashed for security.</p>");

try {
    // Get database connection
    $pdo = DB::getConnection();
    output_message("Connected to database successfully.");
    
    // Check for admin user with email 'admin@ayskrim.com' or 'admin123'
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR email = ? OR username = ? LIMIT 1");
    $stmt->execute(['admin@ayskrim.com', 'admin123', 'admin']);
    $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($adminUser) {
        // Admin exists, update password with proper hashing
        output_message("Admin user found with ID: " . $adminUser['id']);
        output_message("Current email: " . $adminUser['email']);
        output_message("Current username: " . $adminUser['username']);
        
        // Check if the password is already properly hashed
        $isProperlyHashed = (strpos($adminUser['password'], '$2y$') === 0) && strlen($adminUser['password']) > 50;
        output_message("Current password is " . ($isProperlyHashed ? "properly hashed" : "not properly hashed"));
        
        if (!$isProperlyHashed) {
            // Hash the admin password
            $newPassword = 'hanzamadmin'; // Using the known admin password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            
            // Update the admin password
            $updateStmt = $pdo->prepare("UPDATE users SET password = ?, email = ? WHERE id = ?");
            $updateStmt->execute([$hashedPassword, 'admin@ayskrim.com', $adminUser['id']]);
            
            output_message("<div style='color: green; font-weight: bold;'>✓ Admin password has been properly hashed and email set to admin@ayskrim.com</div>");
            output_message("<div style='color: blue;'>Admin login credentials:</div>");
            output_message("<div style='color: blue;'>- Email: admin@ayskrim.com</div>");
            output_message("<div style='color: blue;'>- Password: hanzamadmin</div>");
        } else {
            output_message("<div style='color: green; font-weight: bold;'>✓ Admin password is already properly hashed</div>");
        }
    } else {
        // Admin doesn't exist, create new admin user with hashed password
        output_message("Admin user not found, creating new admin user...");
        
        // Generate properly hashed password
        $password = 'hanzamadmin';
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password, role, verification_status) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            'Admin User',
            'admin',
            'admin@ayskrim.com',
            $hashedPassword,
            'admin',
            'Verified'
        ]);
        
        if ($result) {
            output_message("<div style='color: green; font-weight: bold;'>✓ Admin user created successfully with properly hashed password!</div>");
            output_message("<div style='color: blue;'>Admin login credentials:</div>");
            output_message("<div style='color: blue;'>- Email: admin@ayskrim.com</div>");
            output_message("<div style='color: blue;'>- Password: hanzamadmin</div>");
        } else {
            output_message("<div style='color: red; font-weight: bold;'>✗ Failed to create admin user.</div>");
        }
    }
    
    output_message("<p><a href='../landingPage/home/home.php'>Return to login page</a></p>");
    
} catch (PDOException $e) {
    output_message("<div style='color: red; font-weight: bold;'>ERROR: " . $e->getMessage() . "</div>");
}
?>
