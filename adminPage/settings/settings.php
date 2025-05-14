<?php
// Admin settings
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// Ensure user is admin
requireAdmin();

// Get current admin user
$admin = getCurrentUser();

// Initialize variables
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'general';

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = DB::getConnection();

    if (isset($_POST['update_general'])) {
        // Update general settings
        $siteName = filter_input(INPUT_POST, 'site_name', FILTER_SANITIZE_STRING);
        $siteEmail = filter_input(INPUT_POST, 'site_email', FILTER_SANITIZE_EMAIL);
        $sitePhone = filter_input(INPUT_POST, 'site_phone', FILTER_SANITIZE_STRING);
        $siteAddress = filter_input(INPUT_POST, 'site_address', FILTER_SANITIZE_STRING);

        // Update settings in database
        $stmt = $pdo->prepare("
            INSERT INTO site_settings (setting_key, setting_value, updated_by, updated_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by), updated_at = NOW()
        ");

        $stmt->execute(['site_name', $siteName, $admin['id']]);
        $stmt->execute(['site_email', $siteEmail, $admin['id']]);
        $stmt->execute(['site_phone', $sitePhone, $admin['id']]);
        $stmt->execute(['site_address', $siteAddress, $admin['id']]);

        $message = 'General settings updated successfully.';
        $messageType = 'success';
    } elseif (isset($_POST['update_password'])) {
        // Update admin password
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validate passwords
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $message = 'All password fields are required.';
            $messageType = 'error';
        } elseif ($newPassword !== $confirmPassword) {
            $message = 'New passwords do not match.';
            $messageType = 'error';
        } elseif (strlen($newPassword) < 8) {
            $message = 'New password must be at least 8 characters long.';
            $messageType = 'error';
        } else {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$admin['id']]);
            $storedHash = $stmt->fetchColumn();

            if (!password_verify($currentPassword, $storedHash)) {
                $message = 'Current password is incorrect.';
                $messageType = 'error';
            } else {
                // Update password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $admin['id']]);

                $message = 'Password updated successfully.';
                $messageType = 'success';
            }
        }
    } elseif (isset($_POST['update_profile'])) {
        // Update admin profile
        $fullName = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);

        // Check if email is already in use by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $admin['id']]);
        if ($stmt->fetch()) {
            $message = 'Email is already in use by another user.';
            $messageType = 'error';
        } else {
            // Update profile
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
            $stmt->execute([$fullName, $email, $phone, $admin['id']]);

            $message = 'Profile updated successfully.';
            $messageType = 'success';
        }
    }
}

// Get current settings
$pdo = DB::getConnection();
$stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Default settings if not set
$settings['site_name'] = $settings['site_name'] ?? 'Ayskrim Ice Cream';
$settings['site_email'] = $settings['site_email'] ?? 'info@ayskrim.com';
$settings['site_phone'] = $settings['site_phone'] ?? '+63 917 123 4567';
$settings['site_address'] = $settings['site_address'] ?? '123 Main Street, Davao City, Philippines';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Ayskrim Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/ayskrimWebsite/adminPage/shared/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Include sidebar/navbar -->
        <?php include_once(__DIR__ . '/../shared/navbar.php'); ?>

        <div class="admin-main">
            <!-- Include header -->
            <?php include_once(__DIR__ . '/../shared/header.php'); ?>

            <div class="admin-content">
                <!-- Status message -->
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?>" data-auto-close="5000">
                        <div class="alert-content">
                            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?> alert-icon"></i>
                            <div class="alert-message"><?php echo $message; ?></div>
                        </div>
                        <button class="alert-close"><i class="fas fa-times"></i></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Settings</h3>
                    </div>
                    <div class="card-body">
                        <!-- Tabs -->
                        <div class="tabs">
                            <a href="?tab=general" class="tab <?php echo $activeTab === 'general' ? 'active' : ''; ?>">
                                <i class="fas fa-cog"></i> General
                            </a>
                            <a href="?tab=profile" class="tab <?php echo $activeTab === 'profile' ? 'active' : ''; ?>">
                                <i class="fas fa-user"></i> Profile
                            </a>
                            <a href="?tab=security" class="tab <?php echo $activeTab === 'security' ? 'active' : ''; ?>">
                                <i class="fas fa-lock"></i> Security
                            </a>
                        </div>

                        <!-- Tab content -->
                        <div class="tab-content">
                            <!-- General Settings -->
                            <div class="tab-pane <?php echo $activeTab === 'general' ? 'active' : ''; ?>" id="general">
                                <form action="" method="POST" class="settings-form">
                                    <div class="form-group">
                                        <label for="site_name">Site Name</label>
                                        <input type="text" id="site_name" name="site_name" class="form-control" value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="site_email">Contact Email</label>
                                        <input type="email" id="site_email" name="site_email" class="form-control" value="<?php echo htmlspecialchars($settings['site_email']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="site_phone">Contact Phone</label>
                                        <input type="text" id="site_phone" name="site_phone" class="form-control" value="<?php echo htmlspecialchars($settings['site_phone']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="site_address">Business Address</label>
                                        <textarea id="site_address" name="site_address" class="form-control" rows="3"><?php echo htmlspecialchars($settings['site_address']); ?></textarea>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" name="update_general" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Profile Settings -->
                            <div class="tab-pane <?php echo $activeTab === 'profile' ? 'active' : ''; ?>" id="profile">
                                <form action="" method="POST" class="settings-form">
                                    <div class="form-group">
                                        <label for="full_name">Full Name</label>
                                        <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email Address</label>
                                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($admin['phone'] ?? ''); ?>">
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Security Settings -->
                            <div class="tab-pane <?php echo $activeTab === 'security' ? 'active' : ''; ?>" id="security">
                                <form action="" method="POST" class="settings-form">
                                    <div class="form-group">
                                        <label for="current_password">Current Password</label>
                                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="new_password">New Password</label>
                                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                                        <div class="form-hint">Password must be at least 8 characters long.</div>
                                    </div>
                                    <div class="form-group">
                                        <label for="confirm_password">Confirm New Password</label>
                                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" name="update_password" class="btn btn-primary">Change Password</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Include footer -->
            <?php include_once(__DIR__ . '/../shared/footer.php'); ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-close alerts
            const alerts = document.querySelectorAll('.alert[data-auto-close]');
            alerts.forEach(alert => {
                const closeTime = parseInt(alert.getAttribute('data-auto-close'));
                if (!isNaN(closeTime)) {
                    setTimeout(() => {
                        alert.classList.add('fade-out');
                        setTimeout(() => {
                            alert.remove();
                        }, 300);
                    }, closeTime);
                }
            });

            // Close alert on button click
            const closeButtons = document.querySelectorAll('.alert-close');
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const alert = this.closest('.alert');
                    alert.classList.add('fade-out');
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                });
            });
        });
    </script>
</body>
</html>
