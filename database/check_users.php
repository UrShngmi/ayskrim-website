<?php
// Script to check existing users in the database
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

// Output function
function output_message($message) {
    echo $message . "<br>";
    flush();
}

output_message("<h1>Database User Check</h1>");

try {
    // Get database connection
    $pdo = DB::getConnection();
    output_message("Connected to database successfully.");
    
    // List all tables in the database to verify structure
    output_message("<h2>Database Tables</h2>");
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        output_message("<div style='color: orange;'>No tables found in the database!</div>");
    } else {
        output_message("<ul>");
        foreach ($tables as $table) {
            output_message("<li>{$table}</li>");
        }
        output_message("</ul>");
    }
    
    // Check if users table exists
    if (!in_array('users', $tables)) {
        output_message("<div style='color: red; font-weight: bold;'>Users table doesn't exist!</div>");
        output_message("<p>Run the setup.php script first to create tables.</p>");
        exit;
    }
    
    // Get all users
    output_message("<h2>Current Users</h2>");
    $users = $pdo->query("SELECT id, full_name, username, email, password, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        output_message("<div style='color: orange;'>No users found in the database!</div>");
        output_message("<p>Run the setup.php script to create an admin user.</p>");
    } else {
        output_message("<table border='1' cellpadding='5'>");
        output_message("<tr><th>ID</th><th>Name</th><th>Username</th><th>Email</th><th>Password (hashed/plaintext)</th><th>Role</th></tr>");
        
        foreach ($users as $user) {
            output_message("<tr>");
            output_message("<td>{$user['id']}</td>");
            output_message("<td>{$user['full_name']}</td>");
            output_message("<td>{$user['username']}</td>");
            output_message("<td>{$user['email']}</td>");
            output_message("<td>" . (strlen($user['password']) > 20 ? '[HASHED]' : $user['password']) . "</td>");
            output_message("<td>{$user['role']}</td>");
            output_message("</tr>");
        }
        
        output_message("</table>");
    }
    
    output_message("<h2>Fix Options</h2>");
    output_message("<p>1. <a href='setup.php'>Run full database setup</a> - Creates tables and admin user if none exists</p>");
    output_message("<p>2. <a href='update_admin_password.php'>Update admin password</a> - Only works if admin exists</p>");
    output_message("<p>3. <a href='../landingPage/home/home.php'>Return to login page</a></p>");
    
} catch (PDOException $e) {
    output_message("<div style='color: red; font-weight: bold;'>ERROR: " . $e->getMessage() . "</div>");
}
?>
