<?php
// Shared admin header
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Get current admin user
$admin = getCurrentUser();

// Get current page title
$currentPage = basename(dirname($_SERVER['PHP_SELF']));
$pageTitle = ucfirst($currentPage);

// Define breadcrumb based on current page
$breadcrumbs = [
    ['title' => 'Admin', 'path' => ADMIN_PAGES['dashboard']],
    ['title' => $pageTitle, 'path' => '#']
];
?>

<header class="admin-header">
    <div class="d-flex align-center gap-4">
        <div class="header-title"><?php echo $pageTitle; ?></div>

        <!-- Breadcrumbs -->
        <div class="breadcrumbs d-flex align-center">
            <?php foreach($breadcrumbs as $index => $crumb): ?>
                <?php if($index > 0): ?>
                    <span class="breadcrumb-separator">/</span>
                <?php endif; ?>

                <a href="<?php echo $crumb['path']; ?>" class="breadcrumb-item <?php echo ($index === count($breadcrumbs) - 1) ? 'active' : ''; ?>">
                    <?php echo $crumb['title']; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="header-actions">
        <!-- Notifications dropdown -->
        <div class="dropdown">
            <button type="button" class="dropdown-toggle" aria-label="Notifications">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span class="notification-badge">3</span>
            </button>
            <div class="dropdown-menu">
                <div class="dropdown-header">
                    <span>Notifications</span>
                    <a href="#" class="text-sm">Mark all as read</a>
                </div>
                <div class="dropdown-items">
                    <a href="<?php echo ADMIN_PAGES['orders']; ?>?id=12345" class="dropdown-item unread">
                        <div class="dropdown-item-icon blue">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <div class="dropdown-item-content">
                            <div class="dropdown-item-title">New order received</div>
                            <div class="dropdown-item-description">Order #12345 needs processing</div>
                            <div class="dropdown-item-time">5 minutes ago</div>
                        </div>
                    </a>
                    <a href="<?php echo ADMIN_PAGES['inventory']; ?>?action=restock" class="dropdown-item unread">
                        <div class="dropdown-item-icon yellow">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="dropdown-item-content">
                            <div class="dropdown-item-title">Low inventory alert</div>
                            <div class="dropdown-item-description">Vanilla ice cream is running low</div>
                            <div class="dropdown-item-time">1 hour ago</div>
                        </div>
                    </a>
                    <a href="<?php echo ADMIN_PAGES['bookings']; ?>?id=15" class="dropdown-item unread">
                        <div class="dropdown-item-icon green">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="dropdown-item-content">
                            <div class="dropdown-item-title">New booking request</div>
                            <div class="dropdown-item-description">Event booking for July 15</div>
                            <div class="dropdown-item-time">3 hours ago</div>
                        </div>
                    </a>
                </div>
                <div class="dropdown-footer">
                    <a href="<?php echo ADMIN_PAGES['dashboard']; ?>?section=notifications">View all notifications</a>
                </div>
            </div>
        </div>

        <!-- User dropdown -->
        <div class="dropdown">
            <div class="admin-user dropdown-toggle">
                <img src="<?php echo '/ayskrimWebsite/assets/images/profiles/' . htmlspecialchars($admin['profile_picture']); ?>" alt="Admin" class="user-avatar">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($admin['full_name']); ?></div>
                    <div class="user-role">Administrator</div>
                </div>
            </div>
            <div class="dropdown-menu">
                <a href="<?php echo ADMIN_PAGES['settings']; ?>?section=profile" class="dropdown-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>My Profile</span>
                </a>
                <a href="<?php echo ADMIN_PAGES['settings']; ?>" class="dropdown-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Account Settings</span>
                </a>
                <div class="dropdown-divider"></div>
                <form action="/ayskrimWebsite/api/auth/logoutApi.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION[CSRF_TOKEN_KEY] ?? ''); ?>">
                    <button type="submit" class="dropdown-item text-danger">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
