console.log('orders.js loaded');

document.addEventListener('DOMContentLoaded', function() {
    console.log("Orders page script initialized");

    // Initialize map controls on page load
    setTimeout(() => {
        initMapControlButtons();
    }, 500);

    // Global object to store vehicle positions for each order
    // This ensures consistent vehicle positions across map views
    window.vehiclePositions = {};

    // Global array to track all vehicle markers
    window.allVehicleMarkers = [];

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
    let modalMapInitialized = false;

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
    const mapModal = document.getElementById('mapModal');
    const mapModalClose = document.querySelector('.map-modal-close');
    const modalOrderMap = document.getElementById('modalOrderMap');
    const mapLoadingOverlay = document.getElementById('mapLoadingOverlay');

    // Initialize tabs
    initTabs();

    // Initialize event listeners
    initEventListeners();

    // Initialize map controls
    initMapControlButtons();

    // Initialize progress bars immediately
    initializeProgressBars();

    // Initialize past order event listeners
    initPastOrderEventListeners();

    // Function to initialize past order event listeners
    function initPastOrderEventListeners() {
        // Handle hide details links
        document.querySelectorAll('.hide-details-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const orderId = this.getAttribute('data-order-id');
                const detailsSection = document.getElementById(`order-details-${orderId}`);
                if (detailsSection) {
                    detailsSection.classList.remove('active');
                }
            });
        });

        // Handle "more items" links
        document.querySelectorAll('.more-items-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Get the parent container and additional items container
                const container = this.closest('.expandable-items-container');
                const additionalItems = container.querySelector('.additional-items');

                if (additionalItems) {
                    // Toggle the display of additional items with animation
                    if (additionalItems.style.display === 'none' || !additionalItems.style.display) {
                        // Show additional items
                        additionalItems.style.display = 'block';
                        additionalItems.style.maxHeight = '0';
                        additionalItems.style.opacity = '0';

                        // Force reflow
                        void additionalItems.offsetHeight;

                        // Animate expansion
                        additionalItems.style.maxHeight = additionalItems.scrollHeight + 'px';
                        additionalItems.style.opacity = '1';

                        // Move the link to the bottom of the container
                        container.appendChild(link);

                        // Change the text to "Show less"
                        const moreItemsText = this.querySelector('.more-items');
                        if (moreItemsText) {
                            moreItemsText.innerHTML = '<i class="fas fa-chevron-up"></i> Show less';
                        }
                    } else {
                        // Hide additional items with animation
                        additionalItems.style.maxHeight = '0';
                        additionalItems.style.opacity = '0';

                        // After animation completes, set display to none
                        setTimeout(() => {
                            additionalItems.style.display = 'none';

                            // Move the link back to its original position (after the additional items)
                            container.insertBefore(link, null);

                            // Change text back to "more items"
                            const moreItemsText = this.querySelector('.more-items');
                            if (moreItemsText) {
                                const totalItems = parseInt(this.getAttribute('data-total-items') || '0');
                                const visibleItems = 2; // Number of items shown by default
                                moreItemsText.innerHTML = `+${totalItems - visibleItems} more items`;
                            }
                        }, 300); // Match this with the CSS transition duration
                    }
                }
            });
        });

        // Handle past order action buttons
        document.querySelectorAll('.past-order-card .action-button').forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.stopPropagation();
                const orderId = this.getAttribute('data-order-id');

                if (this.classList.contains('reorder-button')) {
                    // Reorder functionality
                    console.log(`Reordering items from order ${orderId}`);

                    try {
                        // Fetch order details to get items
                        const orderData = await fetchOrderDetails(orderId);

                        if (orderData && orderData.items && orderData.items.length > 0) {
                            // Show loading state
                            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Processing...</span>';
                            this.disabled = true;

                            // Add items to cart (this would need to be implemented)
                            // For now, just redirect to menu page after a delay
                            setTimeout(() => {
                                window.location.href = '/ayskrimWebsite/customerPage/menu/menu.php';
                            }, 1500);
                        } else {
                            alert('Could not find items for this order.');
                        }
                    } catch (error) {
                        console.error('Error reordering:', error);
                        alert('Failed to process reorder. Please try again.');
                        this.innerHTML = '<i class="fas fa-shopping-cart"></i><span>Order Again</span>';
                        this.disabled = false;
                    }
                }
            });
        });

        // Handle star rating system
        document.querySelectorAll('.star-rating-stars i').forEach(star => {
            // Hover effect
            star.addEventListener('mouseover', function() {
                const rating = parseInt(this.getAttribute('data-rating'));
                const stars = this.parentElement.querySelectorAll('i');

                // Reset all stars
                stars.forEach(s => s.classList.remove('hover'));

                // Highlight stars up to the hovered one
                stars.forEach((s, index) => {
                    if (index < rating) {
                        s.classList.add('hover');
                    }
                });
            });

            // Remove hover effect when mouse leaves the stars container
            star.parentElement.addEventListener('mouseleave', function() {
                const stars = this.querySelectorAll('i');
                stars.forEach(s => s.classList.remove('hover'));
            });

            // Handle click on star
            star.addEventListener('click', function() {
                const rating = parseInt(this.getAttribute('data-rating'));
                const stars = this.parentElement.querySelectorAll('i');
                const ratingContainer = this.closest('.star-rating');
                const orderId = ratingContainer.getAttribute('data-order-id');
                const ratingValue = ratingContainer.querySelector('.star-rating-value');

                // Update visual state
                stars.forEach((s, index) => {
                    if (index < rating) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });

                // Update rating value display
                ratingValue.textContent = rating + '.0';

                // Show review input if not already visible
                const reviewContainer = ratingContainer.nextElementSibling;
                if (!reviewContainer.classList.contains('has-review')) {
                    const reviewInput = reviewContainer.querySelector('.review-input');
                    if (reviewInput) {
                        reviewInput.focus();
                    }
                }

                console.log(`Set rating ${rating} for order ${orderId}`);

                // In a real implementation, you would send this rating to the server
                // For now, we'll just show a message
                // submitRating(orderId, rating);
            });
        });

        // Handle review submission
        document.querySelectorAll('.submit-review-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order-id');
                const reviewContainer = this.closest('.review-container');
                const reviewInput = reviewContainer.querySelector('.review-input');
                const review = reviewInput.value.trim();

                if (review) {
                    console.log(`Submitting review for order ${orderId}: "${review}"`);

                    // Show loading state
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
                    this.disabled = true;

                    // In a real implementation, you would send this review to the server
                    // For now, we'll just simulate a successful submission
                    setTimeout(() => {
                        // Create review text element
                        reviewContainer.innerHTML = `
                            <div class="review-text">"${review}"</div>
                            <button class="edit-review-btn" data-order-id="${orderId}">
                                <i class="fas fa-edit"></i> Edit Review
                            </button>
                        `;

                        // Add has-review class
                        reviewContainer.classList.add('has-review');

                        // Add event listener to the new edit button
                        const editBtn = reviewContainer.querySelector('.edit-review-btn');
                        if (editBtn) {
                            editBtn.addEventListener('click', handleEditReview);
                        }
                    }, 1000);
                } else {
                    alert('Please enter a review before submitting.');
                }
            });
        });

        // Handle edit review button
        document.querySelectorAll('.edit-review-btn').forEach(btn => {
            btn.addEventListener('click', handleEditReview);
        });

        // Function to handle edit review button click
        function handleEditReview() {
            const orderId = this.getAttribute('data-order-id');
            const reviewContainer = this.closest('.review-container');
            const reviewText = reviewContainer.querySelector('.review-text').textContent.replace(/^"|"$/g, '');

            // Replace with input
            reviewContainer.innerHTML = `
                <div class="review-input-container">
                    <textarea class="review-input" rows="2">${reviewText}</textarea>
                    <button class="submit-review-btn" data-order-id="${orderId}">
                        <i class="fas fa-paper-plane"></i> Update
                    </button>
                </div>
            `;

            // Focus on the input
            const reviewInput = reviewContainer.querySelector('.review-input');
            if (reviewInput) {
                reviewInput.focus();
            }

            // Add event listener to the new submit button
            const submitBtn = reviewContainer.querySelector('.submit-review-btn');
            if (submitBtn) {
                submitBtn.addEventListener('click', function() {
                    const newReview = reviewInput.value.trim();

                    if (newReview) {
                        console.log(`Updating review for order ${orderId}: "${newReview}"`);

                        // Show loading state
                        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                        this.disabled = true;

                        // In a real implementation, you would send this review to the server
                        // For now, we'll just simulate a successful update
                        setTimeout(() => {
                            // Create review text element
                            reviewContainer.innerHTML = `
                                <div class="review-text">"${newReview}"</div>
                                <button class="edit-review-btn" data-order-id="${orderId}">
                                    <i class="fas fa-edit"></i> Edit Review
                                </button>
                            `;

                            // Add event listener to the new edit button
                            const editBtn = reviewContainer.querySelector('.edit-review-btn');
                            if (editBtn) {
                                editBtn.addEventListener('click', handleEditReview);
                            }
                        }, 1000);
                    } else {
                        alert('Please enter a review before submitting.');
                    }
                });
            }
        }
    }

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

                    // If we have a map instance, make sure it's properly sized and centered
                    if (window.orderMap) {
                        setTimeout(() => {
                            window.orderMap.invalidateSize();
                            if (selectedOrderId) {
                                centerMapOnOrder(selectedOrderId);
                            } else if (window.orderMap.__markers && window.orderMap.__markers.length > 0) {
                                const group = new L.featureGroup(window.orderMap.__markers.map(m => m.marker));
                                window.orderMap.fitBounds(group.getBounds(), { padding: [50, 50] });
                            }
                        }, 100);
                    }
                } else {
                    await switchToDetailsView();
                }
            });
        });
    }

    // Sort menu toggle functionality
    const sortButtons = document.querySelectorAll('.sort-button');
    const sortMenus = document.querySelectorAll('.sort-menu');

    // Store original order of items for each container
    const originalOrders = {};

    // Initialize original orders
    function storeOriginalOrders() {
        // Store original order for past orders
        const pastOrdersContainer = document.querySelector('.past-order-cards');
        if (pastOrdersContainer) {
            const pastOrderItems = Array.from(pastOrdersContainer.querySelectorAll('.past-order-card'));
            originalOrders['orders'] = pastOrderItems.map(item => item.cloneNode(true));
        }

        // Store original order for upcoming bookings
        const upcomingBookingsContainer = document.querySelector('.upcoming-bookings .booking-cards');
        if (upcomingBookingsContainer) {
            const upcomingBookingItems = Array.from(upcomingBookingsContainer.querySelectorAll('.booking-card'));
            originalOrders['upcomingbookings'] = upcomingBookingItems.map(item => item.cloneNode(true));
        }

        // Store original order for past bookings
        const pastBookingsContainer = document.querySelector('.past-bookings .booking-cards');
        if (pastBookingsContainer) {
            const pastBookingItems = Array.from(pastBookingsContainer.querySelectorAll('.booking-card'));
            originalOrders['pastbookings'] = pastBookingItems.map(item => item.cloneNode(true));
        }
    }

    // Call this function when the page loads
    storeOriginalOrders();

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
            const buttonId = this.closest('.sort-menu').id.replace('Menu', 'Button');
            const button = document.getElementById(buttonId);

            if (sortType === 'clear') {
                // Reset to original order
                resetToOriginalOrder(containerId);

                // Reset button text
                if (button) {
                    button.innerHTML = `<i class="fas fa-sort"></i><span>Sort</span>`;
                }
            } else {
                // Sort items
                sortItems(sortType, containerId);

                // Update button text
                const optionText = this.querySelector('span').textContent;
                if (button) {
                    button.innerHTML = `<i class="fas fa-sort"></i><span>${optionText}</span>`;
                }
            }

            this.closest('.sort-menu').classList.remove('active');
        });
    });

    function resetToOriginalOrder(containerId) {
        const containerSelector = containerId === 'orders' ? '.past-order-cards' :
                                 (containerId === 'upcomingbookings' ? '.upcoming-bookings .booking-cards' :
                                 '.past-bookings .booking-cards');
        const container = document.querySelector(containerSelector);

        if (!container || !originalOrders[containerId]) return;

        // Clear container
        container.innerHTML = '';

        // Re-append original items
        originalOrders[containerId].forEach(item => {
            container.appendChild(item.cloneNode(true));
        });

        // Re-initialize event listeners
        if (containerId !== 'orders') {
            initBookingDetailsToggle();
        } else {
            // Re-initialize event listeners for past orders
            initOrderItemEventListeners();

            // Re-initialize more items links
            initMoreItemsLinks();

            // Re-initialize star rating
            initStarRating();
        }
    }

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
                // Parse dates correctly for both orders and bookings
                let dateA, dateB;

                if (containerId === 'orders') {
                    dateA = new Date(a.querySelector('.order-date').textContent);
                    dateB = new Date(b.querySelector('.order-date').textContent);
                } else {
                    // For bookings, parse date in format "May 5, 2025"
                    const dateTextA = a.querySelector('.booking-title-overlay p').textContent;
                    const dateTextB = b.querySelector('.booking-title-overlay p').textContent;

                    dateA = parseBookingDate(dateTextA);
                    dateB = parseBookingDate(dateTextB);
                }

                return dateB - dateA;
            } else if (sortType === 'date-oldest') {
                // Parse dates correctly for both orders and bookings
                let dateA, dateB;

                if (containerId === 'orders') {
                    dateA = new Date(a.querySelector('.order-date').textContent);
                    dateB = new Date(b.querySelector('.order-date').textContent);
                } else {
                    // For bookings, parse date in format "May 5, 2025"
                    const dateTextA = a.querySelector('.booking-title-overlay p').textContent;
                    const dateTextB = b.querySelector('.booking-title-overlay p').textContent;

                    dateA = parseBookingDate(dateTextA);
                    dateB = parseBookingDate(dateTextB);
                }

                return dateA - dateB;
            } else if (sortType === 'price-high') {
                try {
                    // Try to get price for both orders and bookings
                    let priceA, priceB;

                    if (containerId === 'orders') {
                        // For orders, get price from the summary total
                        const priceElA = a.querySelector('.summary-row.total span:last-child');
                        const priceElB = b.querySelector('.summary-row.total span:last-child');

                        if (priceElA && priceElB) {
                            priceA = parseFloat(priceElA.textContent.replace('₱', '').replace(',', ''));
                            priceB = parseFloat(priceElB.textContent.replace('₱', '').replace(',', ''));
                            return priceB - priceA;
                        }
                    } else {
                        // For bookings, get price from the booking details
                        const priceElA = a.querySelector('.booking-detail-row .detail-value.price');
                        const priceElB = b.querySelector('.booking-detail-row .detail-value.price');

                        if (priceElA && priceElB) {
                            priceA = parseFloat(priceElA.textContent.replace('₱', '').replace(',', ''));
                            priceB = parseFloat(priceElB.textContent.replace('₱', '').replace(',', ''));
                            return priceB - priceA;
                        }
                    }
                } catch (error) {
                    console.error('Error sorting by price:', error);
                }
                return 0;
            } else if (sortType === 'price-low') {
                try {
                    // Try to get price for both orders and bookings
                    let priceA, priceB;

                    if (containerId === 'orders') {
                        // For orders, get price from the summary total
                        const priceElA = a.querySelector('.summary-row.total span:last-child');
                        const priceElB = b.querySelector('.summary-row.total span:last-child');

                        if (priceElA && priceElB) {
                            priceA = parseFloat(priceElA.textContent.replace('₱', '').replace(',', ''));
                            priceB = parseFloat(priceElB.textContent.replace('₱', '').replace(',', ''));
                            return priceA - priceB;
                        }
                    } else {
                        // For bookings, get price from the booking details
                        const priceElA = a.querySelector('.booking-detail-row .detail-value.price');
                        const priceElB = b.querySelector('.booking-detail-row .detail-value.price');

                        if (priceElA && priceElB) {
                            priceA = parseFloat(priceElA.textContent.replace('₱', '').replace(',', ''));
                            priceB = parseFloat(priceElB.textContent.replace('₱', '').replace(',', ''));
                            return priceA - priceB;
                        }
                    }
                } catch (error) {
                    console.error('Error sorting by price:', error);
                }
                return 0;
            } else if (sortType === 'status') {
                // Sort by status (for bookings)
                if (containerId !== 'orders') {
                    const statusA = a.querySelector('.status-badge').textContent.trim();
                    const statusB = b.querySelector('.status-badge').textContent.trim();
                    return statusA.localeCompare(statusB);
                }
                return 0;
            } else if (sortType === 'package') {
                // Sort by package type (for bookings)
                if (containerId !== 'orders') {
                    const packageA = a.querySelector('.booking-title-overlay h4').textContent.trim();
                    const packageB = b.querySelector('.booking-title-overlay h4').textContent.trim();
                    return packageA.localeCompare(packageB);
                }
                return 0;
            }
            return 0;
        });

        // Clear container and re-append sorted items
        container.innerHTML = '';
        itemsArray.forEach(item => {
            container.appendChild(item);
        });

        // Re-initialize event listeners for the sorted items
        if (containerId !== 'orders') {
            initBookingDetailsToggle();
        } else {
            // Re-initialize event listeners for past orders
            initOrderItemEventListeners();

            // Re-initialize more items links
            initMoreItemsLinks();

            // Re-initialize star rating
            initStarRating();
        }
    }

    // Function to initialize more items links
    function initMoreItemsLinks() {
        document.querySelectorAll('.more-items-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const orderId = this.getAttribute('data-order-id');
                const additionalItems = this.closest('.expandable-items-container').querySelector('.additional-items');

                if (additionalItems) {
                    if (additionalItems.style.display === 'none' || !additionalItems.style.display) {
                        additionalItems.style.display = 'block';
                        this.querySelector('.more-items').textContent = 'Show less';
                    } else {
                        additionalItems.style.display = 'none';
                        const totalItems = parseInt(this.getAttribute('data-total-items'));
                        this.querySelector('.more-items').textContent = `+${totalItems - 2} more items`;
                    }
                }
            });
        });
    }

    // Initialize more items links on page load
    initMoreItemsLinks();

    // Function to initialize star rating
    function initStarRating() {
        document.querySelectorAll('.star-rating-stars i').forEach(star => {
            star.addEventListener('click', function() {
                const rating = parseInt(this.getAttribute('data-rating'));
                const starRating = this.closest('.star-rating');
                const stars = starRating.querySelectorAll('i');
                const ratingValue = starRating.querySelector('.star-rating-value');

                // Update stars
                stars.forEach(s => {
                    if (parseInt(s.getAttribute('data-rating')) <= rating) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });

                // Update rating value
                if (ratingValue) {
                    ratingValue.textContent = rating + '.0';
                }
            });
        });
    }

    // Initialize star rating on page load
    initStarRating();

    // Helper function to parse booking date in format "May 5, 2025"
    function parseBookingDate(dateText) {
        try {
            const dateParts = dateText.split(', ');
            if (dateParts.length !== 2) return new Date(0); // Invalid date

            const year = parseInt(dateParts[1]);
            const monthDay = dateParts[0].split(' ');
            if (monthDay.length !== 2) return new Date(0); // Invalid date

            const month = getMonthNumber(monthDay[0]) - 1; // JavaScript months are 0-indexed
            const day = parseInt(monthDay[1]);

            return new Date(year, month, day);
        } catch (error) {
            console.error('Error parsing booking date:', error);
            return new Date(0); // Return epoch date as fallback
        }
    }

    // Add search functionality for past orders
    const pastOrdersSearchBar = document.querySelector('.past-orders-container .search-bar input');
    if (pastOrdersSearchBar) {
        pastOrdersSearchBar.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const orderCards = document.querySelectorAll('.past-order-card');

            orderCards.forEach(card => {
                const orderNumber = card.querySelector('.order-number').textContent.toLowerCase();
                const orderAddress = card.querySelector('.order-address span')?.textContent.toLowerCase() || '';

                // Get order items text if available
                let orderItemsText = '';
                const orderItems = card.querySelectorAll('.item-name');
                if (orderItems.length > 0) {
                    orderItems.forEach(item => {
                        orderItemsText += item.textContent.toLowerCase() + ' ';
                    });
                }

                // Get comment text if available
                const orderCommentEl = card.querySelector('.order-comment');
                const orderComment = orderCommentEl ? orderCommentEl.textContent.toLowerCase() : '';

                if (orderNumber.includes(searchTerm) ||
                    orderAddress.includes(searchTerm) ||
                    orderItemsText.includes(searchTerm) ||
                    orderComment.includes(searchTerm)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    // Initialize booking details toggle functionality
    function initBookingDetailsToggle() {
        // Toggle booking details
        const toggleDetailsLinks = document.querySelectorAll('.toggle-details');
        const hideDetailsLinks = document.querySelectorAll('.hide-details-link');

        toggleDetailsLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const bookingId = this.getAttribute('data-booking');
                const detailsSection = document.getElementById(`booking-details-${bookingId}`);

                if (!detailsSection) {
                    console.warn(`Details section not found for booking ${bookingId}`);
                    return;
                }

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

                if (!detailsSection) {
                    console.warn(`Details section not found for booking ${bookingId}`);
                    return;
                }

                detailsSection.classList.remove('active');
                const viewDetailsLink = document.querySelector(`.toggle-details[data-booking="${bookingId}"]`);
                if (viewDetailsLink) {
                    const icon = viewDetailsLink.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-chevron-down');
                        icon.classList.add('fa-chevron-right');
                    }
                    viewDetailsLink.textContent = 'View details';
                }
            });
        });
    }

    // Initialize booking details toggle on page load
    initBookingDetailsToggle();

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

    // Handle booking action buttons
    document.querySelectorAll('.booking-actions .action-button').forEach(btn => {
        btn.addEventListener('click', function() {
            let actionType = '';
            if (this.classList.contains('reschedule-btn')) {
                actionType = 'reschedule';
            } else if (this.classList.contains('cancel-btn')) {
                actionType = 'cancel';
            } else if (this.classList.contains('book-again-btn')) {
                actionType = 'book again';
            }

            const card = this.closest('.booking-card');
            const bookingTitle = card.querySelector('.booking-title-overlay h4').textContent;
            console.log(`Action: ${actionType} for booking "${bookingTitle}"`);

            // Get the booking ID from the card
            const bookingId = card.querySelector('.toggle-details')?.getAttribute('data-booking') ||
                             card.querySelector('.hide-details-link')?.getAttribute('data-booking');

            if (!bookingId) {
                console.error('Could not find booking ID');
                return;
            }

            // Remove "past-" prefix if it exists (for past bookings)
            const cleanBookingId = bookingId.replace('past-', '');

            if (actionType === 'reschedule') {
                openRescheduleModal(cleanBookingId, card);
            } else if (actionType === 'cancel') {
                openCancelConfirmation(cleanBookingId, bookingTitle);
            } else if (actionType === 'book again') {
                // Redirect to menu page with booking form
                window.location.href = '/ayskrimWebsite/customerPage/menu/menu.php?section=booking';
            }
        });
    });

    // Handle past order action buttons
    document.querySelectorAll('.past-order-card .action-button').forEach(btn => {
        btn.addEventListener('click', async function() {
            const orderId = this.getAttribute('data-order-id');

            if (this.classList.contains('details-button')) {
                // View order details
                console.log(`Viewing details for order ${orderId}`);

                // Select the order and show details
                selectOrder(orderId);

                // Ensure "Order Details" toggle is active
                const mapViewOptions = document.querySelectorAll('.map-view-option');
                mapViewOptions.forEach(opt => opt.classList.remove('active'));
                const detailsOption = Array.from(mapViewOptions).find(opt => opt.textContent.trim() === 'Order Details');
                if (detailsOption) detailsOption.classList.add('active');

                await switchToDetailsView();
                scrollToMap();

            } else if (this.classList.contains('reorder-button')) {
                // Reorder functionality
                console.log(`Reordering items from order ${orderId}`);

                try {
                    // Fetch order details to get items
                    const orderData = await fetchOrderDetails(orderId);

                    if (orderData && orderData.items && orderData.items.length > 0) {
                        // Show loading state
                        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Processing...</span>';
                        this.disabled = true;

                        // Add items to cart (this would need to be implemented)
                        // For now, just redirect to menu page after a delay
                        setTimeout(() => {
                            window.location.href = '/ayskrimWebsite/customerPage/menu/menu.php';
                        }, 1500);
                    } else {
                        alert('Could not find items for this order.');
                    }
                } catch (error) {
                    console.error('Error reordering:', error);
                    alert('Failed to process reorder. Please try again.');
                    this.innerHTML = '<i class="fas fa-shopping-cart"></i><span>Order Again</span>';
                    this.disabled = false;
                }
            }
        });
    });

    // Handle rate order buttons
    document.querySelectorAll('.rate-order-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            console.log(`Rating order ${orderId}`);

            // For now, just show an alert
            alert('Rating functionality will be implemented in a future update.');
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

    // === BOOKINGS INTEGRATION ===
    async function fetchUserBookings() {
        try {
            const res = await fetch('/ayskrimWebsite/api/events/fetchUserBookings.php');
            const data = await res.json();
            return data.bookings || { upcoming: [], past: [] };
        } catch (error) {
            console.error('Error fetching bookings:', error);
            return { upcoming: [], past: [] };
        }
    }

    // Function to open the reschedule modal with booking details
    function openRescheduleModal(bookingId, bookingCard) {
        // Get booking details from the card
        const dateText = bookingCard.querySelector('.booking-title-overlay p').textContent;
        const timeText = bookingCard.querySelector('.booking-detail:nth-child(1) span').textContent;

        // Parse date (format: "May 5, 2025")
        const dateParts = dateText.split(', ');
        const year = dateParts[1];
        const monthDay = dateParts[0].split(' ');
        const month = getMonthNumber(monthDay[0]);
        const day = monthDay[1];

        // Format date for input (YYYY-MM-DD)
        const formattedDate = `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;

        // Parse time (format: "2:00 PM - 6:00 PM")
        const timeParts = timeText.split(' - ');
        const startTime = convertTo24Hour(timeParts[0]);
        const endTime = convertTo24Hour(timeParts[1]);

        // Set minimum date to today
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        const formattedToday = `${yyyy}-${mm}-${dd}`;

        // Set values in the form
        document.getElementById('reschedule_event_id').value = bookingId;
        const dateInput = document.getElementById('reschedule_event_date');
        dateInput.value = formattedDate;
        dateInput.setAttribute('min', formattedToday);
        document.getElementById('reschedule_start_time').value = startTime;
        document.getElementById('reschedule_end_time').value = endTime;

        // Show the modal
        const modal = document.getElementById('rescheduleModal');
        modal.style.display = 'flex';

        // Add event listeners for the modal buttons
        document.getElementById('close-reschedule-modal').addEventListener('click', closeRescheduleModal);
        document.getElementById('cancel-reschedule-btn').addEventListener('click', closeRescheduleModal);
        document.getElementById('confirm-reschedule-btn').addEventListener('click', submitReschedule);

        // Close modal when clicking outside
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeRescheduleModal();
            }
        });
    }

    // Function to close the reschedule modal
    function closeRescheduleModal() {
        const modal = document.getElementById('rescheduleModal');
        modal.style.display = 'none';

        // Remove event listeners
        document.getElementById('close-reschedule-modal').removeEventListener('click', closeRescheduleModal);
        document.getElementById('cancel-reschedule-btn').removeEventListener('click', closeRescheduleModal);
        document.getElementById('confirm-reschedule-btn').removeEventListener('click', submitReschedule);
    }

    // Function to submit the reschedule form
    async function submitReschedule() {
        // Get form data
        const eventId = document.getElementById('reschedule_event_id').value;
        const eventDate = document.getElementById('reschedule_event_date').value;
        const startTime = document.getElementById('reschedule_start_time').value;
        const endTime = document.getElementById('reschedule_end_time').value;

        // Validate form data
        if (!eventId || !eventDate || !startTime || !endTime) {
            alert('Please fill in all required fields');
            return;
        }

        // Validate that end time is after start time
        if (startTime >= endTime) {
            alert('End time must be after start time');
            return;
        }

        // Show loading state
        const confirmBtn = document.getElementById('confirm-reschedule-btn');
        const originalText = confirmBtn.textContent;
        confirmBtn.textContent = 'Processing...';
        confirmBtn.disabled = true;

        try {
            // Call the API to reschedule the booking
            const response = await fetch('/ayskrimWebsite/api/events/rescheduleBooking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    event_id: eventId,
                    event_date: eventDate,
                    start_time: startTime,
                    end_time: endTime
                })
            });

            const data = await response.json();

            if (data.success) {
                // Close the modal
                closeRescheduleModal();

                // Show success message
                alert('Booking rescheduled successfully! We will contact you to confirm the new date and time.');

                // Reload the page to show updated booking
                window.location.reload();
            } else {
                // Show error message
                alert(`Failed to reschedule booking: ${data.error}`);

                // Reset button
                confirmBtn.textContent = originalText;
                confirmBtn.disabled = false;
            }
        } catch (error) {
            console.error('Error rescheduling booking:', error);
            alert('An error occurred while rescheduling your booking. Please try again.');

            // Reset button
            confirmBtn.textContent = originalText;
            confirmBtn.disabled = false;
        }
    }

    // Function to open the cancel confirmation modal
    function openCancelConfirmation(bookingId, bookingTitle) {
        // Set confirmation message
        document.getElementById('confirmation-message').textContent = `Are you sure you want to cancel your booking for "${bookingTitle}"? This action cannot be undone.`;

        // Store booking ID for the confirm action
        document.getElementById('confirm-action-btn').setAttribute('data-booking-id', bookingId);

        // Show the modal
        const modal = document.getElementById('confirmationModal');
        modal.style.display = 'flex';

        // Add event listeners for the modal buttons
        document.getElementById('close-confirmation-modal').addEventListener('click', closeConfirmationModal);
        document.getElementById('cancel-confirmation-btn').addEventListener('click', closeConfirmationModal);
        document.getElementById('confirm-action-btn').addEventListener('click', confirmCancelBooking);

        // Close modal when clicking outside
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeConfirmationModal();
            }
        });
    }

    // Function to close the confirmation modal
    function closeConfirmationModal() {
        const modal = document.getElementById('confirmationModal');
        modal.style.display = 'none';

        // Remove event listeners
        document.getElementById('close-confirmation-modal').removeEventListener('click', closeConfirmationModal);
        document.getElementById('cancel-confirmation-btn').removeEventListener('click', closeConfirmationModal);
        document.getElementById('confirm-action-btn').removeEventListener('click', confirmCancelBooking);
    }

    // Function to confirm and process booking cancellation
    async function confirmCancelBooking() {
        // Get booking ID
        const bookingId = document.getElementById('confirm-action-btn').getAttribute('data-booking-id');

        if (!bookingId) {
            alert('Booking ID not found');
            closeConfirmationModal();
            return;
        }

        // Show loading state
        const confirmBtn = document.getElementById('confirm-action-btn');
        const originalText = confirmBtn.textContent;
        confirmBtn.textContent = 'Processing...';
        confirmBtn.disabled = true;

        try {
            // Call the API to cancel the booking
            const response = await fetch('/ayskrimWebsite/api/events/cancelBooking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    event_id: bookingId
                })
            });

            const data = await response.json();

            if (data.success) {
                // Close the modal
                closeConfirmationModal();

                // Show success message
                alert('Booking cancelled successfully!');

                // Reload the page to show updated booking
                window.location.reload();
            } else {
                // Show error message
                alert(`Failed to cancel booking: ${data.error}`);

                // Reset button
                confirmBtn.textContent = originalText;
                confirmBtn.disabled = false;
            }
        } catch (error) {
            console.error('Error cancelling booking:', error);
            alert('An error occurred while cancelling your booking. Please try again.');

            // Reset button
            confirmBtn.textContent = originalText;
            confirmBtn.disabled = false;
        }
    }

    // Helper function to get month number from month name
    function getMonthNumber(monthName) {
        const months = {
            'January': 1, 'February': 2, 'March': 3, 'April': 4, 'May': 5, 'June': 6,
            'July': 7, 'August': 8, 'September': 9, 'October': 10, 'November': 11, 'December': 12,
            'Jan': 1, 'Feb': 2, 'Mar': 3, 'Apr': 4, 'May': 5, 'Jun': 6,
            'Jul': 7, 'Aug': 8, 'Sep': 9, 'Oct': 10, 'Nov': 11, 'Dec': 12
        };
        return months[monthName] || 1;
    }

    // Helper function to convert 12-hour time to 24-hour time
    function convertTo24Hour(time12h) {
        const [time, modifier] = time12h.split(' ');
        let [hours, minutes] = time.split(':');

        if (hours === '12') {
            hours = '00';
        }

        if (modifier === 'PM') {
            hours = parseInt(hours, 10) + 12;
        }

        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
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

        // Show map loading overlay
        if (mapLoadingOverlay) {
            mapLoadingOverlay.style.display = 'flex';
        }

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

            // Hide map loading overlay
            if (mapLoadingOverlay) {
                mapLoadingOverlay.style.display = 'none';
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
            zoomControl: false, // We'll use our custom zoom controls
            attributionControl: true,
            scrollWheelZoom: true,
            dragging: true,
            doubleClickZoom: true,
            boxZoom: true,
            keyboard: true,
            tap: true
        });
        window.orderMap = map;

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

        // Add different map tile layers
        const standardLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        });

        const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: '© <a href="https://www.esri.com/">Esri</a> | © Maxar | © Earthstar Geographics'
        });

        const trafficLayer = L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors | <a href="https://www.hotosm.org/">HOT</a>'
        });

        // Store layers for later use
        window.orderMap.__layers = {
            standard: standardLayer,
            satellite: satelliteLayer,
            traffic: trafficLayer
        };

        // Add the default layer (standard)
        standardLayer.addTo(map);
        const markers = [];
        // IMPORTANT: We need to make sure we're only showing the selected order
        // First, clear any existing vehicle markers to prevent duplicates
        removeAllVehicleMarkers();

        // Only add the marker for the selected order if one is selected
        if (selectedOrderId) {
            console.log(`Initializing map with selected order: ${selectedOrderId}`);
            const selectedOrder = validOrders.find(order => order.id === parseInt(selectedOrderId));
            if (selectedOrder) {
                const marker = L.marker([selectedOrder.coords.lat, selectedOrder.coords.lon]).addTo(map)
                    .bindPopup(`Order #${selectedOrder.id}<br>${selectedOrder.shipping_address}`);
                markers.push({ orderId: selectedOrder.id, marker, coords: selectedOrder.coords });
                map.setView([selectedOrder.coords.lat, selectedOrder.coords.lon], 16);
                marker.openPopup();
            }
        } else {
            // If no order is selected, add all markers but don't show them
            console.log("No order selected, adding all markers");
            validOrders.forEach((order) => {
                const marker = L.marker([order.coords.lat, order.coords.lon]).addTo(map)
                    .bindPopup(`Order #${order.id}<br>${order.shipping_address}`);
                markers.push({ orderId: order.id, marker, coords: order.coords });
            });

            // Store all markers but only show the first one
            if (markers.length > 0) {
                // Auto-select the first order
                selectedOrderId = validOrders[0].id;
                console.log(`Auto-selecting first order: ${selectedOrderId}`);

                map.setView([markers[0].coords.lat, markers[0].coords.lon], 16);
                markers[0].marker.openPopup();
            }
        }

        window.orderMap.__markers = markers; // Store markers for access

        // Reset modal map initialization flag when main map is reinitialized
        modalMapInitialized = false;

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

        // Initialize map control buttons
        initMapControlButtons();

        // Hide map loading overlay
        if (mapLoadingOverlay) {
            mapLoadingOverlay.style.display = 'none';
        }
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
        console.log(`Selecting order: ${orderId}, previous: ${selectedOrderId}`);

        // If the selected order is changing, we need to do a complete reset
        if (selectedOrderId !== orderId) {
            console.log(`Order changed from ${selectedOrderId} to ${orderId}`);

            // Reset modal map initialization flag
            modalMapInitialized = false;

            // Clean up animation intervals when switching orders
            if (window.vehicleAnimationInterval) {
                clearInterval(window.vehicleAnimationInterval);
                window.vehicleAnimationInterval = null;
            }

            if (window.modalVehicleAnimationInterval) {
                clearInterval(window.modalVehicleAnimationInterval);
                window.modalVehicleAnimationInterval = null;
            }

            // IMPORTANT: Remove all vehicle markers from both maps
            // This is critical to ensure no stale markers remain
            try {
                // Call our improved function to remove ALL vehicle markers
                // We don't pass an order ID because we want to remove ALL markers
                removeAllVehicleMarkers();

                // Also remove any other non-tile layers from both maps
                // This ensures a completely clean slate
                if (window.orderMap) {
                    console.log("Removing all non-tile layers from main map");
                    window.orderMap.eachLayer(layer => {
                        if (!(layer instanceof L.TileLayer)) {
                            window.orderMap.removeLayer(layer);
                        }
                    });
                }

                if (window.modalOrderMap) {
                    console.log("Removing all non-tile layers from modal map");
                    window.modalOrderMap.eachLayer(layer => {
                        if (!(layer instanceof L.TileLayer)) {
                            window.modalOrderMap.removeLayer(layer);
                        }
                    });
                }

            } catch (error) {
                console.error("Error during complete map reset:", error);
            }
        }

        // Update the selected order ID
        selectedOrderId = orderId;

        // Check if this order should have a vehicle marker based on its status
        const selectedOrder = activeOrdersData.find(o => o.id === parseInt(orderId));
        if (selectedOrder) {
            const normalizedStatus = selectedOrder.order_status?.trim().toLowerCase() || '';
            console.log(`Selected order ${orderId} has status: "${selectedOrder.order_status}" (normalized: "${normalizedStatus}")`);

            // If the order is not "out for delivery", ensure we remove any vehicle markers
            if (normalizedStatus !== "out for delivery") {
                console.log(`Order ${orderId} should NOT have a vehicle marker - removing any that might exist`);

                // Remove any vehicle markers that might exist for this order
                if (window.orderMap) {
                    window.orderMap.eachLayer(layer => {
                        if (layer._vehicleMarker && layer._orderId && parseInt(layer._orderId) === parseInt(orderId)) {
                            console.log(`Removing incorrect vehicle marker for order ${orderId} from main map`);
                            window.orderMap.removeLayer(layer);
                        }
                    });
                }

                if (window.modalOrderMap) {
                    window.modalOrderMap.eachLayer(layer => {
                        if (layer._vehicleMarker && layer._orderId && parseInt(layer._orderId) === parseInt(orderId)) {
                            console.log(`Removing incorrect vehicle marker for order ${orderId} from modal map`);
                            window.modalOrderMap.removeLayer(layer);
                        }
                    });
                }

                // Also remove from the global array
                window.allVehicleMarkers = window.allVehicleMarkers.filter(marker =>
                    !(marker._orderId && parseInt(marker._orderId) === parseInt(orderId))
                );
            }
        }

        // Update UI for all order items
        document.querySelectorAll('.order-item').forEach(el => {
            const isSelected = el.getAttribute('data-order-id') === orderId;
            el.classList.toggle('selected', isSelected);

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

        // Update the map to show only the selected order
        if (window.orderMap && window.orderMap.__markers) {
            // Hide all markers first
            window.orderMap.__markers.forEach(m => {
                if (window.orderMap.hasLayer(m.marker)) {
                    window.orderMap.removeLayer(m.marker);
                }
            });

            // Find the marker for the selected order
            const marker = window.orderMap.__markers.find(m => m.orderId === parseInt(orderId));

            // If found, add it back to the map and center on it
            if (marker) {
                marker.marker.addTo(window.orderMap);
                window.orderMap.setView([marker.coords.lat, marker.coords.lon], 16);
                marker.marker.openPopup();
            }
        }

        // Update view based on active map-view-option
        const activeViewOption = document.querySelector('.map-view-option.active');
        if (activeViewOption && activeViewOption.textContent.trim() === 'Order Details') {
            await switchToDetailsView();
        } else {
            switchToMapView();
            // This will add the vehicle marker for the selected order only
            centerMapOnOrder(orderId);
        }
    }

    // Function to fetch route between two points using OSRM API
    async function fetchRoute(startLat, startLon, endLat, endLon) {
        try {
            const url = `https://router.project-osrm.org/route/v1/driving/${startLon},${startLat};${endLon},${endLat}?overview=full&geometries=geojson`;
            const response = await fetch(url);
            const data = await response.json();

            if (data.code !== 'Ok' || !data.routes || data.routes.length === 0) {
                console.error('Failed to fetch route:', data);
                return null;
            }

            return data.routes[0].geometry.coordinates.map(coord => [coord[1], coord[0]]);
        } catch (error) {
            console.error('Error fetching route:', error);
            return null;
        }
    }

    // Function to center the map on the selected order's pinned location and draw route
    async function centerMapOnOrder(orderId) {
        console.log(`Centering map on order: ${orderId}`);

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

        // Get order details for enhanced popups
        const order = activeOrdersData.find(o => o.id === parseInt(orderId));
        if (!order) {
            console.warn(`Order data not found for ID: ${orderId}`);
            return;
        }

        // Store location coordinates (7.08405° N, 125.59334° E)
        const storeLocation = [7.08405, 125.59334];

        // Double-check that we're working with the correct order
        if (parseInt(selectedOrderId) !== parseInt(orderId)) {
            console.warn(`Order mismatch: selectedOrderId=${selectedOrderId}, centering on=${orderId}`);
            // Update the selected order ID to match
            selectedOrderId = orderId;
        }

        // CRITICAL: Make sure we only have vehicle markers for THIS order
        try {
            // Use our improved function to remove vehicle markers for all OTHER orders
            // By passing the current order ID, we keep only markers for this order
            removeAllVehicleMarkers(orderId);

            // Now remove all non-tile layers to start fresh
            // This ensures we don't have any stale markers or routes
            window.orderMap.eachLayer(layer => {
                if (!(layer instanceof L.TileLayer)) {
                    window.orderMap.removeLayer(layer);
                }
            });

            console.log(`Map cleared and ready for order ${orderId}`);
        } catch (error) {
            console.warn('Error cleaning up map layers:', error);
            // Continue with the operation even if there's an error
        }

        // Remove the default destination marker and add our custom one
        // Check if the marker exists on the map before trying to remove it
        if (marker && marker.marker && window.orderMap.hasLayer(marker.marker)) {
            window.orderMap.removeLayer(marker.marker);
        }

        // Add enhanced store marker with custom popup
        const storeIcon = L.divIcon({
            className: 'store-marker-icon map-marker',
            html: '<i class="fas fa-ice-cream"></i>',
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });

        const storeMarker = L.marker(storeLocation, {
            icon: storeIcon,
            zIndexOffset: 1000
        }).addTo(window.orderMap);

        storeMarker._storeMarker = true;

        // Simplified store popup content in landscape style
        const storePopupContent = `
            <div class="store-popup">
                <div class="popup-content">
                    <div class="popup-icon">
                        <i class="fas fa-ice-cream"></i>
                    </div>
                    <div class="popup-text">
                        <div class="popup-title">Rey's Davao Icecream Delivery</div>
                        <div class="popup-address">7.08405° N, 125.59334° E, Davao City</div>
                        <div class="popup-hours">Open Hours: 9:00 AM - 9:00 PM</div>
                    </div>
                </div>
            </div>
        `;

        // Create custom popup for store
        const storePopup = L.popup({
            className: 'custom-popup',
            closeButton: true,
            autoClose: false,
            closeOnEscapeKey: true,
            closeOnClick: false,
            offset: [0, -20]
        }).setContent(storePopupContent);

        storeMarker.bindPopup(storePopup);

        // Add click animation to store marker
        storeMarker.on('click', function() {
            const icon = this.getElement();
            icon.classList.add('marker-pulse');
            setTimeout(() => {
                icon.classList.remove('marker-pulse');
            }, 500);
        });

        // Add hover functionality to store marker
        storeMarker.on('mouseover', function() {
            this.openPopup();
            const icon = this.getElement();
            icon.classList.add('marker-hover');
        });

        storeMarker.on('mouseout', function() {
            const icon = this.getElement();
            icon.classList.remove('marker-hover');
            this.closePopup();
        });

        // Add enhanced destination marker with custom popup
        const destinationIcon = L.divIcon({
            className: 'destination-marker-icon map-marker',
            html: '<i class="fas fa-map-marker-alt"></i>',
            iconSize: [40, 40],
            iconAnchor: [20, 40]
        });

        const destinationMarker = L.marker([marker.coords.lat, marker.coords.lon], {
            icon: destinationIcon,
            zIndexOffset: 1000
        }).addTo(window.orderMap);

        destinationMarker._destinationMarker = true;

        // Format estimated delivery time
        let estimatedDelivery = 'Processing';
        if (order.estimated_delivery_time) {
            const deliveryTime = new Date(order.estimated_delivery_time);
            estimatedDelivery = deliveryTime.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        }

        // Simplified destination popup content in landscape style
        const destinationPopupContent = `
            <div class="destination-popup">
                <div class="popup-content">
                    <div class="popup-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="popup-text">
                        <div class="popup-title">Delivery Location</div>
                        <div class="popup-address">${order.shipping_address}</div>
                    </div>
                </div>
            </div>
        `;

        // Create custom popup for destination
        const destinationPopup = L.popup({
            className: 'custom-popup',
            closeButton: true,
            autoClose: false,
            closeOnEscapeKey: true,
            closeOnClick: false,
            offset: [0, -40]
        }).setContent(destinationPopupContent);

        destinationMarker.bindPopup(destinationPopup);

        // Add click animation to destination marker
        destinationMarker.on('click', function() {
            const icon = this.getElement();
            icon.classList.add('marker-pulse');
            setTimeout(() => {
                icon.classList.remove('marker-pulse');
            }, 500);
        });

        // Add hover functionality to destination marker
        destinationMarker.on('mouseover', function() {
            this.openPopup();
            const icon = this.getElement();
            icon.classList.add('marker-hover');
        });

        destinationMarker.on('mouseout', function() {
            const icon = this.getElement();
            icon.classList.remove('marker-hover');
            this.closePopup();
        });

        // Fetch and draw route
        const routeCoordinates = await fetchRoute(
            storeLocation[0], storeLocation[1],
            marker.coords.lat, marker.coords.lon
        );

        if (routeCoordinates) {
            // Create a polyline with the route
            const routeLine = L.polyline(routeCoordinates, {
                weight: 4,
                opacity: 0.8,
                className: 'delivery-route',
                smoothFactor: 1
            }).addTo(window.orderMap);

            routeLine._route = true;

            // Store route bounds for recenter function
            window.orderMap._routeBounds = routeLine.getBounds();

            // Fit map to show both markers and the route
            window.orderMap.fitBounds(routeLine.getBounds(), { padding: [50, 50] });

            // Only add delivery vehicle if order status is "Out for Delivery"
            let vehicleMarker = null;

            // CRITICAL CHECK: Make sure we're working with the correct order
            if (parseInt(selectedOrderId) !== parseInt(order.id)) {
                console.error(`ORDER MISMATCH DETECTED: selectedOrderId=${selectedOrderId}, but creating vehicle for order=${order.id}`);
                // Force the correct order ID
                selectedOrderId = order.id;
            }

            // Normalize the order status for consistent comparison
            const normalizedStatus = order.order_status?.trim().toLowerCase() || '';

            // Log the order status for debugging
            console.log(`Order ${order.id} status: "${order.order_status}" (normalized: "${normalizedStatus}")`);

            // Only create vehicle marker for "out for delivery" status
            if (normalizedStatus === "out for delivery") {
                console.log(`Creating vehicle marker for order ${order.id} (main map)`);

                const vehicleIcon = L.divIcon({
                    className: 'vehicle-marker-icon map-marker',
                    html: '<i class="fas fa-truck"></i>',
                    iconSize: [36, 36],
                    iconAnchor: [18, 18]
                });

                // Get or generate a consistent vehicle position for this order
                let vehiclePosition;

                // Check if we already have a stored position for this order
                if (window.vehiclePositions[order.id]) {
                    // Use the stored position
                    vehiclePosition = window.vehiclePositions[order.id];
                } else {
                    // Generate a random position between 10% and 90% of the route
                    const routeLength = routeCoordinates.length;
                    const minPosition = Math.floor(routeLength * 0.1); // 10% of route
                    const maxPosition = Math.floor(routeLength * 0.9); // 90% of route
                    const positionRange = maxPosition - minPosition;
                    const randomOffset = Math.floor(Math.random() * positionRange);
                    const fixedPosition = minPosition + randomOffset;

                    // Store the position coordinates
                    vehiclePosition = routeCoordinates[fixedPosition];

                    // Save this position for future use
                    window.vehiclePositions[order.id] = vehiclePosition;
                }

                // Create the vehicle marker with improved stability
                vehicleMarker = L.marker(vehiclePosition, {
                    icon: vehicleIcon,
                    zIndexOffset: 1100,
                    // Make sure the marker stays on the map during zoom/pan
                    interactive: true,
                    bubblingMouseEvents: false,
                    pane: 'markerPane'
                }).addTo(window.orderMap);

                // Add custom property to identify this marker
                vehicleMarker._vehicleMarker = true;
                vehicleMarker._orderId = order.id; // Add order ID to the marker for tracking

                // Add a debug identifier to help with troubleshooting
                vehicleMarker._mapType = 'main';
                vehicleMarker._createdAt = new Date().toISOString();

                // Store reference to prevent garbage collection
                window.orderMap._vehicleMarker = vehicleMarker;

                // Log the creation of this marker for debugging
                console.log(`Created main vehicle marker for order ${order.id}`);

                // Add to our global tracking array - but first check if we already have one
                const existingIndex = window.allVehicleMarkers.findIndex(m =>
                    m._orderId === order.id && m._mapType === 'main'
                );

                if (existingIndex >= 0) {
                    console.log(`Replacing existing main vehicle marker for order ${order.id}`);
                    window.allVehicleMarkers[existingIndex] = vehicleMarker;
                } else {
                    window.allVehicleMarkers.push(vehicleMarker);
                }
            }

            // Only create and bind popup if vehicle marker exists
            if (vehicleMarker) {
                // Simplified vehicle popup content in landscape style
                const vehiclePopupContent = `
                    <div class="vehicle-popup">
                        <div class="popup-content">
                            <div class="popup-icon">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="popup-text">
                                <div class="popup-title">Delivery Vehicle</div>
                                <div class="popup-address">Delivering order #${order.id} • ETA: ${estimatedDelivery}</div>
                            </div>
                        </div>
                    </div>
                `;

                // Create custom popup for vehicle
                const vehiclePopup = L.popup({
                    className: 'custom-popup',
                    closeButton: true,
                    autoClose: false,
                    closeOnEscapeKey: true,
                    closeOnClick: false,
                    offset: [0, -18]
                }).setContent(vehiclePopupContent);

                vehicleMarker.bindPopup(vehiclePopup);

                // Add click animation to vehicle marker
                vehicleMarker.on('click', function() {
                    const icon = this.getElement();
                    icon.classList.add('marker-pulse');
                    setTimeout(() => {
                        icon.classList.remove('marker-pulse');
                    }, 500);
                });

                // Add hover functionality to vehicle marker
                vehicleMarker.on('mouseover', function() {
                    this.openPopup();
                    const icon = this.getElement();
                    icon.classList.add('marker-hover');
                });

                vehicleMarker.on('mouseout', function() {
                    const icon = this.getElement();
                    icon.classList.remove('marker-hover');
                    this.closePopup();
                });

                // Add map event listeners to ensure vehicle marker stays visible
                window.orderMap.on('zoomend moveend dragend', function() {
                    // Ensure vehicle marker is still on the map
                    if (vehicleMarker && !window.orderMap.hasLayer(vehicleMarker)) {
                        vehicleMarker.addTo(window.orderMap);
                    }

                    // Force redraw of the marker
                    if (vehicleMarker) {
                        const currentPos = vehicleMarker.getLatLng();
                        vehicleMarker.setLatLng([currentPos.lat, currentPos.lng]);
                    }
                });
            }

            // Clear any existing animation interval
            if (window.vehicleAnimationInterval) {
                clearInterval(window.vehicleAnimationInterval);
                window.vehicleAnimationInterval = null;
            }
        } else {
            // If route fetching fails, just center on the order marker
            window.orderMap.setView([marker.coords.lat, marker.coords.lon], 16, { animate: false });
            window.orderMap.panTo([marker.coords.lat, marker.coords.lon], { animate: true, duration: 0.5 });
        }

        // Open the popup for the destination marker
        destinationMarker.openPopup();

        // Invalidate map size to handle rendering issues, with a slight delay
        setTimeout(() => {
            window.orderMap.invalidateSize();
            // Ensure progress bars are updated after map centering
            updateProgressBars();
        }, 100);

        // Update progress bars immediately as well to prevent flashing of empty progress
        updateProgressBars();
    }

    // Function to remove all vehicle markers from the map
    function removeAllVehicleMarkers(specificOrderId = null) {
        if (specificOrderId) {
            console.log(`Removing vehicle markers for order ${specificOrderId} only`);

            // IMPORTANT: Check if this order should even have a vehicle marker
            // Only orders with "Out for Delivery" status should have vehicle markers
            if (specificOrderId) {
                const order = activeOrdersData.find(o => o.id === parseInt(specificOrderId));
                if (order) {
                    const normalizedStatus = order.order_status?.trim().toLowerCase() || '';
                    if (normalizedStatus !== "out for delivery") {
                        console.log(`Order ${specificOrderId} has status "${order.order_status}" - should NOT have a vehicle marker`);
                        // In this case, we should remove ALL vehicle markers for this order
                        specificOrderId = null;
                    }
                }
            }
        } else {
            console.log("Removing ALL vehicle markers from all maps");
        }

        // MAIN MAP: Remove vehicle markers
        if (window.orderMap) {
            try {
                // First, find all vehicle markers on the main map
                const vehicleMarkersToRemove = [];
                window.orderMap.eachLayer(layer => {
                    // If we're removing for a specific order, only remove that order's marker
                    // Otherwise, remove all vehicle markers
                    if (layer._vehicleMarker) {
                        if (!specificOrderId || (layer._orderId && parseInt(layer._orderId) !== parseInt(specificOrderId))) {
                            vehicleMarkersToRemove.push(layer);
                        }
                    }
                });

                // Log how many we found
                console.log(`Found ${vehicleMarkersToRemove.length} vehicle markers to remove from main map`);

                // Remove each vehicle marker
                vehicleMarkersToRemove.forEach(marker => {
                    if (marker._orderId) {
                        console.log(`Removing vehicle marker for order ${marker._orderId} from main map`);
                    }
                    window.orderMap.removeLayer(marker);
                });

                // If removing all markers or the specific marker that's referenced, clear the reference
                if (!specificOrderId ||
                    (window.orderMap._vehicleMarker &&
                     window.orderMap._vehicleMarker._orderId &&
                     parseInt(window.orderMap._vehicleMarker._orderId) !== parseInt(specificOrderId))) {
                    window.orderMap._vehicleMarker = null;
                }
            } catch (error) {
                console.error("Error removing vehicle markers from main map:", error);
            }
        }

        // MODAL MAP: Remove vehicle markers
        if (window.modalOrderMap) {
            try {
                // Find all vehicle markers on the modal map
                const vehicleMarkersToRemove = [];
                window.modalOrderMap.eachLayer(layer => {
                    // If we're removing for a specific order, only remove other orders' markers
                    // Otherwise, remove all vehicle markers
                    if (layer._vehicleMarker) {
                        if (!specificOrderId || (layer._orderId && parseInt(layer._orderId) !== parseInt(specificOrderId))) {
                            vehicleMarkersToRemove.push(layer);
                        }
                    }
                });

                // Log how many we found
                console.log(`Found ${vehicleMarkersToRemove.length} vehicle markers to remove from modal map`);

                // Remove each vehicle marker
                vehicleMarkersToRemove.forEach(marker => {
                    if (marker._orderId) {
                        console.log(`Removing vehicle marker for order ${marker._orderId} from modal map`);
                    }
                    window.modalOrderMap.removeLayer(marker);
                });

                // If removing all markers or the specific marker that's referenced, clear the reference
                if (!specificOrderId ||
                    (window.modalOrderMap._vehicleMarker &&
                     window.modalOrderMap._vehicleMarker._orderId &&
                     parseInt(window.modalOrderMap._vehicleMarker._orderId) !== parseInt(specificOrderId))) {
                    window.modalOrderMap._vehicleMarker = null;
                }
            } catch (error) {
                console.error("Error removing vehicle markers from modal map:", error);
            }
        }

        // Update the global array
        if (specificOrderId) {
            // Keep only markers for the specified order
            const initialCount = window.allVehicleMarkers.length;
            window.allVehicleMarkers = window.allVehicleMarkers.filter(marker =>
                marker._orderId && parseInt(marker._orderId) === parseInt(specificOrderId)
            );
            console.log(`Filtered global array: removed ${initialCount - window.allVehicleMarkers.length} markers, kept ${window.allVehicleMarkers.length} for order ${specificOrderId}`);
        } else {
            // Clear the entire global array
            const markerCount = window.allVehicleMarkers.length;
            window.allVehicleMarkers = [];
            console.log(`Cleared all ${markerCount} vehicle markers from global tracking array`);
        }

        // Force garbage collection by nullifying references
        if (window.vehicleMarker) {
            window.vehicleMarker = null;
        }
    }

    // Function to scroll to the map section
    function scrollToMap() {
        const mapContainer = document.querySelector('.live-tracking');
        if (mapContainer) {
            mapContainer.scrollIntoView({ behavior: 'smooth' });
        }
    }

    function toggleMapFullscreen() {
        // Show the modal instead of using fullscreen class
        if (!mapModal) return;

        // Show loading overlay in modal
        const modalMapLoadingOverlay = document.createElement('div');
        modalMapLoadingOverlay.className = 'map-loading-overlay';
        modalMapLoadingOverlay.innerHTML = `
            <div class="map-loading-spinner"></div>
            <div class="map-loading-text">Loading map...</div>
        `;

        if (modalOrderMap) {
            modalOrderMap.innerHTML = '';
            modalOrderMap.appendChild(modalMapLoadingOverlay);
        }

        // Show the modal
        mapModal.classList.add('active');

        // Initialize the modal map if not already done
        setTimeout(() => {
            initModalMap();
        }, 100);

        // Add event listener to close button if not already added
        if (mapModalClose && !mapModalClose._hasClickListener) {
            mapModalClose.addEventListener('click', () => {
                console.log("Closing map modal");
                mapModal.classList.remove('active');

                // Clean up animation interval when modal is closed
                if (window.modalVehicleAnimationInterval) {
                    clearInterval(window.modalVehicleAnimationInterval);
                    window.modalVehicleAnimationInterval = null;
                }

                // Clean up modal map vehicle markers
                try {
                    console.log("Cleaning up modal map vehicle markers");

                    // First, remove all modal-specific vehicle markers from the global array
                    const initialCount = window.allVehicleMarkers.length;
                    window.allVehicleMarkers = window.allVehicleMarkers.filter(marker =>
                        marker._mapType !== 'modal'
                    );
                    console.log(`Removed ${initialCount - window.allVehicleMarkers.length} modal markers from global array`);

                    // Then, if the modal map exists, remove all vehicle markers from it
                    if (window.modalOrderMap) {
                        const vehicleMarkersToRemove = [];
                        window.modalOrderMap.eachLayer(layer => {
                            if (layer._vehicleMarker) {
                                vehicleMarkersToRemove.push(layer);
                            }
                        });

                        vehicleMarkersToRemove.forEach(marker => {
                            if (marker._orderId) {
                                console.log(`Removing vehicle marker for order ${marker._orderId} from modal map`);
                            }
                            window.modalOrderMap.removeLayer(marker);
                        });

                        // Clear the reference
                        window.modalOrderMap._vehicleMarker = null;
                    }
                } catch (error) {
                    console.warn('Error removing vehicle markers when closing modal:', error);
                }

                // Reset modal map initialization flag
                modalMapInitialized = false;
            });
            mapModalClose._hasClickListener = true;
        }
    }

    // Function to initialize the modal map
    async function initModalMap() {
        if (!modalOrderMap || !selectedOrderId || modalMapInitialized) return;

        console.log(`Initializing modal map for order: ${selectedOrderId}`);

        // CRITICAL: Make sure we only have vehicle markers for THIS order
        try {
            console.log(`Preparing modal map for order ${selectedOrderId}`);

            // Use our improved function to remove vehicle markers for all OTHER orders
            // By passing the current order ID, we keep only markers for this order
            removeAllVehicleMarkers(selectedOrderId);

            // Now completely clear the modal map (except tile layers)
            // This ensures we have a clean slate for the modal map
            if (window.modalOrderMap) {
                window.modalOrderMap.eachLayer(layer => {
                    if (!(layer instanceof L.TileLayer)) {
                        window.modalOrderMap.removeLayer(layer);
                    }
                });
            }

            console.log(`Modal map cleared and ready for order ${selectedOrderId}`);
        } catch (error) {
            console.warn('Error cleaning maps during modal initialization:', error);
        }

        // Find the selected order's coordinates
        const order = activeOrdersData.find(o => o.id === parseInt(selectedOrderId));
        if (!order) return;

        // Get coordinates from the main map's markers
        const markers = window.orderMap?.__markers || [];
        const marker = markers.find(m => m.orderId === parseInt(selectedOrderId));
        if (!marker) return;

        // Create a new map in the modal
        const modalMap = L.map(modalOrderMap, {
            center: [marker.coords.lat, marker.coords.lon],
            zoom: 16,
            zoomControl: true,
            attributionControl: true,
            scrollWheelZoom: true,
            dragging: true,
            doubleClickZoom: true,
            boxZoom: true,
            keyboard: true,
            tap: true
        });

        // Add the tile layer (using the same as the main map)
        const tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(modalMap);

        // Ensure SVG gradient is defined for the route
        if (!document.querySelector('svg #pink-gradient')) {
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
        }

        // Store location coordinates (7.08405° N, 125.59334° E)
        const storeLocation = [7.08405, 125.59334];

        // Add enhanced store marker with custom popup
        const storeIcon = L.divIcon({
            className: 'store-marker-icon map-marker',
            html: '<i class="fas fa-ice-cream"></i>',
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });

        const storeMarker = L.marker(storeLocation, {
            icon: storeIcon,
            zIndexOffset: 1000
        }).addTo(modalMap);

        // Simplified store popup content in landscape style
        const storePopupContent = `
            <div class="store-popup">
                <div class="popup-content">
                    <div class="popup-icon">
                        <i class="fas fa-ice-cream"></i>
                    </div>
                    <div class="popup-text">
                        <div class="popup-title">Rey's Davao Icecream Delivery</div>
                        <div class="popup-address">7.08405° N, 125.59334° E, Davao City</div>
                        <div class="popup-hours">Open Hours: 9:00 AM - 9:00 PM</div>
                    </div>
                </div>
            </div>
        `;

        // Create custom popup for store
        const storePopup = L.popup({
            className: 'custom-popup',
            closeButton: true,
            autoClose: false,
            closeOnEscapeKey: true,
            closeOnClick: false,
            offset: [0, -20]
        }).setContent(storePopupContent);

        storeMarker.bindPopup(storePopup);

        // Add click animation to store marker
        storeMarker.on('click', function() {
            const icon = this.getElement();
            icon.classList.add('marker-pulse');
            setTimeout(() => {
                icon.classList.remove('marker-pulse');
            }, 500);
        });

        // Add hover functionality to store marker
        storeMarker.on('mouseover', function() {
            this.openPopup();
            const icon = this.getElement();
            icon.classList.add('marker-hover');
        });

        storeMarker.on('mouseout', function() {
            const icon = this.getElement();
            icon.classList.remove('marker-hover');
            this.closePopup();
        });

        // Add enhanced destination marker with custom popup
        const destinationIcon = L.divIcon({
            className: 'destination-marker-icon map-marker',
            html: '<i class="fas fa-map-marker-alt"></i>',
            iconSize: [40, 40],
            iconAnchor: [20, 40]
        });

        const destinationMarker = L.marker([marker.coords.lat, marker.coords.lon], {
            icon: destinationIcon,
            zIndexOffset: 1000
        }).addTo(modalMap);

        // Format estimated delivery time
        let estimatedDelivery = 'Processing';
        if (order.estimated_delivery_time) {
            const deliveryTime = new Date(order.estimated_delivery_time);
            estimatedDelivery = deliveryTime.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        }

        // Simplified destination popup content in landscape style
        const destinationPopupContent = `
            <div class="destination-popup">
                <div class="popup-content">
                    <div class="popup-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="popup-text">
                        <div class="popup-title">Delivery Location</div>
                        <div class="popup-address">${order.shipping_address}</div>
                    </div>
                </div>
            </div>
        `;

        // Create custom popup for destination
        const destinationPopup = L.popup({
            className: 'custom-popup',
            closeButton: true,
            autoClose: false,
            closeOnEscapeKey: true,
            closeOnClick: false,
            offset: [0, -40]
        }).setContent(destinationPopupContent);

        destinationMarker.bindPopup(destinationPopup);

        // Add click animation to destination marker
        destinationMarker.on('click', function() {
            const icon = this.getElement();
            icon.classList.add('marker-pulse');
            setTimeout(() => {
                icon.classList.remove('marker-pulse');
            }, 500);
        });

        // Add hover functionality to destination marker
        destinationMarker.on('mouseover', function() {
            this.openPopup();
            const icon = this.getElement();
            icon.classList.add('marker-hover');
        });

        destinationMarker.on('mouseout', function() {
            const icon = this.getElement();
            icon.classList.remove('marker-hover');
            this.closePopup();
        });

        // Open the popup for the destination marker
        destinationMarker.openPopup();

        // Fetch and draw route
        const routeCoordinates = await fetchRoute(
            storeLocation[0], storeLocation[1],
            marker.coords.lat, marker.coords.lon
        );

        if (routeCoordinates) {
            // Create a polyline with the route
            const routeLine = L.polyline(routeCoordinates, {
                weight: 4,
                opacity: 0.8,
                className: 'delivery-route',
                smoothFactor: 1
            }).addTo(modalMap);

            // Fit map to show both markers and the route
            modalMap.fitBounds(routeLine.getBounds(), { padding: [50, 50] });

            // Only add delivery vehicle if order status is "Out for Delivery"
            let vehicleMarker = null;

            // CRITICAL CHECK: Make sure we're working with the correct order
            if (parseInt(selectedOrderId) !== parseInt(order.id)) {
                console.error(`MODAL ORDER MISMATCH DETECTED: selectedOrderId=${selectedOrderId}, but creating vehicle for order=${order.id}`);
                // Force the correct order ID
                selectedOrderId = order.id;
            }

            // Normalize the order status for consistent comparison
            const normalizedStatus = order.order_status?.trim().toLowerCase() || '';

            // Log the order status for debugging
            console.log(`Modal map: Order ${order.id} status: "${order.order_status}" (normalized: "${normalizedStatus}")`);

            // Only create vehicle marker for "out for delivery" status
            if (normalizedStatus === "out for delivery") {
                console.log(`Creating vehicle marker for order ${order.id} (modal map)`);

                const vehicleIcon = L.divIcon({
                    className: 'vehicle-marker-icon map-marker',
                    html: '<i class="fas fa-truck"></i>',
                    iconSize: [36, 36],
                    iconAnchor: [18, 18]
                });

                // Use the same vehicle position as the main map
                let vehiclePosition;

                // Check if we already have a stored position for this order
                if (window.vehiclePositions[order.id]) {
                    // Use the stored position from the main map
                    vehiclePosition = window.vehiclePositions[order.id];
                } else {
                    // If no position exists yet (unlikely), generate one and store it
                    const routeLength = routeCoordinates.length;
                    const minPosition = Math.floor(routeLength * 0.1); // 10% of route
                    const maxPosition = Math.floor(routeLength * 0.9); // 90% of route
                    const positionRange = maxPosition - minPosition;
                    const randomOffset = Math.floor(Math.random() * positionRange);
                    const fixedPosition = minPosition + randomOffset;

                    vehiclePosition = routeCoordinates[fixedPosition];
                    window.vehiclePositions[order.id] = vehiclePosition;
                }

                // Create the vehicle marker with improved stability
                vehicleMarker = L.marker(vehiclePosition, {
                    icon: vehicleIcon,
                    zIndexOffset: 1100,
                    // Make sure the marker stays on the map during zoom/pan
                    interactive: true,
                    bubblingMouseEvents: false,
                    pane: 'markerPane'
                }).addTo(modalMap);

                // Add custom property to identify this marker
                vehicleMarker._vehicleMarker = true;
                vehicleMarker._orderId = order.id; // Add order ID to the marker for tracking

                // Add a debug identifier to help with troubleshooting
                vehicleMarker._mapType = 'modal';
                vehicleMarker._createdAt = new Date().toISOString();

                // Store reference to prevent garbage collection
                modalMap._vehicleMarker = vehicleMarker;

                // Log the creation of this marker for debugging
                console.log(`Created modal vehicle marker for order ${order.id}`);

                // Add to our global tracking array - but first check if we already have one
                const existingIndex = window.allVehicleMarkers.findIndex(m =>
                    m._orderId === order.id && m._mapType === 'modal'
                );

                if (existingIndex >= 0) {
                    console.log(`Replacing existing modal vehicle marker for order ${order.id}`);
                    window.allVehicleMarkers[existingIndex] = vehicleMarker;
                } else {
                    window.allVehicleMarkers.push(vehicleMarker);
                }
            }

            // Only create and bind popup if vehicle marker exists
            if (vehicleMarker) {
                // Simplified vehicle popup content in landscape style
                const vehiclePopupContent = `
                    <div class="vehicle-popup">
                        <div class="popup-content">
                            <div class="popup-icon">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="popup-text">
                                <div class="popup-title">Delivery Vehicle</div>
                                <div class="popup-address">Delivering order #${order.id} • ETA: ${estimatedDelivery}</div>
                            </div>
                        </div>
                    </div>
                `;

                // Create custom popup for vehicle
                const vehiclePopup = L.popup({
                    className: 'custom-popup',
                    closeButton: true,
                    autoClose: false,
                    closeOnEscapeKey: true,
                    closeOnClick: false,
                    offset: [0, -18]
                }).setContent(vehiclePopupContent);

                vehicleMarker.bindPopup(vehiclePopup);

                // Add click animation to vehicle marker
                vehicleMarker.on('click', function() {
                    const icon = this.getElement();
                    icon.classList.add('marker-pulse');
                    setTimeout(() => {
                        icon.classList.remove('marker-pulse');
                    }, 500);
                });

                // Add hover functionality to vehicle marker
                vehicleMarker.on('mouseover', function() {
                    this.openPopup();
                    const icon = this.getElement();
                    icon.classList.add('marker-hover');
                });

                vehicleMarker.on('mouseout', function() {
                    const icon = this.getElement();
                    icon.classList.remove('marker-hover');
                    this.closePopup();
                });

                // Add map event listeners to ensure vehicle marker stays visible
                modalMap.on('zoomend moveend dragend', function() {
                    // Ensure vehicle marker is still on the map
                    if (vehicleMarker && !modalMap.hasLayer(vehicleMarker)) {
                        vehicleMarker.addTo(modalMap);
                    }

                    // Force redraw of the marker
                    if (vehicleMarker) {
                        const currentPos = vehicleMarker.getLatLng();
                        vehicleMarker.setLatLng([currentPos.lat, currentPos.lng]);
                    }
                });
            }

            // Clear any existing animation interval
            if (window.modalVehicleAnimationInterval) {
                clearInterval(window.modalVehicleAnimationInterval);
                window.modalVehicleAnimationInterval = null;
            }
        }

        // Remove loading overlay
        const loadingOverlay = modalOrderMap.querySelector('.map-loading-overlay');
        if (loadingOverlay) {
            loadingOverlay.remove();
        }

        // Set flag to avoid reinitializing
        modalMapInitialized = true;

        // Store the modal map instance
        window.modalOrderMap = modalMap;

        // Invalidate size after a short delay to ensure proper rendering
        setTimeout(() => {
            modalMap.invalidateSize();
        }, 300);
    }

    // Function to initialize map control buttons
    function initMapControlButtons() {
        // Map view buttons (Standard/Satellite/Traffic)
        const mapViewButtons = document.querySelectorAll('.map-view-button');
        if (mapViewButtons.length) {
            // First remove any existing event listeners to prevent duplicates
            mapViewButtons.forEach(button => {
                const newButton = button.cloneNode(true);
                button.parentNode.replaceChild(newButton, button);
            });

            // Now add event listeners to the fresh buttons
            document.querySelectorAll('.map-view-button').forEach(button => {
                button.addEventListener('click', () => {
                    const view = button.getAttribute('data-view');
                    switchMapView(view);

                    // Ensure vehicle marker is still visible after changing map view
                    setTimeout(() => {
                        if (window.orderMap && window.orderMap._vehicleMarker &&
                            !window.orderMap.hasLayer(window.orderMap._vehicleMarker)) {
                            window.orderMap._vehicleMarker.addTo(window.orderMap);
                        }
                    }, 100);
                });

                // Add tooltip functionality
                button.setAttribute('title', button.querySelector('span').textContent);
            });
        }

        // Map control buttons (Zoom In/Out, Center, Refresh)
        // First remove any existing event listeners to prevent duplicates
        const controlButtons = document.querySelectorAll('.map-control-button, .map-fullscreen-button');
        controlButtons.forEach(button => {
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
        });

        // Now get fresh references
        const zoomInButton = document.querySelector('.map-control-button.zoom-in');
        const zoomOutButton = document.querySelector('.map-control-button.zoom-out');
        const centerButton = document.querySelector('.map-control-button.center');
        const refreshButton = document.querySelector('.map-control-button.refresh');
        const fullscreenButton = document.querySelector('.map-fullscreen-button');

        // Add tooltips to map control buttons
        if (zoomInButton) {
            zoomInButton.setAttribute('title', 'Zoom In');
            zoomInButton.addEventListener('click', () => {
                if (window.orderMap) {
                    window.orderMap.zoomIn();
                }
            });
        }

        if (zoomOutButton) {
            zoomOutButton.setAttribute('title', 'Zoom Out');
            zoomOutButton.addEventListener('click', () => {
                if (window.orderMap) {
                    window.orderMap.zoomOut();
                }
            });
        }

        if (centerButton) {
            centerButton.setAttribute('title', 'Center Map');
            centerButton.addEventListener('click', () => {
                if (window.orderMap && selectedOrderId) {
                    centerMapOnOrder(selectedOrderId);
                } else if (window.orderMap && window.orderMap._routeBounds) {
                    window.orderMap.fitBounds(window.orderMap._routeBounds, { padding: [50, 50] });
                } else if (window.orderMap && window.orderMap.__markers && window.orderMap.__markers.length > 0) {
                    const group = new L.featureGroup(window.orderMap.__markers.map(m => m.marker));
                    window.orderMap.fitBounds(group.getBounds(), { padding: [50, 50] });
                }
            });
        }

        if (refreshButton) {
            refreshButton.setAttribute('title', 'Refresh Map');
            refreshButton.addEventListener('click', async () => {
                if (window.orderMap) {
                    // Add a spinning animation to the refresh button
                    const icon = refreshButton.querySelector('i');
                    icon.classList.add('fa-spin');

                    // Set a maximum timeout for the spinning animation (5 seconds)
                    const spinTimeout = setTimeout(() => {
                        icon.classList.remove('fa-spin');
                    }, 5000);

                    // Clean up any existing vehicle markers before refreshing
                    try {
                        removeAllVehicleMarkers();
                    } catch (error) {
                        console.warn('Error removing vehicle markers during map refresh:', error);
                    }

                    // Reload the map data
                    try {
                        // Use Promise.race to ensure the operation doesn't hang
                        await Promise.race([
                            renderActiveOrdersAndMap(),
                            new Promise((_, reject) =>
                                setTimeout(() => reject(new Error('Refresh operation timed out')), 4000)
                            )
                        ]);
                    } catch (error) {
                        console.error('Error refreshing map:', error);
                        // Show a brief error message to the user
                        const mapDiv = document.getElementById('orderMap');
                        if (mapDiv) {
                            const errorMsg = document.createElement('div');
                            errorMsg.className = 'map-error-message';
                            errorMsg.textContent = 'Could not refresh map data. Please try again.';
                            errorMsg.style.position = 'absolute';
                            errorMsg.style.top = '50%';
                            errorMsg.style.left = '50%';
                            errorMsg.style.transform = 'translate(-50%, -50%)';
                            errorMsg.style.backgroundColor = 'rgba(255, 0, 0, 0.7)';
                            errorMsg.style.color = 'white';
                            errorMsg.style.padding = '10px 15px';
                            errorMsg.style.borderRadius = '5px';
                            errorMsg.style.zIndex = '1000';
                            mapDiv.appendChild(errorMsg);

                            // Remove the error message after 3 seconds
                            setTimeout(() => {
                                if (errorMsg.parentNode) {
                                    errorMsg.parentNode.removeChild(errorMsg);
                                }
                            }, 3000);
                        }
                    } finally {
                        // Clear the timeout and remove the spinning animation
                        clearTimeout(spinTimeout);
                        icon.classList.remove('fa-spin');
                    }
                }
            });
        }

        if (fullscreenButton) {
            fullscreenButton.setAttribute('title', 'Toggle Fullscreen');
            fullscreenButton.addEventListener('click', toggleMapFullscreen);
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

        // Add event listener for "Book New Event" buttons
        document.querySelectorAll('.book-event-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                window.location.href = '/ayskrimWebsite/customerPage/menu/menu.php?section=booking';
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

        // Map view buttons are now handled in initMapControlButtons()

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

    // Function to initialize bookings tab
    async function initBookingsTab() {
        console.log('Initializing bookings tab');

        try {
            // Fetch bookings data from API
            const bookings = await fetchUserBookings();
            console.log('Fetched bookings:', bookings);

            // Update badge counts
            const upcomingBadge = document.querySelector('.secondary-tab-btn[data-tab="upcoming-bookings"] .badge-count');
            const pastBadge = document.querySelector('.secondary-tab-btn[data-tab="past-bookings"] .badge-count');

            if (upcomingBadge) {
                upcomingBadge.textContent = bookings.upcoming.length;
            }

            if (pastBadge) {
                pastBadge.textContent = bookings.past.length;
            }

            // Re-initialize booking details toggle
            setTimeout(() => {
                initBookingDetailsToggle();
            }, 100);

        } catch (error) {
            console.error('Error initializing bookings tab:', error);
        }
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
            // Initialize bookings tab when selected
            initBookingsTab();
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

        // Update active state of map view buttons
        const mapViewButtons = document.querySelectorAll('.map-view-button');
        mapViewButtons.forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-view') === view);
        });

        // If we have a Leaflet map instance and layers
        if (window.orderMap && window.orderMap.__layers) {
            // Remove all vehicle markers first to prevent them from persisting
            try {
                removeAllVehicleMarkers();
            } catch (error) {
                console.warn('Error removing vehicle markers during map view switch:', error);
            }

            // Remove all existing layers
            Object.values(window.orderMap.__layers).forEach(layer => {
                if (window.orderMap.hasLayer(layer)) {
                    window.orderMap.removeLayer(layer);
                }
            });

            // Add the selected layer
            if (view === 'standard' && window.orderMap.__layers.standard) {
                window.orderMap.__layers.standard.addTo(window.orderMap);
            } else if (view === 'satellite' && window.orderMap.__layers.satellite) {
                window.orderMap.__layers.satellite.addTo(window.orderMap);
            } else if (view === 'traffic' && window.orderMap.__layers.traffic) {
                window.orderMap.__layers.traffic.addTo(window.orderMap);
            }

            // If we have a selected order, recenter the map
            // But only do this if we're not in the middle of another operation
            if (selectedOrderId) {
                // This will redraw all markers including the vehicle marker if needed
                // Use a longer timeout to ensure the map has fully loaded the new tile layer
                setTimeout(() => {
                    try {
                        centerMapOnOrder(selectedOrderId);
                    } catch (error) {
                        console.error('Error recentering map after view change:', error);
                    }
                }, 200);
            }
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

    // Initialize bookings tab if it's active on page load
    if (activeMainTab === 'bookings') {
        initBookingsTab();
    }

    // Observe changes in order list for dynamic updates
    const observer = new MutationObserver(updateActiveOrdersCount);
    observer.observe(orderList, { childList: true });
});