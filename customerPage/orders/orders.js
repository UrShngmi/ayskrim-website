console.log('orders.js loaded');

document.addEventListener('DOMContentLoaded', function() {
    console.log("Orders page script initialized");

    // Ensure this script doesn't interfere with navbar
    const navbarInitialized = document.querySelector('.navbar-bg');
    const logoContainer = document.getElementById('logoContainer');
    const fixedHeader = document.querySelector('.fixed-header');

    if (navbarInitialized) {
        console.log("Navbar detected, ensuring compatibility");
        if (logoContainer && fixedHeader) {
            setTimeout(() => {
                const currentScroll = window.scrollY;
                if (currentScroll > 15) {
                    logoContainer.classList.add('scrolled');
                    fixedHeader.classList.add('scrolled');
                }
            }, 100);
        }
    }

    // Handle recommended items navigation
    const prevButton = document.querySelector('.item-nav-button.prev-button');
    const nextButton = document.querySelector('.item-nav-button.next-button');
    const itemsGrid = document.querySelector('.items-grid');

    if (prevButton && nextButton && itemsGrid) {
        let scrollAmount = 0;
        const itemWidth = itemsGrid.querySelector('.item-card')?.offsetWidth || 200;
        const scrollDistance = itemWidth + 24;

        prevButton.addEventListener('click', () => {
            scrollAmount = Math.max(scrollAmount - scrollDistance, 0);
            itemsGrid.scrollTo({
                left: scrollAmount,
                behavior: 'smooth'
            });
        });

        nextButton.addEventListener('click', () => {
            const maxScroll = itemsGrid.scrollWidth - itemsGrid.clientWidth;
            scrollAmount = Math.min(scrollAmount + scrollDistance, maxScroll);
            itemsGrid.scrollTo({
                left: scrollAmount,
                behavior: 'smooth'
            });
        });

        itemsGrid.style.display = 'flex';
        itemsGrid.style.overflowX = 'hidden';
        itemsGrid.style.scrollBehavior = 'smooth';
        itemsGrid.style.gap = '24px';

        const itemCards = itemsGrid.querySelectorAll('.item-card');
        itemCards.forEach(card => {
            card.style.minWidth = '200px';
            card.style.maxWidth = '200px';
            card.style.flex = '0 0 auto';
        });
    }

    // View mode switching (Grid/List/Calendar)
    const viewButtons = document.querySelectorAll('.view-btn');
    viewButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const viewType = this.getAttribute('data-view');
            const container = this.closest('.upcoming-bookings-container, .past-bookings-container, .past-orders-container');

            if (container) {
                const cards = container.querySelectorAll('.booking-card, .past-order-card');
                const containerViewButtons = container.querySelectorAll('.view-btn');
                containerViewButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                if (viewType === 'calendar') {
                    alert('Calendar view will be implemented in a future update.');
                    return;
                }

                cards.forEach(card => {
                    card.classList.remove('grid-view', 'list-view');
                    card.classList.add(viewType + '-view');
                });

                if (viewType === 'list') {
                    container.querySelector('.booking-cards, .past-order-cards').classList.add('list-layout');
                } else {
                    container.querySelector('.booking-cards, .past-order-cards').classList.remove('list-layout');
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
    let activeOrdersData = []; // Added to store active orders for progress bar updates

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
    const mapViewOptions = document.querySelectorAll('.map-view-option');
    const orderMap = document.getElementById('orderMap');
    const orderDetails = document.getElementById('orderDetails');
    const orderList = document.querySelector('.order-list');

    // Initialize tabs
    initTabs();

    // Initialize event listeners
    initEventListeners();

    // Initialize progress bars immediately
    initializeProgressBars();

    // Count active orders and update header
    function updateActiveOrdersCount() {
        const orderItems = document.querySelectorAll('.order-item:not(.completed)');
        const ordersCount = document.querySelector('.orders-count');
        const count = orderItems.length;
        ordersCount.textContent = `${count} ${count === 1 ? 'Order' : 'Orders'}`;
    }

    // Initialize back to map button
    const backToMapBtn = document.querySelector('.back-to-map-btn');
    if (backToMapBtn) {
        backToMapBtn.addEventListener('click', function() {
            switchToMapView();
        });
    }

    // Map view toggle functionality
    if (mapViewOptions.length && orderMap && orderDetails) {
        mapViewOptions.forEach(option => {
            option.addEventListener('click', async function() {
                mapViewOptions.forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');

                if (this.textContent.trim() === 'Live Map') {
                    switchToMapView();
                } else {
                    await switchToDetailsView();
                }
            });
        });
    }

    // Sort menu toggle functionality
    const sortButtons = document.querySelectorAll('.sort-button');
    const sortMenus = document.querySelectorAll('.sort-menu');

    sortButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const menuId = this.id.replace('Button', 'Menu');
            const menu = document.getElementById(menuId);

            if (menu) {
                sortMenus.forEach(m => {
                    if (m.id !== menuId) {
                        m.classList.remove('active');
                    }
                });
                menu.classList.toggle('active');
            }
        });
    });

    document.addEventListener('click', function() {
        sortMenus.forEach(menu => {
            menu.classList.remove('active');
        });
    });

    sortMenus.forEach(menu => {
        menu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });

    const sortOptions = document.querySelectorAll('.sort-option');
    sortOptions.forEach(option => {
        option.addEventListener('click', function() {
            const sortType = this.getAttribute('data-sort');
            const containerId = this.closest('.sort-menu').id.replace('Menu', '').replace('sort', '').toLowerCase();
            sortItems(sortType, containerId);

            const optionText = this.querySelector('span').textContent;
            const buttonId = this.closest('.sort-menu').id.replace('Menu', 'Button');
            const button = document.getElementById(buttonId);
            if (button) {
                button.innerHTML = `<i class="fas fa-sort"></i><span>${optionText}</span>`;
            }

            this.closest('.sort-menu').classList.remove('active');
        });
    });

    function sortItems(sortType, containerId) {
        const containerSelector = containerId === 'orders' ? '.past-order-cards' :
                                 (containerId === 'upcomingbookings' ? '.upcoming-bookings .booking-cards' :
                                 '.past-bookings .booking-cards');
        const itemSelector = containerId === 'orders' ? '.past-order-card' : '.booking-card';
        const container = document.querySelector(containerSelector);
        const items = container?.querySelectorAll(itemSelector);

        if (!container || !items || items.length === 0) return;

        const itemsArray = Array.from(items);

        itemsArray.sort((a, b) => {
            if (sortType === 'date-newest') {
                const dateA = new Date(a.querySelector('.order-date, .booking-title-overlay p').textContent);
                const dateB = new Date(b.querySelector('.order-date, .booking-title-overlay p').textContent);
                return dateB - dateA;
            } else if (sortType === 'date-oldest') {
                const dateA = new Date(a.querySelector('.order-date, .booking-title-overlay p').textContent);
                const dateB = new Date(b.querySelector('.order-date, .booking-title-overlay p').textContent);
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

        container.innerHTML = '';
        itemsArray.forEach(item => {
            container.appendChild(item);
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

    // Toggle booking details
    const toggleDetailsLinks = document.querySelectorAll('.toggle-details');
    const hideDetailsLinks = document.querySelectorAll('.hide-details-link');

    toggleDetailsLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const bookingId = this.getAttribute('data-booking');
            const detailsSection = document.getElementById(`booking-details-${bookingId}`);

            const allExpandedDetails = document.querySelectorAll('.booking-details-expanded');
            allExpandedDetails.forEach(details => {
                if (details.id !== `booking-details-${bookingId}`) {
                    details.classList.remove('active');
                }
            });

            detailsSection.classList.add('active');
            this.querySelector('i').classList.remove('fa-chevron-right');
            this.querySelector('i').classList.add('fa-chevron-down');
            const viewDetailsLink = this.closest('.view-details-link');
            viewDetailsLink.querySelector('a').textContent = 'Hide details';
        });
    });

    hideDetailsLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const bookingId = this.getAttribute('data-booking');
            const detailsSection = document.getElementById(`booking-details-${bookingId}`);

            detailsSection.classList.remove('active');
            const viewDetailsLink = document.querySelector(`.toggle-details[data-booking="${bookingId}"]`);
            viewDetailsLink.querySelector('i').classList.remove('fa-chevron-down');
            viewDetailsLink.querySelector('i').classList.add('fa-chevron-right');
            viewDetailsLink.textContent = 'View details';
        });
    });

    // Search functionality for bookings
    const upcomingBookingSearchBar = document.querySelector('.upcoming-bookings-container .search-bar input');
    if (upcomingBookingSearchBar) {
        upcomingBookingSearchBar.addEventListener('input', function() {
            searchBookings(this, '.upcoming-bookings-container .booking-card');
        });
    }

    const pastBookingSearchBar = document.querySelector('.past-bookings-container .search-bar input');
    if (pastBookingSearchBar) {
        pastBookingSearchBar.addEventListener('input', function() {
            searchBookings(this, '.past-bookings-container .booking-card');
        });
    }

    function searchBookings(searchInput, cardSelector) {
        const searchTerm = searchInput.value.toLowerCase();
        const bookingCards = document.querySelectorAll(cardSelector);

        bookingCards.forEach(card => {
            const title = card.querySelector('.booking-title-overlay h4').textContent.toLowerCase();
            const date = card.querySelector('.booking-title-overlay p').textContent.toLowerCase();
            const location = card.querySelector('.booking-detail:nth-child(2) span').textContent.toLowerCase();
            const services = Array.from(card.querySelectorAll('.service-tag')).map(tag => tag.textContent.toLowerCase()).join(' ');
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
            let actionType = '';
            if (this.classList.contains('details-btn')) {
                actionType = 'view details';
            } else if (this.classList.contains('reschedule-btn')) {
                actionType = 'reschedule';
            } else if (this.classList.contains('cancel-btn')) {
                actionType = 'cancel';
            }

            const card = this.closest('.booking-card');
            const bookingTitle = card.querySelector('.booking-title-overlay h4').textContent;
            console.log(`Action: ${actionType} for booking "${bookingTitle}"`);

            if (actionType === 'view details') {
                const toggleLink = card.querySelector('.toggle-details');
                if (toggleLink) {
                    toggleLink.click();
                }
            }
        });
    });

    // Details link and track button click handlers
    function initOrderItemEventListeners() {
        const trackButtons = document.querySelectorAll('.track-button');
        const detailsLinks = document.querySelectorAll('.details-link');

        trackButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const orderId = this.getAttribute('data-order-id');
                selectOrder(orderId);
                // Ensure "Live Map" toggle is active
                mapViewOptions.forEach(opt => opt.classList.remove('active'));
                const liveMapOption = Array.from(mapViewOptions).find(opt => opt.textContent.trim() === 'Live Map');
                if (liveMapOption) liveMapOption.classList.add('active');
                switchToMapView();
                scrollToMap();
            });
        });

        detailsLinks.forEach(link => {
            link.addEventListener('click', async function(e) {
                e.preventDefault();
                e.stopPropagation();
                const orderId = this.getAttribute('data-order-id');
                selectOrder(orderId);
                // Ensure "Order Details" toggle is active
                mapViewOptions.forEach(opt => opt.classList.remove('active'));
                const detailsOption = Array.from(mapViewOptions).find(opt => opt.textContent.trim() === 'Order Details');
                if (detailsOption) detailsOption.classList.add('active');
                await switchToDetailsView();
                scrollToMap();
            });
        });
    }

    // Functions to switch between map and details views
    async function switchToDetailsView() {
        if (!selectedOrderId) {
            orderDetails.querySelector('.order-details-content').innerHTML = `
                <div class="error-message">
                    <p>Please select an order to view details.</p>
                </div>
            `;
            return;
        }

        // Hide map and ALL related UI components including map-view buttons and map-controls
        const mapRelatedElements = [
            orderMap,
            ...document.querySelectorAll('.map-help'),
            ...document.querySelectorAll('.map-fullscreen-button'),
            ...document.querySelectorAll('.map-controls'),
            ...document.querySelectorAll('.map-view-button'),
            ...document.querySelectorAll('.map-controls-right')
        ];
        mapRelatedElements.forEach(el => {
            if (el) el.style.display = 'none';
        });

        // Ensure only map-view-options remain visible
        document.querySelectorAll('.map-view-options').forEach(el => {
            if (el) el.style.display = '';
        });

        // Show order details
        orderDetails.style.display = 'block';
        orderDetails.querySelector('.order-details-content').innerHTML = `
            <div class="order-details-loading">
                <div class="spinner"></div>
                <p>Loading order details...</p>
            </div>
        `;

        // Fetch and display order details
        const orderData = await fetchOrderDetails(selectedOrderId);
        if (orderData) {
            displayOrderDetails(orderData);
        } else {
            orderDetails.querySelector('.order-details-content').innerHTML = `
                <div class="error-message">
                    <p>Failed to load order details. Please try again.</p>
                </div>
            `;
        }
    }

    function switchToMapView() {
        // Show map and related UI components
        const mapRelatedElements = [
            orderMap,
            ...document.querySelectorAll('.map-controls'),
            ...document.querySelectorAll('.map-help'),
            ...document.querySelectorAll('.map-fullscreen-button'),
            ...document.querySelectorAll('.map-view-options'),
            ...document.querySelectorAll('.map-view-button'),
            ...document.querySelectorAll('.map-controls-right')
        ];
        mapRelatedElements.forEach(el => {
            if (el) el.style.display = '';
        });

        // Hide order details
        orderDetails.style.display = 'none';

        // Invalidate map size if Leaflet map exists and center on the selected order
        if (typeof window.orderMap !== 'undefined') {
            window.orderMap.invalidateSize();
            if (selectedOrderId) {
                centerMapOnOrder(selectedOrderId);
            }
        }

        // Ensure progress bars are updated after map display
        updateProgressBars();
    }

    // === ACTIVE ORDERS MAP INTEGRATION ===
    async function fetchActiveOrders() {
        const res = await fetch('/ayskrimWebsite/api/orders/fetchActiveOrders.php');
        const data = await res.json();
        return data.orders || [];
    }

    async function fetchOrderDetails(orderId) {
        const res = await fetch(`/ayskrimWebsite/api/orders/fetchOrderDetails.php?orderId=${orderId}`);
        const data = await res.json();
        return data.order || null;
    }

    async function geocodeAddress(address) {
        if (!address) return null;
        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`;
        try {
            const res = await fetch(url);
            const results = await res.json();
            if (results.length > 0) {
                return { lat: parseFloat(results[0].lat), lon: parseFloat(results[0].lon) };
            }
        } catch (e) {
            console.error('Geocoding failed', e);
        }
        return null;
    }

    async function renderActiveOrdersAndMap() {
        console.log('renderActiveOrdersAndMap called');
        const container = document.querySelector('.order-list');
        const loadingDiv = container.querySelector('.active-orders-loading');
        if (loadingDiv) loadingDiv.style.display = 'flex';

        const orders = await fetchActiveOrders();
        orders.sort((a, b) => a.id - b.id);
        activeOrdersData = orders; // Store orders for progress bar updates
        const geocoded = await Promise.all(orders.map(async order => {
            const coords = await geocodeAddress(order.shipping_address);
            return coords ? { ...order, coords } : null;
        }));
        const validOrders = geocoded.filter(Boolean);

        if (loadingDiv) loadingDiv.remove();
        if (validOrders.length === 0) {
            container.innerHTML = '<div style="text-align:center;color:#ec4899;font-weight:500;padding:2em 0;">No active orders found.</div>';
            const mapDiv = document.getElementById('orderMap');
            if (mapDiv && mapDiv._leaflet_id) {
                mapDiv._leaflet_id = null;
                mapDiv.innerHTML = '';
            }
            return;
        }

        const mapDiv = document.getElementById('orderMap');
        if (mapDiv && mapDiv._leaflet_id) {
            mapDiv._leaflet_id = null;
            mapDiv.innerHTML = '';
        }
        const map = L.map('orderMap', {
            center: [validOrders[0].coords.lat, validOrders[0].coords.lon],
            zoom: 14,
            zoomControl: true,
            scrollWheelZoom: false
        });
        window.orderMap = map;
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        const markers = [];
        validOrders.forEach((order) => {
            const marker = L.marker([order.coords.lat, order.coords.lon]).addTo(map)
                .bindPopup(`Order #${order.id}<br>${order.shipping_address}`);
            markers.push({ orderId: order.id, marker, coords: order.coords });
        });
        window.orderMap.__markers = markers; // Store markers for access
        const group = new L.featureGroup(markers.map(m => m.marker));
        map.fitBounds(group.getBounds(), { padding: [50, 50] });

        container.innerHTML = '';
        validOrders.forEach((order, idx) => {
            const div = document.createElement('div');
            div.className = 'order-item';
            div.setAttribute('data-order-id', order.id);
            div.tabIndex = 0;
            const statusClass = order.order_status ? order.order_status.toLowerCase().replace(/ /g, '-') : 'processing';
            div.innerHTML = `
                <div class="order-header">
                    <div>
                        <span class="order-id">ORD-${order.id}</span>
                        <span class="order-date">${new Date(order.created_at).toLocaleDateString()}</span>
                    </div>
                    <span class="order-status ${statusClass}">${order.order_status || 'Processing'}</span>
                </div>
                <div class="order-info">
                    <div class="order-eta">
                        <i class="far fa-clock"></i>
                        ${order.estimated_delivery_time ?
                            `ETA: ${new Date(order.estimated_delivery_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}` :
                            'ETA: Processing'}
                    </div>
                    <div class="order-price">
                        ₱${parseFloat(order.total_amount).toFixed(2)}
                    </div>
                </div>
                <div class="order-progress">
                    <div class="progress-bar">
                        <div class="progress" style="width: ${getProgressWidth(order.order_status)};"></div>
                    </div>
                </div>
                <div class="order-actions">
                    <button class="order-action-button track-button" data-order-id="${order.id}">
                        <i class="fas fa-truck"></i>
                        Track Order
                    </button>
                    <button class="order-action-button contact-button">
                        <i class="fas fa-phone"></i>
                        Contact
                    </button>
                </div>
                <div class="order-footer">
                    <div class="last-updated">
                        <i class="fas fa-sync-alt"></i>
                        Last updated: ${getTimeAgoFromDate(new Date(order.created_at))}
                    </div>
                    <a href="#" class="details-link" data-order-id="${order.id}">Details</a>
                </div>
            `;

            div.addEventListener('click', async () => {
                selectOrder(order.id);
                // Update view based on active map-view-option
                const activeViewOption = document.querySelector('.map-view-option.active');
                if (activeViewOption && activeViewOption.textContent.trim() === 'Order Details') {
                    await switchToDetailsView();
                } else {
                    switchToMapView();
                }
            });

            if (idx === 0 && markers[0]) {
                setTimeout(() => {
                    selectOrder(order.id);
                    // Initialize with active view
                    const activeViewOption = document.querySelector('.map-view-option.active');
                    if (activeViewOption && activeViewOption.textContent.trim() === 'Order Details') {
                        switchToDetailsView();
                    } else {
                        centerMapOnOrder(order.id);
                    }
                }, 500);
            }

            container.appendChild(div);
        });

        initOrderItemEventListeners();
        updateActiveOrdersCount();
        updateProgressBars();
    }

    function getProgressWidth(status) {
        // Normalize status by trimming and converting to lowercase
        const normalizedStatus = status?.trim().toLowerCase();

        switch (normalizedStatus) {
            case 'pending': return '20%';
            case 'preparing': return '40%';
            case 'out for delivery': return '80%';
            case 'delivered': return '100%';
            default: return '20%';
        }
    }

    // Function to initialize progress bars on page load
    async function initializeProgressBars() {
        try {
            // Fetch active orders data first
            const orders = await fetchActiveOrders();
            if (orders && orders.length > 0) {
                activeOrdersData = orders; // Store orders for progress bar updates

                // Update all progress bars with correct widths
                updateProgressBars();
            } else {
                // If no orders from API, try to use the ones from the DOM
                const orderItems = document.querySelectorAll('.order-item');
                if (orderItems.length > 0) {
                    // Create a minimal data structure for the orders
                    activeOrdersData = Array.from(orderItems).map(item => {
                        const statusEl = item.querySelector('.order-status');
                        return {
                            id: parseInt(item.getAttribute('data-order-id')),
                            order_status: statusEl ? statusEl.textContent : 'Pending'
                        };
                    });
                    updateProgressBars();
                }
            }
        } catch (error) {
            console.error('Error initializing progress bars:', error);

            // Fallback to DOM-based initialization
            const orderItems = document.querySelectorAll('.order-item');
            if (orderItems.length > 0) {
                activeOrdersData = Array.from(orderItems).map(item => {
                    const statusEl = item.querySelector('.order-status');
                    return {
                        id: parseInt(item.getAttribute('data-order-id')),
                        order_status: statusEl ? statusEl.textContent : 'Pending'
                    };
                });
                updateProgressBars();
            }
        }
    }

    // Helper function to update progress bars for all orders
    function updateProgressBars() {
        document.querySelectorAll('.order-item').forEach(el => {
            const orderId = el.getAttribute('data-order-id');
            const order = activeOrdersData.find(o => o.id === parseInt(orderId));

            if (!order) {
                console.warn(`Order ${orderId} not found in activeOrdersData`);
                return;
            }

            // Try to find progress element in dynamically created orders
            const progress = el.querySelector('.progress');

            // Try to find progress element in PHP-rendered orders
            const progressBar = el.querySelector('.order-progress-bar');

            // Get the status class
            const statusClass = order.order_status.toLowerCase().replace(/ /g, '-');

            // Update the status class on the order status element
            const statusElement = el.querySelector('.order-status');
            if (statusElement) {
                // Remove all existing status classes
                statusElement.className = 'order-status';
                // Add the correct status class
                statusElement.classList.add(statusClass);
            }

            if (progress) {
                // Update progress width
                const width = getProgressWidth(order.order_status);
                progress.style.width = width;
                progress.parentElement.style.display = 'block';

                // Update progress class
                progress.className = 'progress';
                progress.classList.add(statusClass);

                console.log(`Updated progress for order ${orderId}: ${order.order_status} -> ${width}`);
            } else if (progressBar) {
                // Handle PHP-rendered progress bars
                const width = getProgressWidth(order.order_status);
                progressBar.style.width = width;
                progressBar.style.display = 'block';

                // Update progress class
                progressBar.className = 'order-progress-bar';
                progressBar.classList.add(statusClass);

                console.log(`Updated PHP progress bar for order ${orderId}: ${order.order_status} -> ${width}`);
            } else {
                console.warn(`Progress element missing for order ${orderId}`);
            }
        });
    }

    function getTimeAgoFromDate(date) {
        const now = new Date();
        const diff = now - date;
        if (diff < 60000) return 'Just now';
        else if (diff < 3600000) return `${Math.floor(diff / 60000)} ${Math.floor(diff / 60000) === 1 ? 'min' : 'mins'} ago`;
        else if (diff < 86400000) return `${Math.floor(diff / 3600000)} ${Math.floor(diff / 3600000) === 1 ? 'hour' : 'hours'} ago`;
        else if (diff < 604800000) return `${Math.floor(diff / 86400000)} ${Math.floor(diff / 86400000) === 1 ? 'day' : 'days'} ago`;
        else return date.toLocaleDateString();
    }

    function displayOrderDetails(order) {
        if (!order) return;
        const orderDetailsContent = document.querySelector('.order-details-content');
        if (!orderDetailsContent) return;

        const orderDate = new Date(order.created_at);
        const formattedDate = `${orderDate.toLocaleDateString()} at ${orderDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}`;
        const items = order.items ? order.items.map(item => `
            <div class="cart-item receipt-row"
                 data-product-id="${item.product_id}"
                 style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #f3f4f6;">
                <img src="${item.image_url ? `/ayskrimWebsite/assets/images/${item.image_url}` : '/ayskrimWebsite/assets/images/placeholder.jpg'}"
                     alt="${item.product_name}"
                     class="item-thumb"
                     style="width:38px;height:38px;border-radius:8px;object-fit:cover;flex-shrink:0;">
                <div class="item-details" style="flex:1;min-width:0;">
                    <div class="item-name" style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        ${item.product_name}
                    </div>
                    <div class="item-quantity" style="color:#6b7280;font-size:0.95rem;">
                        x${item.quantity}
                    </div>
                </div>
                <div class="item-price" style="color:#ec4899;font-weight:600;min-width:70px;text-align:right;">
                    ₱${parseFloat(item.subtotal).toFixed(2)}
                </div>
            </div>
        `).join('') : '<p>No items found</p>';

        const subtotal = order.items ? order.items.reduce((total, item) => total + parseFloat(item.subtotal), 0) : 0;
        const deliveryFee = parseFloat(order.total_amount) - subtotal;

        orderDetailsContent.innerHTML = `
            <div class="order-details-container">
                <div class="order-header">
                    <div class="order-number-date">
                        <div class="order-number">Order #${order.id}</div>
                        <div class="order-date">${formattedDate}</div>
                    </div>
                    <div class="order-status ${order.order_status.toLowerCase().replace(/ /g, '-')}">${order.order_status}</div>
                </div>
                <div class="order-info-sections">
                    <div class="order-section-card address-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <h3 class="section-title">Delivery Address</h3>
                        </div>
                        <div class="delivery-address">
                            ${order.shipping_address}
                        </div>
                    </div>
                    <div class="order-section-card delivery-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-truck"></i>
                            </div>
                            <h3 class="section-title">Delivery Details</h3>
                        </div>
                        <div class="delivery-details">
                            <div class="details-row">
                                <div class="details-label">Type:</div>
                                <div class="details-value">${order.delivery_type}</div>
                            </div>
                            <div class="details-row">
                                <div class="details-label">Tracking Code:</div>
                                <div class="details-value tracking-code">${order.tracking_code}</div>
                            </div>
                            <div class="details-row">
                                <div class="details-label">Estimated Delivery:</div>
                                <div class="details-value">${order.delivery_status || 'Processing'}</div>
                            </div>
                        </div>
                    </div>
                    <div class="order-section-card payment-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <h3 class="section-title">Payment Information</h3>
                        </div>
                        <div class="payment-details">
                            <div class="details-row">
                                <div class="details-label">Status:</div>
                                <div class="details-value">
                                    <span class="payment-status ${order.payment_status.toLowerCase()}">${order.payment_status}</span>
                                </div>
                            </div>
                            <div class="details-row">
                                <div class="details-label">Method:</div>
                                <div class="details-value">${order.payment_method || 'Cash on Delivery'}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="order-section-card order-items-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-shopping-basket"></i>
                        </div>
                        <h3 class="section-title">Order Items</h3>
                    </div>
                    <div class="order-items">
                        ${items}
                    </div>
                    <div class="summary-totals" style="margin-top:15px;border-top:1px solid #f3f4f6;padding-top:15px;">
                        <div class="summary-row" style="display:flex;justify-content:space-between;margin-bottom:8px;">
                            <span>Subtotal</span>
                            <span class="subtotal">₱${subtotal.toFixed(2)}</span>
                        </div>
                        <div class="summary-row" style="display:flex;justify-content:space-between;margin-bottom:8px;">
                            <span>Delivery Fee</span>
                            <span class="delivery-fee">₱${deliveryFee.toFixed(2)}</span>
                        </div>
                        <div class="summary-row total" style="display:flex;justify-content:space-between;font-size:1.25rem;font-weight:700;margin-top:10px;">
                            <span>Total</span>
                            <span class="total-amount" style="color:var(--pink-500);font-weight:800;">₱${parseFloat(order.total_amount).toFixed(2)}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    async function selectOrder(orderId) {
        selectedOrderId = orderId;
        document.querySelectorAll('.order-item').forEach(el => {
            el.classList.toggle('selected', el.getAttribute('data-order-id') === orderId);
            const order = activeOrdersData.find(o => o.id === parseInt(el.getAttribute('data-order-id')));

            if (!order) return;

            // Get the status class
            const statusClass = order.order_status.toLowerCase().replace(/ /g, '-');

            // Update the status class on the order status element
            const statusElement = el.querySelector('.order-status');
            if (statusElement) {
                // Remove all existing status classes
                statusElement.className = 'order-status';
                // Add the correct status class
                statusElement.classList.add(statusClass);
            }

            // Try both progress bar types
            const progress = el.querySelector('.progress');
            const progressBar = el.querySelector('.order-progress-bar');

            if (progress) {
                progress.style.width = getProgressWidth(order.order_status);
                progress.parentElement.style.display = 'block';

                // Update progress class
                progress.className = 'progress';
                progress.classList.add(statusClass);
            } else if (progressBar) {
                progressBar.style.width = getProgressWidth(order.order_status);
                progressBar.style.display = 'block';

                // Update progress class
                progressBar.className = 'order-progress-bar';
                progressBar.classList.add(statusClass);
            }
        });

        // Update view based on active map-view-option
        const activeViewOption = document.querySelector('.map-view-option.active');
        if (activeViewOption && activeViewOption.textContent.trim() === 'Order Details') {
            await switchToDetailsView();
        } else {
            switchToMapView();
            centerMapOnOrder(orderId);
        }
    }

    // Function to center the map on the selected order's pinned location
    function centerMapOnOrder(orderId) {
        if (!window.orderMap || !orderId) {
            console.warn('Map or orderId not available for centering');
            return;
        }

        const markers = window.orderMap.__markers || [];
        const marker = markers.find(m => m.orderId === parseInt(orderId));
        if (!marker) {
            console.warn(`No marker found for order ID: ${orderId}`);
            return;
        }

        // Close all existing popups to avoid clutter
        window.orderMap.eachLayer(layer => {
            if (layer instanceof L.Popup) {
                window.orderMap.removeLayer(layer);
            } else if (layer instanceof L.Marker && layer !== marker.marker) {
                layer.closePopup();
            }
        });

        // Reset map view to the marker's coordinates with a fixed zoom level
        window.orderMap.setView([marker.coords.lat, marker.coords.lon], 16, { animate: false });

        // Pan to the exact location to ensure precise centering
        window.orderMap.panTo([marker.coords.lat, marker.coords.lon], { animate: true, duration: 0.5 });

        // Open the popup for the selected marker
        marker.marker.openPopup();

        // Invalidate map size to handle rendering issues, with a slight delay
        setTimeout(() => {
            window.orderMap.invalidateSize();
            // Double-check centering after invalidation
            window.orderMap.setView([marker.coords.lat, marker.coords.lon], 16, { animate: false });
            // Ensure progress bars are updated after map centering
            updateProgressBars();
        }, 100);

        // Update progress bars immediately as well to prevent flashing of empty progress
        updateProgressBars();
    }

    // Function to scroll to the map section
    function scrollToMap() {
        const mapContainer = document.querySelector('.live-tracking');
        if (mapContainer) {
            mapContainer.scrollIntoView({ behavior: 'smooth' });
        }
    }

    function toggleMapFullscreen() {
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

        if (window.orderMap) {
            setTimeout(() => {
                window.orderMap.invalidateSize();
                // Recenter on the selected order after toggling fullscreen
                if (selectedOrderId) {
                    centerMapOnOrder(selectedOrderId);
                }
            }, 100);
        }
    }

    function initTabs() {
        const urlParams = new URLSearchParams(window.location.search);
        const mainTabParam = urlParams.get('mainTab');
        const secondaryTabParam = urlParams.get('secondaryTab');

        if (mainTabParam) activeMainTab = mainTabParam;
        if (secondaryTabParam) {
            if (activeMainTab === 'orders') {
                activeOrdersTab = secondaryTabParam + '-orders';
            } else {
                activeBookingsTab = secondaryTabParam + '-bookings';
            }
        }

        document.getElementById('orders-tab').classList.toggle('active', activeMainTab === 'orders');
        document.getElementById('bookings-tab').classList.toggle('active', activeMainTab === 'bookings');
        document.getElementById('active-orders-tab').classList.toggle('active', activeOrdersTab === 'active-orders');
        document.getElementById('past-orders-tab').classList.toggle('active', activeOrdersTab === 'past-orders');
        document.getElementById('upcoming-bookings-tab').classList.toggle('active', activeBookingsTab === 'upcoming-bookings');
        document.getElementById('past-bookings-tab').classList.toggle('active', activeBookingsTab === 'past-bookings');

        updateMainTabSlider();
        updateSecondaryTabSlider('orders');
        updateSecondaryTabSlider('bookings');

        if (activeMainTab === 'orders' && orderCards.length > 0) {
            selectOrder(orderCards[0].getAttribute('data-order-id'));
        }
    }

    function initEventListeners() {
        mainTabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const tab = btn.getAttribute('data-tab');
                switchMainTab(tab);
            });
        });

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

        viewBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const mode = btn.getAttribute('data-view');
                switchViewMode(mode);
            });
        });

        dropdownTriggers.forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const dropdown = trigger.nextElementSibling;
                dropdown.classList.toggle('active');
            });
        });

        document.addEventListener('click', () => {
            document.querySelectorAll('.dropdown-menu.active').forEach(menu => {
                menu.classList.remove('active');
            });
        });

        orderCards.forEach(card => {
            card.addEventListener('click', () => {
                const orderId = card.getAttribute('data-order-id');
                selectOrder(orderId);
            });
        });

        trackOrderBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const orderId = btn.getAttribute('data-order-id');
                selectOrder(orderId);
                switchToMapView();
                scrollToMap();
            });
        });

        if (mapViewBtns) {
            mapViewBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const view = btn.getAttribute('data-map-view');
                    switchMapView(view);
                });
            });
        }

        if (mapFullscreenBtn) {
            mapFullscreenBtn.addEventListener('click', toggleMapFullscreen);
        }

        const calendarViewBtns = document.querySelectorAll('.calendar-view-btn');
        calendarViewBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                window.location.href = '/calendar/calendar.php';
            });
        });
    }

    function switchMainTab(tab) {
        activeMainTab = tab;
        mainTabBtns.forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-tab') === tab);
        });
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.remove('active');
        });
        document.getElementById(tab + '-tab').classList.add('active');
        updateMainTabSlider();
        const tabContent = document.querySelector(`#${tab}-tab .tab-content-inner`);
        if (tabContent) {
            tabContent.style.animation = 'none';
            setTimeout(() => {
                tabContent.style.animation = 'fadeIn 0.3s ease';
            }, 10);
        }
        const url = new URL(window.location);
        url.searchParams.set('mainTab', tab);
        history.pushState({}, '', url);
        if (tab === 'orders') {
            switchSecondaryTab('active-orders', 'orders');
        } else {
            switchSecondaryTab('upcoming-bookings', 'bookings');
        }
    }

    function switchSecondaryTab(tab, type) {
        if (type === 'orders') {
            activeOrdersTab = tab;
        } else {
            activeBookingsTab = tab;
        }
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
        updateSecondaryTabSlider(type);
        const secondaryTabValue = tab.replace('-orders', '').replace('-bookings', '');
        const url = new URL(window.location);
        url.searchParams.set('secondaryTab', secondaryTabValue);
        history.pushState({}, '', url);
    }

    function switchViewMode(mode) {
        viewMode = mode;
        viewBtns.forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-view') === mode);
        });
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
        mapView = view;
        mapViewBtns.forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-map-view') === view);
        });
        const mapElement = document.getElementById('map');
        if (mapElement) {
            mapElement.className = 'map ' + view + '-view';
        }
    }

    function updateMainTabSlider() {
        if (mainTabsSlider) {
            const activeIndex = Array.from(mainTabBtns).findIndex(btn => btn.classList.contains('active'));
            mainTabsSlider.style.transform = `translateX(${activeIndex * 100}%)`;
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
            slider.style.transform = `translateX(${activeIndex * 100}%)`;
        }
    }

    renderActiveOrdersAndMap();

    // Observe changes in order list for dynamic updates
    const observer = new MutationObserver(updateActiveOrdersCount);
    observer.observe(orderList, { childList: true });
});