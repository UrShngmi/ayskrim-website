/**
 * Admin Dashboard Functionality Test Script
 * This script tests various functionality of the admin dashboard
 * Run this in the browser console to check for issues
 */

(function() {
    console.log('Starting Admin Dashboard Functionality Test...');
    
    // Test 1: Check if all navigation links exist and have proper href attributes
    console.log('Test 1: Checking navigation links...');
    const navItems = document.querySelectorAll('.nav-item');
    let navLinksValid = true;
    
    navItems.forEach(item => {
        const href = item.getAttribute('href');
        if (!href || href === '#' || href === 'javascript:void(0)') {
            console.error(`Navigation link issue: ${item.textContent.trim()} has invalid href: ${href}`);
            navLinksValid = false;
        }
    });
    
    if (navLinksValid) {
        console.log('✅ All navigation links are valid');
    } else {
        console.error('❌ Some navigation links have issues');
    }
    
    // Test 2: Check if dropdown functionality works
    console.log('Test 2: Testing dropdown functionality...');
    const dropdowns = document.querySelectorAll('.dropdown');
    let dropdownsValid = true;
    
    dropdowns.forEach((dropdown, index) => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (!toggle) {
            console.error(`Dropdown #${index + 1} is missing toggle element`);
            dropdownsValid = false;
        }
        
        if (!menu) {
            console.error(`Dropdown #${index + 1} is missing menu element`);
            dropdownsValid = false;
        }
        
        // Test toggle functionality
        if (toggle && menu) {
            // Simulate click
            const event = new MouseEvent('click', {
                bubbles: true,
                cancelable: true,
                view: window
            });
            
            // Check if menu becomes visible on click
            toggle.dispatchEvent(event);
            
            // Check if the menu has the 'show' class after click
            if (!menu.classList.contains('show')) {
                console.error(`Dropdown #${index + 1} menu doesn't show on toggle click`);
                dropdownsValid = false;
            }
            
            // Check if clicking outside closes the dropdown
            document.body.dispatchEvent(new MouseEvent('click', {
                bubbles: true,
                cancelable: true,
                view: window
            }));
            
            // Menu should be hidden after clicking outside
            if (menu.classList.contains('show')) {
                console.error(`Dropdown #${index + 1} menu doesn't close when clicking outside`);
                dropdownsValid = false;
            }
        }
    });
    
    if (dropdownsValid) {
        console.log('✅ All dropdowns are functioning correctly');
    } else {
        console.error('❌ Some dropdowns have issues');
    }
    
    // Test 3: Check if sidebar toggle works
    console.log('Test 3: Testing sidebar toggle functionality...');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const adminSidebar = document.querySelector('.admin-sidebar');
    const adminMain = document.querySelector('.admin-main');
    let sidebarToggleValid = true;
    
    if (!sidebarToggle) {
        console.error('Sidebar toggle button not found');
        sidebarToggleValid = false;
    }
    
    if (!adminSidebar) {
        console.error('Admin sidebar not found');
        sidebarToggleValid = false;
    }
    
    if (!adminMain) {
        console.error('Admin main content area not found');
        sidebarToggleValid = false;
    }
    
    if (sidebarToggle && adminSidebar && adminMain) {
        // Get initial state
        const initialCollapsedState = adminSidebar.classList.contains('collapsed');
        const initialExpandedState = adminMain.classList.contains('expanded');
        
        // Simulate click
        sidebarToggle.dispatchEvent(new MouseEvent('click', {
            bubbles: true,
            cancelable: true,
            view: window
        }));
        
        // Check if state changed
        const newCollapsedState = adminSidebar.classList.contains('collapsed');
        const newExpandedState = adminMain.classList.contains('expanded');
        
        if (initialCollapsedState === newCollapsedState) {
            console.error('Sidebar collapsed state did not toggle');
            sidebarToggleValid = false;
        }
        
        if (initialExpandedState === newExpandedState) {
            console.error('Main content expanded state did not toggle');
            sidebarToggleValid = false;
        }
        
        // Restore original state
        if (initialCollapsedState !== newCollapsedState) {
            sidebarToggle.dispatchEvent(new MouseEvent('click', {
                bubbles: true,
                cancelable: true,
                view: window
            }));
        }
    }
    
    if (sidebarToggleValid) {
        console.log('✅ Sidebar toggle is functioning correctly');
    } else {
        console.error('❌ Sidebar toggle has issues');
    }
    
    // Test 4: Check if breadcrumbs are correct
    console.log('Test 4: Checking breadcrumbs...');
    const breadcrumbs = document.querySelectorAll('.breadcrumb-item');
    let breadcrumbsValid = true;
    
    if (breadcrumbs.length === 0) {
        console.error('No breadcrumbs found');
        breadcrumbsValid = false;
    } else {
        // First breadcrumb should be "Admin"
        if (breadcrumbs[0].textContent.trim() !== 'Admin') {
            console.error(`First breadcrumb should be "Admin", but found "${breadcrumbs[0].textContent.trim()}"`);
            breadcrumbsValid = false;
        }
        
        // Last breadcrumb should have "active" class
        if (!breadcrumbs[breadcrumbs.length - 1].classList.contains('active')) {
            console.error('Last breadcrumb does not have "active" class');
            breadcrumbsValid = false;
        }
    }
    
    if (breadcrumbsValid) {
        console.log('✅ Breadcrumbs are correct');
    } else {
        console.error('❌ Breadcrumbs have issues');
    }
    
    // Final summary
    console.log('Admin Dashboard Functionality Test Complete');
})();
