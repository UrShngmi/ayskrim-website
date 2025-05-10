document.addEventListener("DOMContentLoaded", () => {
    console.log("DOM fully loaded, initializing navbar.js");

    // Logo animation elements
    const logoContainer = document.getElementById('logoContainer');
    const fixedHeader = document.querySelector('.fixed-header');
    
    // Search bar elements
    const searchIcon = document.getElementById('searchIcon');
    const searchInput = document.getElementById('searchInput');
    const closeSearch = document.getElementById('closeSearch');
    const navbarBg = document.querySelector('.navbar-bg');
    const navLinksContainer = document.querySelector('.nav-links-container');
    const rightSection = document.querySelector('.right-section');

    // Debugging: Log all elements
    console.log('searchIcon:', searchIcon);
    console.log('searchInput:', searchInput);
    console.log('closeSearch:', closeSearch);
    console.log('navbarBg:', navbarBg);
    console.log('navLinksContainer:', navLinksContainer);
    console.log('rightSection:', rightSection);

    // Check if all required elements are found
    if (!searchIcon || !searchInput || !closeSearch || !navbarBg || !navLinksContainer || !rightSection) {
        console.error('One or more required elements not found. Search functionality will not work.');
        return;
    }

    // Expose toggleSearchBar to the global scope for manual testing
    window.toggleSearchBarManually = (show) => toggleSearchBar(show);

    // Throttle function for better performance
    function throttle(callback, delay = 100) {
        let isThrottled = false;
        
        return function(...args) {
            if (isThrottled) return;
            
            isThrottled = true;
            callback.apply(this, args);
            
            setTimeout(() => {
                isThrottled = false;
            }, delay);
        };
    }

    // Function to handle logo resize on scroll
    let lastScrollTop = window.scrollY;

    function handleScroll() {
        const currentScroll = window.scrollY;
        const scrollThreshold = window.innerHeight * 0.15;
        const isScrollingUp = currentScroll < lastScrollTop;
    
        if (!isScrollingUp && currentScroll > 15) {
            logoContainer?.classList.add('scrolled');
            fixedHeader?.classList.add('scrolled');
        } else if (isScrollingUp && currentScroll < scrollThreshold) {
            logoContainer?.classList.remove('scrolled');
            fixedHeader?.classList.remove('scrolled');
        }
    
        lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
    }
    
    handleScroll();
    window.addEventListener('scroll', throttle(handleScroll, 10));
    
    // Search bar toggle functionality
    let isSearchOpen = false;

    function toggleSearchBar(show) {
        console.log('Toggling search bar to:', show);
        isSearchOpen = show;
        const lordIcon = searchIcon.querySelector('lord-icon');
        if (lordIcon) {
            lordIcon.setAttribute('trigger', show ? 'loop' : 'loop-on-hover');
            lordIcon.setAttribute('data-search-active', show);
        }
        if (show) {
            console.log('Adding expanded class to elements');
            navbarBg.classList.add('expanded');
            navLinksContainer.classList.add('expanded');
            rightSection.classList.add('expanded');
            searchInput.classList.add('expanded');
            closeSearch.classList.add('expanded');
            searchInput.focus();
            searchIcon.setAttribute('aria-expanded', 'true');
        } else {
            console.log('Removing expanded class from elements');
            navbarBg.classList.remove('expanded');
            navLinksContainer.classList.remove('expanded');
            rightSection.classList.remove('expanded');
            searchInput.classList.remove('expanded');
            closeSearch.classList.remove('expanded');
            searchInput.value = '';
            searchIcon.setAttribute('aria-expanded', 'false');
            searchIcon.focus();
        }
        // Log the current state of classes
        console.log('navbarBg classes:', navbarBg.classList.toString());
        console.log('navLinksContainer classes:', navLinksContainer.classList.toString());
        console.log('rightSection classes:', rightSection.classList.toString());
        console.log('searchInput classes:', searchInput.classList.toString());
        console.log('closeSearch classes:', closeSearch.classList.toString());
    }

    // Search icon click event
    searchIcon.addEventListener('click', () => {
        console.log('Search icon clicked');
        toggleSearchBar(!isSearchOpen);
    });

    // Search icon keyboard event
    searchIcon.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            console.log('Search icon keydown:', e.key);
            toggleSearchBar(!isSearchOpen);
        }
    });

    // Close button click event
    closeSearch.addEventListener('click', () => {
        console.log('Close search clicked');
        toggleSearchBar(false);
    });

    // Close button keyboard event
    closeSearch.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            console.log('Close search keydown:', e.key);
            toggleSearchBar(false);
        }
    });

    // Close search bar when clicking outside
    document.addEventListener('click', (e) => {
        if (isSearchOpen && !searchIcon.contains(e.target) && !searchInput.contains(e.target) && !closeSearch.contains(e.target)) {
            console.log('Clicked outside search area');
            toggleSearchBar(false);
        }
    });

    // Close search bar on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isSearchOpen) {
            console.log('Escape key pressed');
            toggleSearchBar(false);
        }
    });

    // Profile dropdown elements
    const profileDropdown = document.getElementById("profileDropdown");
    const profileButton = profileDropdown ? profileDropdown.querySelector(".profile-button") : null;
    const profileMenu = document.getElementById("profileMenu");
    
    // Cart count display
    const cartCountElements = document.querySelectorAll(".cart-count");
    
    async function updateCartCount() {
        try {
            // Get guest token from sessionStorage if it exists
            const guestToken = sessionStorage.getItem('guestToken');
            
            // Make API call to get cart count
            const response = await fetch(`/ayskrimWebsite/api/orders/getCartCount.php${guestToken ? `?token=${guestToken}` : ''}`);
            
            if (!response.ok) {
                throw new Error('Failed to fetch cart count');
            }
            
            const data = await response.json();
            
            if (data.success) {
                // Update all cart count elements
                cartCountElements.forEach(element => {
                    element.textContent = data.count;
                    element.style.display = data.count > 0 ? 'flex' : 'none';
                });
                // Store the count in sessionStorage
                sessionStorage.setItem('cartCount', data.count);
            } else {
                // Fallback to sessionStorage for guests
                let cartItems = [];
                try {
                    const storedCart = sessionStorage.getItem('guestCart');
                    if (storedCart) {
                        cartItems = JSON.parse(storedCart);
                    }
                } catch (error) {
                    console.error('Error reading cart from storage:', error);
                }
                
                const cartCount = cartItems.reduce((sum, item) => sum + (item.quantity || 0), 0);
                cartCountElements.forEach(element => {
                    element.textContent = cartCount;
                    element.style.display = cartCount > 0 ? 'flex' : 'none';
                });
                sessionStorage.setItem('cartCount', cartCount);
            }
        } catch (error) {
            console.error('Error updating cart count:', error);
            // Fallback to sessionStorage for guests
            let cartItems = [];
            try {
                const storedCart = sessionStorage.getItem('guestCart');
                if (storedCart) {
                    cartItems = JSON.parse(storedCart);
                }
            } catch (error) {
                console.error('Error reading cart from storage:', error);
            }
            
            const cartCount = cartItems.reduce((sum, item) => sum + (item.quantity || 0), 0);
            cartCountElements.forEach(element => {
                element.textContent = cartCount;
                element.style.display = cartCount > 0 ? 'flex' : 'none';
            });
            sessionStorage.setItem('cartCount', cartCount);
        }
    }
    
    // Update cart count on page load
    updateCartCount();
    
    // Update cart count when storage changes (for guests)
    window.addEventListener('storage', (e) => {
        if (e.key === 'guestCart') {
            updateCartCount();
        }
    });

    // Update cart count when page is shown (for browser navigation)
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            updateCartCount();
        }
    });

    // Expose updateCartCount to window for other scripts to use
    window.updateCartCount = updateCartCount;
    
    if (profileButton && profileMenu) {
        profileButton.setAttribute("aria-expanded", "false");
        profileMenu.setAttribute("aria-hidden", "true");
        
        // Flag to track if menu was opened by click
        let isMenuOpenedByClick = false;
        
        function toggleDropdown(show) {
            if (show) {
                profileMenu.classList.add('show');
                profileButton.setAttribute("aria-expanded", "true");
                profileMenu.setAttribute("aria-hidden", "false");
                isMenuOpenedByClick = true;
            } else {
                profileMenu.classList.remove('show');
                profileButton.setAttribute("aria-expanded", "false");
                profileMenu.setAttribute("aria-hidden", "true");
                isMenuOpenedByClick = false;
            }
        }
        
        // Click event for the profile button
        profileButton.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();
            const expanded = profileButton.getAttribute("aria-expanded") === "true";
            toggleDropdown(!expanded);
        });
        
        // Close dropdown when clicking outside
        document.addEventListener("click", (e) => {
            if (!profileDropdown.contains(e.target) && profileMenu.classList.contains('show')) {
                toggleDropdown(false);
            }
        });
        
        // Handle mouse leaving the dropdown area after a click open
        profileDropdown.addEventListener("mouseleave", () => {
            // Only close if it was opened by click and user is no longer hovering
            if (isMenuOpenedByClick && !profileDropdown.matches(":hover")) {
                toggleDropdown(false);
            }
        });
        
        // Handle keyboard navigation
        profileButton.addEventListener("keydown", (e) => {
            if (e.key === "Enter" || e.key === ' ') {
                e.preventDefault();
                const expanded = profileButton.getAttribute("aria-expanded") === "true";
                toggleDropdown(!expanded);
            }
            if (e.key === "Escape" && profileButton.getAttribute("aria-expanded") === "true") {
                toggleDropdown(false);
                profileButton.focus();
            }
        });
        
        const menuItems = profileMenu.querySelectorAll('a, button');
        if (menuItems.length > 0) {
            menuItems.forEach((item, index) => {
                item.addEventListener("keydown", (e) => {
                    if (e.key === "Escape") {
                        toggleDropdown(false);
                        profileButton.focus();
                    }
                    if (e.key === "Tab") {
                        if (e.shiftKey && index === 0) {
                            e.preventDefault();
                            menuItems[menuItems.length - 1].focus();
                        } else if (!e.shiftKey && index === menuItems.length - 1) {
                            e.preventDefault();
                            menuItems[0].focus();
                        }
                    }
                });
            });
        }
    }
    
    const mobileSubmenuToggles = document.querySelectorAll('.mobile-submenu-toggle');
    if (mobileSubmenuToggles.length > 0) {
        mobileSubmenuToggles.forEach(toggle => {
            toggle.addEventListener('click', () => {
                const submenuId = toggle.getAttribute('aria-controls');
                const submenu = document.getElementById(submenuId);
                if (submenu) {
                    const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
                    toggle.setAttribute('aria-expanded', !isExpanded);
                    submenu.style.display = isExpanded ? 'none' : 'block';
                    submenu.setAttribute('aria-hidden', isExpanded);
                }
            });
        });
    }

    window.guestToken = window.guestToken || (document.cookie.match(/guest_token=([^;]+)/) || [])[1];

    function updateCartCountBadge() {
        if (window.isLoggedIn) {
            $.ajax({
                url: '/ayskrimWebsite/api/orders/getCartCount.php',
                method: 'GET',
                success: function(response) {
                    setCartCount(response.success ? response.count : 0);
                },
                error: function() { setCartCount(0); }
            });
        } else {
            const token = window.guestToken || (document.cookie.match(/guest_token=([^;]+)/) || [])[1];
            if (token) {
                $.ajax({
                    url: '/ayskrimWebsite/api/orders/getCartCount.php?token=' + encodeURIComponent(token),
                    method: 'GET',
                    success: function(response) {
                        setCartCount(response.success ? response.count : getSessionCartCount());
                    },
                    error: function() { setCartCount(getSessionCartCount()); }
                });
            } else {
                setCartCount(getSessionCartCount());
            }
        }
    }

    function setCartCount(count) {
        const cartCountElement = $('.cart-count');
        if (cartCountElement.length) {
            cartCountElement.text(count);
            cartCountElement.css('display', count > 0 ? 'block' : 'none');
        }
        sessionStorage.setItem('cartCount', count);
    }

    function getSessionCartCount() {
        const cart = JSON.parse(sessionStorage.getItem('guestCart') || '[]');
        return cart.reduce((sum, item) => sum + item.quantity, 0);
    }

    $(document).ready(updateCartCountBadge);
    window.addEventListener('pageshow', updateCartCountBadge);
});