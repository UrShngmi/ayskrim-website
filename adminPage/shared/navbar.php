<?php
// Shared admin navbar
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/constants.php';

// Get current page to highlight active nav item
$currentPage = basename(dirname($_SERVER['PHP_SELF']));

// Define navigation items with icons and paths
$navItems = [
    'dashboard' => [
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>',
        'title' => 'Dashboard',
        'path' => ADMIN_PAGES['dashboard']
    ],
    'orders' => [
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>',
        'title' => 'Orders',
        'path' => ADMIN_PAGES['orders']
    ],
    'menu' => [
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>',
        'title' => 'Menu',
        'path' => ADMIN_PAGES['menu']
    ],
    'inventory' => [
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>',
        'title' => 'Inventory',
        'path' => ADMIN_PAGES['inventory']
    ],
    'bookings' => [
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>',
        'title' => 'Bookings',
        'path' => ADMIN_PAGES['bookings']
    ],
    'expenses' => [
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
        'title' => 'Expenses',
        'path' => ADMIN_PAGES['expenses']
    ],
    'reports' => [
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>',
        'title' => 'Reports',
        'path' => ADMIN_PAGES['reports']
    ],
    'settings' => [
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>',
        'title' => 'Settings',
        'path' => ADMIN_PAGES['settings']
    ],
    'logs' => [
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>',
        'title' => 'Logs',
        'path' => ADMIN_PAGES['logs']
    ]
];
?>

<!-- Admin Sidebar -->
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="/ayskrimWebsite/assets/images/logo.png" alt="Ayskrim Logo">
            <span>Ayskrim Admin</span>
        </div>
        <button class="sidebar-toggle" aria-label="Toggle Sidebar">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
            </svg>
        </button>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <?php foreach(['dashboard', 'orders', 'menu', 'inventory'] as $page): ?>
                <a href="<?php echo $navItems[$page]['path']; ?>" class="nav-item <?php echo $currentPage === $page ? 'active' : ''; ?>">
                    <?php echo $navItems[$page]['icon']; ?>
                    <span class="nav-text"><?php echo $navItems[$page]['title']; ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Management</div>
            <?php foreach(['bookings', 'expenses', 'reports'] as $page): ?>
                <a href="<?php echo $navItems[$page]['path']; ?>" class="nav-item <?php echo $currentPage === $page ? 'active' : ''; ?>">
                    <?php echo $navItems[$page]['icon']; ?>
                    <span class="nav-text"><?php echo $navItems[$page]['title']; ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">System</div>
            <?php foreach(['settings', 'logs'] as $page): ?>
                <a href="<?php echo $navItems[$page]['path']; ?>" class="nav-item <?php echo $currentPage === $page ? 'active' : ''; ?>">
                    <?php echo $navItems[$page]['icon']; ?>
                    <span class="nav-text"><?php echo $navItems[$page]['title']; ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>
</aside>

<!-- Mobile sidebar toggle button (only visible on mobile) -->
<button class="mobile-sidebar-toggle">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
    </svg>
</button>
