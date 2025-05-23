document.addEventListener('DOMContentLoaded', function() {
    // Handle grid/list view toggle in all sections
    const viewButtons = document.querySelectorAll('.view-btn');
    viewButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Get the view type
            const viewType = this.getAttribute('data-view');
            
            // Find the container that contains this button
            const container = this.closest('.upcoming-bookings-container, .past-bookings-container, .past-orders-container');
            
            if (container) {
                // Get all cards in this container
                const cards = container.querySelectorAll('.booking-card, .past-order-card');
                
                // Remove active class from all view buttons in this container
                const containerViewButtons = container.querySelectorAll('.view-btn');
                containerViewButtons.forEach(button => button.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Apply the view type to the cards
                if (viewType === 'list') {
                    cards.forEach(card => {
                        card.classList.add('list-view');
                        card.classList.remove('grid-view');
                    });
                } else {
                    cards.forEach(card => {
                        card.classList.add('grid-view');
                        card.classList.remove('list-view');
                    });
                }
            }
        });
    });
    
    // Initialize variables
    let activeMainTab = 'orders';
    let activeOrdersTab = 'active-orders';
    let activeBookingsTab = 'upcoming-bookings';
    let viewMode = 'grid';
    let mapView = 'standard';
    let selectedOrderId = null;
    
    // Get DOM elements
    const mainTabBtns = document.querySelectorAll('.main-tab-btn');
    const mainTabsSlider = document.getElementById('main-tabs-slider');
    const secondaryTabBtns = document.querySelectorAll('.secondary-tab-btn');
    const ordersTabsSlider = document.getElementById('orders-tabs-slider');
    const bookingsTabsSlider = document.getElementById('bookings-tabs-slider');
    const viewBtns = document.querySelectorAll('.view-btn[data-view]');
    const dropdownTriggers = document.querySelectorAll('.dropdown-trigger');
    const viewControls = document.getElementById('view-controls');
    const orderCards = document.querySelectorAll('.order-card');
    const trackOrderBtns = document.querySelectorAll('.track-order-btn');
    const mapViewBtns = document.querySelectorAll('.map-view-btn');
    const mapFullscreenBtn = document.getElementById('map-fullscreen-btn');
    const driverMarker = document.getElementById('driver-marker');
    const deliveryRoute = document.getElementById('delivery-route');
    
    // Initialize tabs
    initTabs();
    
    // Initialize event listeners
    initEventListeners();
    
    // Initialize map if it exists
    initMap();
    
    // Sort Menu Toggle for Past Orders
    const sortOrdersButton = document.getElementById('sortOrdersButton');
    const sortOrdersMenu = document.getElementById('sortOrdersMenu');
    
    if (sortOrdersButton && sortOrdersMenu) {
        sortOrdersButton.addEventListener('click', function(e) {
            e.stopPropagation();
            sortOrdersMenu.classList.toggle('active');
        });
        
        // Close the menu when clicking anywhere else
        document.addEventListener('click', function() {
            if (sortOrdersMenu.classList.contains('active')) {
                sortOrdersMenu.classList.remove('active');
            }
        });
        
        // Prevent closing when clicking inside the menu
        sortOrdersMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
        
        // Handle sort options
        const sortOptions = document.querySelectorAll('.sort-option');
        sortOptions.forEach(option => {
            option.addEventListener('click', function() {
                const sortType = this.getAttribute('data-sort');
                sortOrders(sortType);
                
                // Update button text to show current sort
                const optionText = this.querySelector('span').textContent;
                sortOrdersButton.innerHTML = `<i class="fas fa-sort"></i><span>${optionText}</span>`;
                
                // Close the menu
                sortOrdersMenu.classList.remove('active');
            });
        });
    }
    
    // Function to sort orders
    function sortOrders(sortType) {
        const orderCards = document.querySelectorAll('.past-order-card');
        const orderCardsArray = Array.from(orderCards);
        const container = document.querySelector('.past-order-cards');
        
        if (!container) return;
        
        orderCardsArray.sort((a, b) => {
            if (sortType === 'date-newest') {
                const dateA = new Date(a.querySelector('.order-date').textContent);
                const dateB = new Date(b.querySelector('.order-date').textContent);
                return dateB - dateA;
            } else if (sortType === 'date-oldest') {
                const dateA = new Date(a.querySelector('.order-date').textContent);
                const dateB = new Date(b.querySelector('.order-date').textContent);
                return dateA - dateB;
            } else if (sortType === 'price-high') {
                const priceA = parseFloat(a.querySelector('.order-price').textContent.replace('$', ''));
                const priceB = parseFloat(b.querySelector('.order-price').textContent.replace('$', ''));
                return priceB - priceA;
            } else if (sortType === 'price-low') {
                const priceA = parseFloat(a.querySelector('.order-price').textContent.replace('$', ''));
                const priceB = parseFloat(b.querySelector('.order-price').textContent.replace('$', ''));
                return priceA - priceB;
            }
            return 0;
        });
        
        // Clear and append sorted cards
        container.innerHTML = '';
        orderCardsArray.forEach(card => {
            container.appendChild(card);
        });
    }
    
    // Add search functionality
    const searchBar = document.querySelector('.search-bar input');
    if (searchBar) {
        searchBar.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const orderCards = document.querySelectorAll('.past-order-card');
            
            orderCards.forEach(card => {
                const orderNumber = card.querySelector('.order-number').textContent.toLowerCase();
                const orderAddress = card.querySelector('.order-address span').textContent.toLowerCase();
                const orderComment = card.querySelector('.order-comment').textContent.toLowerCase();
                
                if (orderNumber.includes(searchTerm) || 
                    orderAddress.includes(searchTerm) || 
                    orderComment.includes(searchTerm)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
    
    // Toggle booking details for both Upcoming and Past Bookings sections
    const toggleDetailsLinks = document.querySelectorAll('.toggle-details');
    const hideDetailsLinks = document.querySelectorAll('.hide-details-link');
    
    toggleDetailsLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const bookingId = this.getAttribute('data-booking');
            const detailsSection = document.getElementById(`booking-details-${bookingId}`);
            
            // Hide all other expanded details
            const allExpandedDetails = document.querySelectorAll('.booking-details-expanded');
            allExpandedDetails.forEach(details => {
                if (details.id !== `booking-details-${bookingId}`) {
                    details.classList.remove('active');
                }
            });
            
            // Toggle this details section
            detailsSection.classList.add('active');
            
            // Change the arrow icon
            this.querySelector('i').classList.remove('fa-chevron-right');
            this.querySelector('i').classList.add('fa-chevron-down');
            
            // Change the text
            const viewDetailsLink = this.closest('.view-details-link');
            viewDetailsLink.querySelector('a').textContent = 'Hide details';
        });
    });
    
    hideDetailsLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const bookingId = this.getAttribute('data-booking');
            const detailsSection = document.getElementById(`booking-details-${bookingId}`);
            
            // Hide the details section
            detailsSection.classList.remove('active');
            
            // Change the arrow icon in the view details link
            const viewDetailsLink = document.querySelector(`.toggle-details[data-booking="${bookingId}"]`);
            viewDetailsLink.querySelector('i').classList.remove('fa-chevron-down');
            viewDetailsLink.querySelector('i').classList.add('fa-chevron-right');
            
            // Change the text back
            viewDetailsLink.textContent = 'View details';
        });
    });
    
    // Implement search functionality for upcoming bookings
    const upcomingBookingSearchBar = document.querySelector('.upcoming-bookings-container .search-bar input');
    if (upcomingBookingSearchBar) {
        upcomingBookingSearchBar.addEventListener('input', function() {
            searchBookings(this, '.upcoming-bookings-container .booking-card');
        });
    }
    
    // Implement search functionality for past bookings
    const pastBookingSearchBar = document.querySelector('.past-bookings-container .search-bar input');
    if (pastBookingSearchBar) {
        pastBookingSearchBar.addEventListener('input', function() {
            searchBookings(this, '.past-bookings-container .booking-card');
        });
    }
    
    // Generic function to search bookings
    function searchBookings(searchInput, cardSelector) {
        const searchTerm = searchInput.value.toLowerCase();
        const bookingCards = document.querySelectorAll(cardSelector);
        
        bookingCards.forEach(card => {
            const title = card.querySelector('.booking-title-overlay h4').textContent.toLowerCase();
            const date = card.querySelector('.booking-title-overlay p').textContent.toLowerCase();
            const location = card.querySelector('.booking-detail:nth-child(2) span').textContent.toLowerCase();
            const services = Array.from(card.querySelectorAll('.service-tag')).map(tag => tag.textContent.toLowerCase()).join(' ');
            
            // Also search in the review if it exists
            let review = '';
            const reviewElement = card.querySelector('.booking-review p');
            if (reviewElement) {
                review = reviewElement.textContent.toLowerCase();
            }
            
            if (title.includes(searchTerm) || 
                date.includes(searchTerm) || 
                location.includes(searchTerm) || 
                services.includes(searchTerm) ||
                review.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    // Handle action buttons
    document.querySelectorAll('.action-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Get the action type
            let actionType = '';
            if (this.classList.contains('details-btn')) {
                actionType = 'view details';
            } else if (this.classList.contains('reschedule-btn')) {
                actionType = 'reschedule';
            } else if (this.classList.contains('cancel-btn')) {
                actionType = 'cancel';
            }
            
            // Get the booking title
            const card = this.closest('.booking-card');
            const bookingTitle = card.querySelector('.booking-title-overlay h4').textContent;
            
            // For a real app, this would call an API or navigate to another page
            // For this demo, we'll just log the action
            console.log(`Action: ${actionType} for booking "${bookingTitle}"`);
            
            // If it's the details button, toggle the expanded details
            if (actionType === 'view details') {
                const toggleLink = card.querySelector('.toggle-details');
                if (toggleLink) {
                    toggleLink.click();
                }
            }
        });
    });
    
    // Functions
    function initTabs() {
        // Set initial active tabs based on URL parameters or defaults
        const urlParams = new URLSearchParams(window.location.search);
        const mainTabParam = urlParams.get('mainTab');
        const secondaryTabParam = urlParams.get('secondaryTab');
        
        if (mainTabParam) {
            activeMainTab = mainTabParam;
        }
        
        if (secondaryTabParam) {
            if (activeMainTab === 'orders') {
                activeOrdersTab = secondaryTabParam + '-orders';
            } else {
                activeBookingsTab = secondaryTabParam + '-bookings';
            }
        }
        
        // Set initial active tabs
        document.getElementById('orders-tab').classList.toggle('active', activeMainTab === 'orders');
        document.getElementById('bookings-tab').classList.toggle('active', activeMainTab === 'bookings');
        
        document.getElementById('active-orders-tab').classList.toggle('active', activeOrdersTab === 'active-orders');
        document.getElementById('past-orders-tab').classList.toggle('active', activeOrdersTab === 'past-orders');
        
        document.getElementById('upcoming-bookings-tab').classList.toggle('active', activeBookingsTab === 'upcoming-bookings');
        document.getElementById('past-bookings-tab').classList.toggle('active', activeBookingsTab === 'past-bookings');
        
        // Set initial slider positions
        updateMainTabSlider();
        updateSecondaryTabSlider('orders');
        updateSecondaryTabSlider('bookings');
        
        // Select first order by default if on orders tab
        if (activeMainTab === 'orders' && orderCards.length > 0) {
            selectOrder(orderCards[0].getAttribute('data-order-id'));
        }
    }
    
    function initEventListeners() {
        // Calendar view buttons click handler
        const calendarViewBtns = document.querySelectorAll('.calendar-view-btn');
        calendarViewBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Use the correct path to the calendar.php file
                window.location.href = '/calendar/calendar.php';
            });
        });
        
        // Main tab buttons
        mainTabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const tab = btn.getAttribute('data-tab');
                switchMainTab(tab);
            });
        });
        
        // Secondary tab buttons
        secondaryTabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const tab = btn.getAttribute('data-tab');
                
                if (tab === 'active-orders' || tab === 'past-orders') {
                    switchSecondaryTab(tab, 'orders');
                } else if (tab === 'upcoming-bookings' || tab === 'past-bookings') {
                    switchSecondaryTab(tab, 'bookings');
                }
        });
    });

        // View mode buttons
        viewBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const mode = btn.getAttribute('data-view');
                switchViewMode(mode);
            });
        });
        
        // Dropdown triggers
        dropdownTriggers.forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const dropdown = trigger.nextElementSibling;
                dropdown.classList.toggle('active');
        });
    });

        // Close dropdowns when clicking outside
        document.addEventListener('click', () => {
            document.querySelectorAll('.dropdown-menu.active').forEach(menu => {
                menu.classList.remove('active');
            });
        });
        
        // Order cards
        orderCards.forEach(card => {
            card.addEventListener('click', () => {
                const orderId = card.getAttribute('data-order-id');
                selectOrder(orderId);
        });
    });

        // Track order buttons
        trackOrderBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const orderId = btn.getAttribute('data-order-id');
                selectOrder(orderId);
                scrollToMap();
            });
        });
        
        // Map view buttons
        if (mapViewBtns) {
            mapViewBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const view = btn.getAttribute('data-map-view');
                    switchMapView(view);
                });
            });
        }
        
        // Map fullscreen button
        if (mapFullscreenBtn) {
            mapFullscreenBtn.addEventListener('click', toggleMapFullscreen);
        }
    }
    
    function switchMainTab(tab) {
        // Update active tab
        activeMainTab = tab;
        
        // Update tab buttons
        mainTabBtns.forEach(btn => {
            if (btn.getAttribute('data-tab') === tab) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        // Update tab content
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.remove('active');
        });
        document.getElementById(tab + '-tab').classList.add('active');
        
        // Update slider position
        updateMainTabSlider();
        
        // Add animation class to content
        const tabContent = document.querySelector(`#${tab}-tab .tab-content-inner`);
        if (tabContent) {
            tabContent.style.animation = 'none';
            setTimeout(() => {
                tabContent.style.animation = 'fadeIn 0.3s ease';
            }, 10);
        }
        
        // Update URL parameter
        const url = new URL(window.location);
        url.searchParams.set('mainTab', tab);
        history.pushState({}, '', url);
        
        // Reset secondary tab to first tab
        if (tab === 'orders') {
            switchSecondaryTab('active-orders', 'orders');
        } else {
            switchSecondaryTab('upcoming-bookings', 'bookings');
        }
    }
    
    function switchSecondaryTab(tab, type) {
        // Update active tab
        if (type === 'orders') {
            activeOrdersTab = tab;
        } else {
            activeBookingsTab = tab;
        }
        
        // Update tab buttons
        secondaryTabBtns.forEach(btn => {
            if (btn.getAttribute('data-tab') === tab) {
                btn.classList.add('active');
            } else if (
                (type === 'orders' && (btn.getAttribute('data-tab') === 'active-orders' || btn.getAttribute('data-tab') === 'past-orders')) ||
                (type === 'bookings' && (btn.getAttribute('data-tab') === 'upcoming-bookings' || btn.getAttribute('data-tab') === 'past-bookings'))
            ) {
                btn.classList.remove('active');
            }
        });
        
        // Update tab content
        document.querySelectorAll('.secondary-tab-pane').forEach(pane => {
            if (pane.id === tab + '-tab') {
                pane.classList.add('active');
            } else if (
                (type === 'orders' && (pane.id === 'active-orders-tab' || pane.id === 'past-orders-tab')) ||
                (type === 'bookings' && (pane.id === 'upcoming-bookings-tab' || pane.id === 'past-bookings-tab'))
            ) {
                pane.classList.remove('active');
            }
        });
        
        // Update slider position
        updateSecondaryTabSlider(type);
        
        // Update URL parameter
        const secondaryTabValue = tab.replace('-orders', '').replace('-bookings', '');
        const url = new URL(window.location);
        url.searchParams.set('secondaryTab', secondaryTabValue);
        history.pushState({}, '', url);
    }
    
    function switchViewMode(mode) {
        // Update active mode
        viewMode = mode;
        
        // Update view buttons
        viewBtns.forEach(btn => {
            if (btn.getAttribute('data-view') === mode) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        // Update layouts
        updateViewMode();
    }
    
    function updateViewMode() {
        const orderGrid = document.getElementById('active-orders-grid');
        const pastOrdersContainer = document.querySelector('.order-history .order-grid');
        const bookingGrid = document.querySelector('.booking-grid');
        
        if (orderGrid) {
            orderGrid.classList.toggle('list-view', viewMode === 'list');
        }
        
        if (pastOrdersContainer) {
            pastOrdersContainer.classList.toggle('list-view', viewMode === 'list');
        }
        
        if (bookingGrid) {
            bookingGrid.classList.toggle('list-view', viewMode === 'list');
        }
    }
    
    function switchMapView(view) {
        // Update active view
        mapView = view;
        
        // Update view buttons
        mapViewBtns.forEach(btn => {
            if (btn.getAttribute('data-map-view') === view) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        // Update map
        const mapElement = document.getElementById('map');
        
    if (mapElement) {
            mapElement.className = 'map ' + view + '-view';
            
            // Additional map-specific updates could be added here
            // For a real map implementation, you'd update the map tiles or layers
        }
    }
    
    function selectOrder(orderId) {
        // Update selected order
        selectedOrderId = orderId;
        
        // Update order cards
        orderCards.forEach(card => {
            card.classList.toggle('active', card.getAttribute('data-order-id') === orderId);
        });
        
        // Update map for selected order
        updateMapForOrder(orderId);
    }
    
    function updateMapForOrder(orderId) {
        // Here you would update the map to show the route for the selected order
        // This is a simplified version that just updates the visual elements
        
        // Animate driver marker
        if (driverMarker) {
            // Reset animation
            driverMarker.style.animation = 'none';
            driverMarker.offsetHeight; // Trigger reflow
            driverMarker.style.animation = 'moveDriver 10s linear infinite';
        }
        
        // Animate delivery route
        if (deliveryRoute) {
            deliveryRoute.style.animation = 'none';
            deliveryRoute.offsetHeight; // Trigger reflow
            deliveryRoute.style.animation = 'dashOffset 30s linear infinite';
        }
    }
    
    function scrollToMap() {
        const mapContainer = document.querySelector('.order-map-container');
        if (mapContainer) {
            mapContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
    
    function toggleMapFullscreen(map) {
        const mapContainer = document.querySelector('.map-container');
        if (!mapContainer) return;
        
        mapContainer.classList.toggle('fullscreen');
        
        if (mapContainer.classList.contains('fullscreen')) {
            document.body.style.overflow = 'hidden';
            const fullscreenButton = document.querySelector('.map-fullscreen-button i');
            if (fullscreenButton) {
                fullscreenButton.classList.remove('fa-expand');
                fullscreenButton.classList.add('fa-compress');
            }
        } else {
            document.body.style.overflow = '';
            const fullscreenButton = document.querySelector('.map-fullscreen-button i');
            if (fullscreenButton) {
                fullscreenButton.classList.remove('fa-compress');
                fullscreenButton.classList.add('fa-expand');
            }
        }
        
        // Important: when container size changes, Leaflet needs to recalculate
        if (map) {
            setTimeout(() => {
                map.invalidateSize();
            }, 100);
        }
    }
    
    function updateMainTabSlider() {
        if (mainTabsSlider) {
            const activeIndex = Array.from(mainTabBtns).findIndex(btn => btn.classList.contains('active'));
            if (activeIndex === 0) {
                mainTabsSlider.style.transform = 'translateX(0)';
            } else {
                mainTabsSlider.style.transform = 'translateX(100%)';
            }
        }
    }
    
    function updateSecondaryTabSlider(type) {
        const slider = type === 'orders' ? ordersTabsSlider : bookingsTabsSlider;
        const activeBtn = type === 'orders' 
            ? document.querySelector('.secondary-tab-btn[data-tab="' + activeOrdersTab + '"]')
            : document.querySelector('.secondary-tab-btn[data-tab="' + activeBookingsTab + '"]');
        
        if (slider && activeBtn) {
            const activeIndex = Array.from(
                activeBtn.parentElement.querySelectorAll('.secondary-tab-btn')
            ).indexOf(activeBtn);
            
            if (activeIndex === 0) {
                slider.style.transform = 'translateX(0)';
            } else {
                slider.style.transform = 'translateX(100%)';
            }
        }
    }

    function initMap() {
        // Check if map exists in the DOM
        const orderMap = document.getElementById('orderMap');
        if (!orderMap) return;

        // Ensure the map container has proper dimensions before initialization
        const mapContainer = orderMap.parentElement;
        if (mapContainer) {
            mapContainer.style.display = 'flex';
            mapContainer.style.flexDirection = 'column';
            mapContainer.style.backgroundColor = 'white';
        }

        // Force proper sizing
        orderMap.style.position = 'absolute';
        orderMap.style.height = '100%';
        orderMap.style.width = '100%';
        orderMap.style.margin = '0';
        orderMap.style.padding = '0';
        orderMap.style.backgroundColor = 'white';

        // Initialize Leaflet map with strict container check
        const map = L.map(orderMap, {
            zoomControl: false,
            attributionControl: true,
            scrollWheelZoom: true,
            dragging: true,
            doubleClickZoom: true,
            boxZoom: true
        }).setView([40.7128, -74.0060], 14);

        // Add tile layer (Standard view)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://leafletjs.com">Leaflet</a> | © Rey\'s Ice Cream Delivery Map | © OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        // Force multiple map redraws to ensure proper rendering
        // First immediate invalidate
        map.invalidateSize(true);
        
        // Then delayed invalidates to catch any delayed layout calculations
        setTimeout(() => map.invalidateSize(true), 100);
        setTimeout(() => map.invalidateSize(true), 500);
        setTimeout(() => map.invalidateSize(true), 1000);

        // Define SVG for gradient route
        const svgGradient = `
        <svg width="0" height="0">
            <defs>
                <linearGradient id="pink-gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#FF99B5" />
                    <stop offset="100%" stop-color="#FF3B8E" />
                </linearGradient>
            </defs>
        </svg>
        `;
        document.body.insertAdjacentHTML('beforeend', svgGradient);

        // Create custom icons
        const homeIcon = L.divIcon({
            className: 'custom-marker home-marker',
            html: '<div class="marker-icon"><i class="fas fa-home"></i></div>',
            iconSize: [50, 50],
            iconAnchor: [25, 25]
        });

        const truckIcon = L.divIcon({
            className: 'custom-marker truck-marker',
            html: '<div class="marker-icon"><i class="fas fa-truck"></i></div>',
            iconSize: [50, 50],
            iconAnchor: [25, 25]
        });

        const storeIcon = L.divIcon({
            className: 'custom-marker store-marker',
            html: '<div class="marker-icon"><i class="fas fa-store"></i></div>',
            iconSize: [50, 50],
            iconAnchor: [25, 25]
        });

        // Define points for demonstration
        const storePoint = [40.7128, -74.0060]; // Store location
        const customerPoint = [40.7300, -73.9850]; // Customer location
        const truckPoint = [
            storePoint[0] + (customerPoint[0] - storePoint[0]) * 0.6,
            storePoint[1] + (customerPoint[1] - storePoint[1]) * 0.6
        ];

        // Add markers
        const storeMarker = L.marker(storePoint, { icon: storeIcon }).addTo(map);
        const customerMarker = L.marker(customerPoint, { icon: homeIcon }).addTo(map);
        const truckMarker = L.marker(truckPoint, { icon: truckIcon }).addTo(map);

        // Add polyline for route with custom styling
        const routeCoordinates = [storePoint, truckPoint, customerPoint];
        const route = L.polyline(routeCoordinates, {
            color: '#FF5FA2',
            weight: 4,
            opacity: 0.8,
            className: 'delivery-route',
            smoothFactor: 1
        }).addTo(map);

        // Fit map to show all markers
        map.fitBounds(route.getBounds(), { padding: [50, 50] });
        
        // Store bounds for recenter function
        map._routeBounds = route.getBounds();

        // Store map instance for later use
        window.orderMap = map;

        // Initialize custom map controls
        initMapControls(map);
        
        // Handle container resize events
        window.addEventListener('resize', function() {
            if (map) map.invalidateSize(true);
        });
        
        // Add mutation observer to detect DOM changes that might affect layout
        const observer = new MutationObserver(() => {
            if (map) map.invalidateSize(true);
        });
        
        observer.observe(document.body, { 
            childList: true, 
            subtree: true, 
            attributes: true,
            attributeFilter: ['style', 'class']
        });
    }

    function initMapControls(map) {
        if (!map) return;
        
        // Map view options (Live Map / Order Details)
        const mapViewOptions = document.querySelectorAll('.map-view-option');
        mapViewOptions.forEach(option => {
            option.addEventListener('click', () => {
                mapViewOptions.forEach(opt => opt.classList.remove('active'));
                option.classList.add('active');
            });
        });
        
        // Map type controls (Standard/Satellite/Traffic)
        const mapViewButtons = document.querySelectorAll('.map-view-button');
        mapViewButtons.forEach(button => {
            button.addEventListener('click', () => {
                const view = button.getAttribute('data-view');
                updateMapTileLayer(map, view);
                
                mapViewButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
            });
        });

        // Zoom controls
        const zoomInButton = document.querySelector('.zoom-in');
        if (zoomInButton) {
            zoomInButton.addEventListener('click', () => {
                map.zoomIn(1);
            });
        }
        
        const zoomOutButton = document.querySelector('.zoom-out');
        if (zoomOutButton) {
            zoomOutButton.addEventListener('click', () => {
                map.zoomOut(1);
            });
        }

        // Center map button
        const centerButton = document.querySelector('.center');
        if (centerButton) {
            centerButton.addEventListener('click', () => {
                if (map._routeBounds) {
                    map.fitBounds(map._routeBounds, { padding: [50, 50] });
                }
            });
        }

        // Refresh map button
        const refreshButton = document.querySelector('.refresh');
        if (refreshButton) {
            refreshButton.addEventListener('click', () => {
                map.invalidateSize();
                
                // Animate refresh button
                refreshButton.classList.add('rotating');
                setTimeout(() => {
                    refreshButton.classList.remove('rotating');
                }, 1000);
            });
        }

        // Fullscreen button
        const fullscreenButton = document.querySelector('.map-fullscreen-button');
        if (fullscreenButton) {
            fullscreenButton.addEventListener('click', () => {
                toggleMapFullscreen(map);
            });
        }
    }

    function updateMapTileLayer(map, view) {
        if (!map) return;
        
        // Remove existing tile layers
        map.eachLayer(layer => {
            if (layer instanceof L.TileLayer) {
                map.removeLayer(layer);
            }
        });
        
        // Add appropriate tile layer based on view
        let tileLayer;
        switch (view) {
            case 'satellite':
                // Use a satellite tile provider (example: ESRI)
                tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© Rey\'s Ice Cream Delivery Map | Imagery © Esri',
                    maxZoom: 19
                });
                break;
            case 'traffic':
                // Use OpenStreetMap as a fallback for traffic view
                tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© Rey\'s Ice Cream Delivery Map | © OpenStreetMap contributors (Traffic data unavailable in demo)',
                    maxZoom: 19
                });
                break;
            default: // standard
                tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© Rey\'s Ice Cream Delivery Map | © OpenStreetMap contributors',
                    maxZoom: 19
                });
        }
        
        tileLayer.addTo(map);
    }

    function animateRoute() {
        // This function is kept for backward compatibility
        // Animation is now handled by Leaflet
    }
});
