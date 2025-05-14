<?php
// Test file for admin header
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/constants.php';

// Ensure user is admin
requireAdmin();

// Get current admin user
$admin = getCurrentUser();

// Set page title for testing
$pageTitle = 'Test Header';

// Define breadcrumb based on current page
$breadcrumbs = [
    ['title' => 'Admin', 'path' => ADMIN_PAGES['dashboard']],
    ['title' => $pageTitle, 'path' => '#']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Admin Header | Ayskrim</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/ayskrimWebsite/adminPage/shared/admin.css">
    <style>
        .test-container {
            padding: 2rem;
            max-width: 800px;
            margin: 2rem auto;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .test-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .test-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .test-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .test-description {
            margin-bottom: 1rem;
            color: #4b5563;
        }
        
        .test-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            background-color: #0ea5e9;
            color: white;
            border: none;
            border-radius: 0.375rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        
        .test-button:hover {
            background-color: #0284c7;
        }
        
        .test-result {
            margin-top: 1rem;
            padding: 1rem;
            background-color: #f3f4f6;
            border-radius: 0.375rem;
            font-family: monospace;
            white-space: pre-wrap;
            display: none;
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Include sidebar/navbar -->
        <?php include_once(__DIR__ . '/navbar.php'); ?>

        <div class="admin-main">
            <!-- Include header -->
            <?php include_once(__DIR__ . '/header.php'); ?>
            
            <div class="admin-content">
                <h1>Admin Header Test Page</h1>
                <p>This page is used to test the admin header functionality.</p>
                
                <div class="test-container">
                    <div class="test-section">
                        <div class="test-title">Test 1: Dropdown Functionality</div>
                        <div class="test-description">
                            This test checks if the dropdowns in the header work correctly.
                        </div>
                        <button class="test-button" id="testDropdowns">Run Test</button>
                        <div class="test-result" id="dropdownTestResult"></div>
                    </div>
                    
                    <div class="test-section">
                        <div class="test-title">Test 2: Navigation Links</div>
                        <div class="test-description">
                            This test checks if all navigation links in the sidebar are valid.
                        </div>
                        <button class="test-button" id="testNavLinks">Run Test</button>
                        <div class="test-result" id="navLinksTestResult"></div>
                    </div>
                    
                    <div class="test-section">
                        <div class="test-title">Test 3: Sidebar Toggle</div>
                        <div class="test-description">
                            This test checks if the sidebar toggle button works correctly.
                        </div>
                        <button class="test-button" id="testSidebarToggle">Run Test</button>
                        <div class="test-result" id="sidebarToggleTestResult"></div>
                    </div>
                    
                    <div class="test-section">
                        <div class="test-title">Test 4: Breadcrumbs</div>
                        <div class="test-description">
                            This test checks if the breadcrumbs are displayed correctly.
                        </div>
                        <button class="test-button" id="testBreadcrumbs">Run Test</button>
                        <div class="test-result" id="breadcrumbsTestResult"></div>
                    </div>
                    
                    <div class="test-section">
                        <div class="test-title">Run All Tests</div>
                        <div class="test-description">
                            Run all tests at once.
                        </div>
                        <button class="test-button" id="testAll">Run All Tests</button>
                        <div class="test-result" id="allTestsResult"></div>
                    </div>
                </div>
            </div>
            
            <!-- Include footer -->
            <?php include_once(__DIR__ . '/footer.php'); ?>
        </div>
    </div>
    
    <script src="/ayskrimWebsite/adminPage/shared/admin.js"></script>
    <script src="/ayskrimWebsite/adminPage/shared/test-functionality.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Test dropdowns
            document.getElementById('testDropdowns').addEventListener('click', function() {
                const result = document.getElementById('dropdownTestResult');
                result.style.display = 'block';
                result.textContent = 'Running dropdown test...';
                
                // Test logic here
                const dropdowns = document.querySelectorAll('.dropdown');
                let issues = [];
                
                dropdowns.forEach((dropdown, index) => {
                    const toggle = dropdown.querySelector('.dropdown-toggle');
                    const menu = dropdown.querySelector('.dropdown-menu');
                    
                    if (!toggle) {
                        issues.push(`Dropdown #${index + 1} is missing toggle element`);
                    }
                    
                    if (!menu) {
                        issues.push(`Dropdown #${index + 1} is missing menu element`);
                    }
                });
                
                if (issues.length === 0) {
                    result.textContent = '✅ All dropdowns have the correct structure';
                } else {
                    result.textContent = '❌ Issues found:\n' + issues.join('\n');
                }
            });
            
            // Test navigation links
            document.getElementById('testNavLinks').addEventListener('click', function() {
                const result = document.getElementById('navLinksTestResult');
                result.style.display = 'block';
                result.textContent = 'Running navigation links test...';
                
                // Test logic here
                const navItems = document.querySelectorAll('.nav-item');
                let issues = [];
                
                navItems.forEach(item => {
                    const href = item.getAttribute('href');
                    if (!href || href === '#' || href === 'javascript:void(0)') {
                        issues.push(`Navigation link "${item.textContent.trim()}" has invalid href: ${href}`);
                    }
                });
                
                if (issues.length === 0) {
                    result.textContent = `✅ All ${navItems.length} navigation links are valid`;
                } else {
                    result.textContent = '❌ Issues found:\n' + issues.join('\n');
                }
            });
            
            // Test sidebar toggle
            document.getElementById('testSidebarToggle').addEventListener('click', function() {
                const result = document.getElementById('sidebarToggleTestResult');
                result.style.display = 'block';
                result.textContent = 'Running sidebar toggle test...';
                
                // Test logic here
                const sidebarToggle = document.querySelector('.sidebar-toggle');
                const adminSidebar = document.querySelector('.admin-sidebar');
                const adminMain = document.querySelector('.admin-main');
                let issues = [];
                
                if (!sidebarToggle) {
                    issues.push('Sidebar toggle button not found');
                }
                
                if (!adminSidebar) {
                    issues.push('Admin sidebar not found');
                }
                
                if (!adminMain) {
                    issues.push('Admin main content area not found');
                }
                
                if (issues.length === 0) {
                    result.textContent = '✅ Sidebar toggle components found';
                } else {
                    result.textContent = '❌ Issues found:\n' + issues.join('\n');
                }
            });
            
            // Test breadcrumbs
            document.getElementById('testBreadcrumbs').addEventListener('click', function() {
                const result = document.getElementById('breadcrumbsTestResult');
                result.style.display = 'block';
                result.textContent = 'Running breadcrumbs test...';
                
                // Test logic here
                const breadcrumbs = document.querySelectorAll('.breadcrumb-item');
                let issues = [];
                
                if (breadcrumbs.length === 0) {
                    issues.push('No breadcrumbs found');
                } else {
                    // First breadcrumb should be "Admin"
                    if (breadcrumbs[0].textContent.trim() !== 'Admin') {
                        issues.push(`First breadcrumb should be "Admin", but found "${breadcrumbs[0].textContent.trim()}"`);
                    }
                    
                    // Last breadcrumb should have "active" class
                    if (!breadcrumbs[breadcrumbs.length - 1].classList.contains('active')) {
                        issues.push('Last breadcrumb does not have "active" class');
                    }
                }
                
                if (issues.length === 0) {
                    result.textContent = '✅ Breadcrumbs are correct';
                } else {
                    result.textContent = '❌ Issues found:\n' + issues.join('\n');
                }
            });
            
            // Run all tests
            document.getElementById('testAll').addEventListener('click', function() {
                document.getElementById('testDropdowns').click();
                document.getElementById('testNavLinks').click();
                document.getElementById('testSidebarToggle').click();
                document.getElementById('testBreadcrumbs').click();
                
                const result = document.getElementById('allTestsResult');
                result.style.display = 'block';
                result.textContent = 'All tests completed. Check individual test results above.';
            });
        });
    </script>
</body>
</html>
