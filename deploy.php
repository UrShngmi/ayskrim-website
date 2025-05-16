<?php
/**
 * Deployment Helper Script for Ayskrim Website
 * 
 * This script helps prepare the website for deployment by:
 * 1. Checking for common deployment issues
 * 2. Creating a production-ready database export
 * 3. Generating a list of files to be uploaded
 * 
 * Usage: Run this script from the command line:
 * php deploy.php
 */

// Start output buffering
ob_start();
echo "=== Ayskrim Website Deployment Helper ===\n\n";

// Check PHP version
echo "Checking PHP version... ";
if (version_compare(PHP_VERSION, '8.0.0', '>=')) {
    echo "OK (" . PHP_VERSION . ")\n";
} else {
    echo "WARNING: Your PHP version (" . PHP_VERSION . ") is lower than recommended (8.0.0+)\n";
}

// Check for config.production.php
echo "Checking for production config... ";
if (file_exists(__DIR__ . '/includes/config.production.php')) {
    echo "Found\n";
    
    // Check if production config has been customized
    $config = file_get_contents(__DIR__ . '/includes/config.production.php');
    if (strpos($config, 'YOUR_PRODUCTION_DB_HOST') !== false) {
        echo "WARNING: Production config contains placeholder values. Update before deployment.\n";
    }
} else {
    echo "Not found. Please create includes/config.production.php\n";
}

// Check for .htaccess
echo "Checking for .htaccess... ";
if (file_exists(__DIR__ . '/.htaccess')) {
    echo "Found\n";
} else {
    echo "Not found. Please create .htaccess file\n";
}

// Check database connection
echo "Checking database connection... ";
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

try {
    $pdo = DB::getConnection();
    echo "Connected successfully\n";
    
    // Count tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Found " . count($tables) . " tables in database\n";
    
    // Check for admin user
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $stmt->execute();
    $adminCount = $stmt->fetchColumn();
    echo "Found " . $adminCount . " admin users\n";
    
    if ($adminCount === 0) {
        echo "WARNING: No admin users found. Run database/update_admin_password.php before deployment\n";
    }
    
    // Create database export
    echo "Creating database export... ";
    $exportFile = __DIR__ . '/database/export_' . date('Y-m-d_H-i-s') . '.sql';
    
    // This is a simple export. For a more comprehensive export, use mysqldump
    $export = "-- Ayskrim Database Export\n";
    $export .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
    
    foreach ($tables as $table) {
        // Get create table statement
        $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
        $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
        $export .= $createTable['Create Table'] . ";\n\n";
        
        // Get table data
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) > 0) {
            $export .= "-- Data for table `$table`\n";
            $export .= "INSERT INTO `$table` VALUES\n";
            
            $rowStrings = [];
            foreach ($rows as $row) {
                $values = array_map(function($value) use ($pdo) {
                    if ($value === null) {
                        return 'NULL';
                    }
                    return $pdo->quote($value);
                }, array_values($row));
                
                $rowStrings[] = "(" . implode(", ", $values) . ")";
            }
            
            $export .= implode(",\n", $rowStrings) . ";\n\n";
        }
    }
    
    file_put_contents($exportFile, $export);
    echo "Done. Saved to " . $exportFile . "\n";
    
} catch (PDOException $e) {
    echo "Failed: " . $e->getMessage() . "\n";
}

// Create list of files to upload
echo "Creating file list for deployment... ";
$fileList = __DIR__ . '/deployment-files.txt';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));
$files = [];

foreach ($iterator as $file) {
    if ($file->isFile()) {
        $path = str_replace(__DIR__ . '/', '', $file->getPathname());
        
        // Skip certain files and directories
        if (
            strpos($path, '.git/') === 0 ||
            strpos($path, 'node_modules/') === 0 ||
            strpos($path, 'vendor/') === 0 ||
            strpos($path, '.DS_Store') !== false ||
            strpos($path, 'deploy.php') === 0 ||
            strpos($path, 'deployment-files.txt') === 0 ||
            strpos($path, 'database/export_') === 0
        ) {
            continue;
        }
        
        $files[] = $path;
    }
}

file_put_contents($fileList, implode("\n", $files));
echo "Done. Found " . count($files) . " files. List saved to " . $fileList . "\n";

// Final message
echo "\n=== Deployment Preparation Complete ===\n";
echo "Please review the deployment-checklist.md file for next steps.\n";

// Get the output
$output = ob_get_clean();

// Display the output
echo $output;

// Save the output to a log file
file_put_contents(__DIR__ . '/deployment-log.txt', $output);
echo "Log saved to deployment-log.txt\n";
