<?php
// Admin dashboard
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';

// Ensure user is admin
requireAdmin();

// Get current admin user
$admin = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Ayskrim</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #333;
        }
        .admin-header {
            background: linear-gradient(90deg, #4b5ae2, #6d9ee6);
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .admin-welcome {
            font-size: 1.8rem;
            font-weight: 600;
        }
        .admin-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .admin-name {
            font-weight: 500;
        }
        .logout-btn {
            background-color: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .logout-btn:hover {
            background-color: rgba(255,255,255,0.3);
        }
        .admin-content {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        .placeholder-text {
            text-align: center;
            padding: 5rem 1rem;
            font-size: 1.2rem;
            color: #6c757d;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="admin-welcome">Admin Dashboard</div>
        <div class="admin-info">
            <span class="admin-name">Welcome, <?php echo htmlspecialchars($admin['full_name']); ?></span>
            <form action="/ayskrimWebsite/api/auth/logoutApi.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION[CSRF_TOKEN_KEY] ?? ''); ?>">
                <button class="logout-btn" type="submit">Logout</button>
            </form>
        </div>
    </header>
    
    <main class="admin-content">
        <div class="placeholder-text">
            <h2>Admin Dashboard Content</h2>
            <p>This area will contain admin management features for Ayskrim Ice Cream.</p>
        </div>
    </main>
</body>
</html>
