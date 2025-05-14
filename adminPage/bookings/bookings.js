/**
 * Booking Management JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add CSS for booking-specific elements
    const style = document.createElement('style');
    style.textContent = `
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: var(--spacing-6);
            margin-bottom: var(--spacing-6);
        }

        .stat-card {
            background-color: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            padding: var(--spacing-6);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid var(--gray-200);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-200);
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--spacing-3);
        }

        .stat-title {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-500);
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-icon.blue {
            background-color: rgba(0, 0, 255, 0.1);
            color: var(--status-out-for-delivery);
        }

        .stat-icon.green {
            background-color: rgba(0, 128, 0, 0.1);
            color: var(--status-delivered);
        }

        .stat-icon.yellow {
            background-color: rgba(255, 165, 0, 0.1);
            color: var(--status-preparing);
        }

        .stat-icon.red {
            background-color: rgba(255, 0, 0, 0.1);
            color: var(--status-cancelled);
        }

        .stat-icon.gray {
            background-color: rgba(128, 128, 128, 0.1);
            color: var(--status-pending);
        }

        .stat-icon.primary {
            background-color: var(--primary-100);
            color: var(--primary-600);
        }

        .stat-value {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        /* Booking details styles */
        .booking-details {
            font-size: 0.95rem;
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-6);
            flex-wrap: wrap;
            gap: var(--spacing-4);
        }

        .booking-actions {
            display: flex;
            gap: var(--spacing-2);
            flex-wrap: wrap;
        }

        .booking-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: var(--spacing-4);
            margin-bottom: var(--spacing-6);
            background-color: var(--gray-50);
            border-radius: var(--radius-lg);
            padding: var(--spacing-4);
        }

        .booking-info-item {
            padding: var(--spacing-3);
            background-color: white;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
        }

        .info-label {
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: var(--spacing-2);
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
        }

        .info-value {
            color: var(--gray-900);
        }

        .booking-section {
            margin-bottom: var(--spacing-6);
        }

        .booking-section h5 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: var(--spacing-3);
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
        }

        .flavor-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
            gap: var(--spacing-2);
        }

        .flavor-list li {
            background-color: var(--primary-50);
            color: var(--primary-700);
            padding: var(--spacing-1) var(--spacing-3);
            border-radius: var(--radius-full);
            font-size: 0.875rem;
        }

        .timeline {
            margin-top: var(--spacing-4);
        }

        .timeline-item {
            display: flex;
            gap: var(--spacing-3);
            margin-bottom: var(--spacing-4);
            position: relative;
        }

        .timeline-item:not(:last-child):before {
            content: '';
            position: absolute;
            top: 30px;
            left: 16px;
            bottom: -30px;
            width: 2px;
            background-color: var(--gray-200);
        }

        .timeline-icon {
            width: 32px;
            height: 32px;
            background-color: var(--primary-100);
            color: var(--primary-600);
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            z-index: 1;
        }

        .timeline-content {
            flex: 1;
        }

        .timeline-title {
            font-weight: 600;
            color: var(--gray-800);
        }

        .timeline-date {
            font-size: 0.875rem;
            color: var(--gray-500);
        }

        /* Fix for modal */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .modal-backdrop.active {
            opacity: 1;
            visibility: visible;
        }

        .modal {
            background-color: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            transform: scale(0.95);
            opacity: 0;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        .modal-backdrop.active .modal {
            transform: scale(1);
            opacity: 1;
        }

        /* Calendar tooltip */
        .fc-tooltip {
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .tooltip-title {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .tooltip-time, .tooltip-status, .tooltip-location {
            margin-bottom: 2px;
        }

        /* Dropdown menu styling */
        .dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            z-index: 10;
            min-width: 180px;
            padding: 0.5rem 0;
            margin: 0.125rem 0 0;
            background-color: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s ease;
        }

        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            color: var(--gray-700);
            text-decoration: none;
            white-space: nowrap;
            transition: background-color 0.2s ease;
        }

        .dropdown-item:hover {
            background-color: var(--gray-100);
            color: var(--primary-600);
        }

        .dropdown-item i {
            margin-right: 0.5rem;
            width: 16px;
            text-align: center;
        }

        /* View options styling */
        .view-options {
            display: flex;
            align-items: center;
            background-color: var(--gray-100);
            border-radius: var(--radius-lg);
            padding: 2px;
            margin-right: var(--spacing-3);
        }

        .view-btn {
            background: none;
            border: none;
            padding: var(--spacing-2) var(--spacing-3);
            border-radius: var(--radius-md);
            cursor: pointer;
            color: var(--gray-600);
            transition: all 0.2s ease;
        }

        .view-btn:hover {
            color: var(--primary-600);
            background-color: var(--gray-200);
        }

        .view-btn.active {
            background-color: white;
            color: var(--primary-600);
            box-shadow: var(--shadow-sm);
        }

        .card-actions {
            display: flex;
            align-items: center;
        }

        /* Filter section styling */
        .filter-section {
            background-color: var(--gray-50);
            border-radius: var(--radius-lg);
            padding: var(--spacing-4);
            margin-bottom: var(--spacing-6);
            border: 1px solid var(--gray-200);
        }

        .filter-form .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--spacing-3);
            align-items: flex-end;
        }

        .filter-form .form-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-form .form-group:last-child {
            display: flex;
            gap: var(--spacing-2);
        }

        @media (max-width: 768px) {
            .filter-form .form-row {
                grid-template-columns: 1fr;
            }

            .filter-form .form-group:last-child {
                margin-top: var(--spacing-2);
            }
        }

        /* Table styling */
        .admin-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .admin-table th {
            background-color: var(--gray-50);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: var(--spacing-3) var(--spacing-4);
            border-bottom: 2px solid var(--gray-200);
            text-align: left;
        }

        .admin-table td {
            padding: var(--spacing-3) var(--spacing-4);
            border-bottom: 1px solid var(--gray-200);
            vertical-align: middle;
        }

        .admin-table tbody tr:hover {
            background-color: var(--gray-50);
        }

        /* Action buttons styling */
        .action-buttons {
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            background: none;
            border: none;
            color: var(--gray-600);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-icon:hover {
            background-color: var(--gray-100);
            color: var(--primary-600);
        }

        .view-booking {
            background-color: var(--primary-50);
            color: var(--primary-600);
        }

        .view-booking:hover {
            background-color: var(--primary-100);
            color: var(--primary-700);
        }

        /* Badge styling */
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge.success {
            background-color: rgba(0, 128, 0, 0.1);
            color: var(--status-delivered);
        }

        .badge.warning {
            background-color: rgba(255, 165, 0, 0.1);
            color: var(--status-preparing);
        }

        .badge.danger {
            background-color: rgba(255, 0, 0, 0.1);
            color: var(--status-cancelled);
        }

        .badge.primary {
            background-color: rgba(0, 0, 255, 0.1);
            color: var(--status-out-for-delivery);
        }

        .badge.gray {
            background-color: rgba(128, 128, 128, 0.1);
            color: var(--status-pending);
        }

        /* Utility classes */
        .mr-1 {
            margin-right: var(--spacing-1);
        }

        .mb-0 {
            margin-bottom: 0;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .booking-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .booking-info-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .booking-actions {
                width: 100%;
                justify-content: space-between;
            }
        }
    `;
    document.head.appendChild(style);

    // Initialize date pickers
    if (typeof flatpickr !== 'undefined') {
        flatpickr('.date-picker', {
            dateFormat: 'Y-m-d',
            allowInput: true
        });
    }

    // Initialize calendar if view is active
    if (document.getElementById('calendar-view').classList.contains('active')) {
        initializeCalendar();
    }

    // View toggle buttons
    const viewButtons = document.querySelectorAll('.view-btn');
    const viewContainers = document.querySelectorAll('.view-container');

    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const view = this.getAttribute('data-view');

            // Update active button
            viewButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            // Show selected view
            viewContainers.forEach(container => {
                container.classList.toggle('active', container.id === view + '-view');
            });

            // Initialize calendar if calendar view is selected
            if (view === 'calendar' && typeof FullCalendar !== 'undefined') {
                initializeCalendar();
            }

            // Update URL without reloading
            const url = new URL(window.location.href);
            url.searchParams.set('view', view);
            window.history.replaceState({}, '', url);
        });
    });

    // View booking details
    function initializeViewBookingButtons() {
        const viewBookingButtons = document.querySelectorAll('.view-booking');
        const bookingDetailsModal = document.getElementById('booking-details-modal');
        const bookingDetailsContent = document.querySelector('.booking-details-content');
        const modalClose = document.querySelector('.modal-close');

        viewBookingButtons.forEach(button => {
            button.addEventListener('click', function() {
                const bookingId = this.getAttribute('data-id');

                // Show modal
                bookingDetailsModal.classList.add('active');
                document.body.style.overflow = 'hidden'; // Prevent background scrolling

                // Show loading spinner
                bookingDetailsContent.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i></div>';

                // Fetch booking details
                fetch(`/ayskrimWebsite/api/events/getBookingDetails.php?id=${bookingId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            displayBookingDetails(data.booking);
                        } else {
                            bookingDetailsContent.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching booking details:', error);
                        bookingDetailsContent.innerHTML = '<div class="alert alert-danger">Failed to load booking details. Please try again.</div>';
                    });
            });
        });

        // Close modal
        if (modalClose) {
            modalClose.addEventListener('click', function() {
                bookingDetailsModal.classList.remove('active');
                document.body.style.overflow = ''; // Restore scrolling
            });
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === bookingDetailsModal) {
                bookingDetailsModal.classList.remove('active');
                document.body.style.overflow = ''; // Restore scrolling
            }
        });
    }

    // Initialize view booking buttons
    initializeViewBookingButtons();

    // Re-initialize view booking buttons after AJAX content is loaded
    document.addEventListener('contentLoaded', function() {
        initializeViewBookingButtons();
    });

    // Export bookings data
    const exportButton = document.querySelector('.export-btn');
    if (exportButton) {
        exportButton.addEventListener('click', function() {
            exportBookingsToCSV();
        });
    }

    // Dropdown toggle
    function initializeDropdowns() {
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const dropdown = this.nextElementSibling;

                // Close all other dropdowns
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    if (menu !== dropdown) {
                        menu.classList.remove('show');
                    }
                });

                // Toggle current dropdown
                dropdown.classList.toggle('show');
            });
        });

        // Initialize dropdown menu items
        const dropdownItems = document.querySelectorAll('.dropdown-menu a');

        dropdownItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const url = this.getAttribute('href');
                const statusText = this.textContent.trim();

                // Show confirmation
                if (confirm(`Are you sure you want to mark this booking as "${statusText}"?`)) {
                    // Send AJAX request to update status
                    fetch(`/ayskrimWebsite/api/events/updateBookingStatus.php${url.substring(url.indexOf('?'))}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                // Reload page to show updated data
                                window.location.href = '?status_updated=1';
                            } else {
                                throw new Error(data.error || 'Failed to update booking status');
                            }
                        })
                        .catch(error => {
                            console.error('Error updating booking status:', error);
                            alert('An error occurred while updating the booking status. Please try again.');
                        });
                }
            });
        });
    }

    // Initialize dropdowns
    initializeDropdowns();

    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    });

    // Re-initialize dropdowns after AJAX content is loaded
    document.addEventListener('contentLoaded', function() {
        initializeDropdowns();
    });
});

/**
 * Initialize FullCalendar
 */
function initializeCalendar() {
    const calendarEl = document.getElementById('booking-calendar');

    if (!calendarEl || typeof FullCalendar === 'undefined' || !window.calendarEvents) {
        return;
    }

    // Clear existing calendar
    calendarEl.innerHTML = '';

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: window.calendarEvents,
        eventTimeFormat: {
            hour: 'numeric',
            minute: '2-digit',
            meridiem: 'short'
        },
        eventClick: function(info) {
            // Find and click the corresponding view button
            const viewButton = document.querySelector(`.view-booking[data-id="${info.event.id}"]`);
            if (viewButton) {
                viewButton.click();
            }
        },
        eventDidMount: function(info) {
            // Add tooltip with event details
            const tooltip = document.createElement('div');
            tooltip.classList.add('calendar-tooltip');

            const status = info.event.extendedProps.status;
            const location = info.event.extendedProps.location;

            tooltip.innerHTML = `
                <div class="tooltip-title">${info.event.title}</div>
                <div class="tooltip-time">${info.event.start.toLocaleTimeString()} - ${info.event.end.toLocaleTimeString()}</div>
                <div class="tooltip-status">Status: ${status}</div>
                <div class="tooltip-location">Location: ${location}</div>
            `;

            // Add custom tooltip functionality
            info.el.setAttribute('title', info.event.title);

            // Simple tooltip hover effect
            info.el.addEventListener('mouseover', function() {
                const tooltipEl = document.createElement('div');
                tooltipEl.className = 'fc-tooltip';
                tooltipEl.innerHTML = tooltip.innerHTML;
                tooltipEl.style.position = 'absolute';
                tooltipEl.style.zIndex = 10000;
                tooltipEl.style.backgroundColor = 'white';
                tooltipEl.style.padding = '8px';
                tooltipEl.style.borderRadius = '4px';
                tooltipEl.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
                tooltipEl.style.maxWidth = '300px';

                document.body.appendChild(tooltipEl);

                const rect = info.el.getBoundingClientRect();
                tooltipEl.style.top = (rect.top - tooltipEl.offsetHeight - 5) + 'px';
                tooltipEl.style.left = (rect.left + (rect.width / 2) - (tooltipEl.offsetWidth / 2)) + 'px';

                this.tooltipEl = tooltipEl;
            });

            info.el.addEventListener('mouseout', function() {
                if (this.tooltipEl) {
                    document.body.removeChild(this.tooltipEl);
                    this.tooltipEl = null;
                }
            });
        }
    });

    calendar.render();
}

/**
 * Display booking details in modal
 */
function displayBookingDetails(booking) {
    const bookingDetailsContent = document.querySelector('.booking-details-content');

    // Parse special requests for flavors
    let flavorsHtml = '<p>No flavors selected</p>';
    if (booking.special_requests) {
        try {
            const specialRequests = JSON.parse(booking.special_requests);
            if (specialRequests.selected_flavors && specialRequests.selected_flavors.length > 0) {
                flavorsHtml = '<ul class="flavor-list">';
                specialRequests.selected_flavors.forEach(flavor => {
                    flavorsHtml += `<li>${flavor}</li>`;
                });
                flavorsHtml += '</ul>';
            }
        } catch (e) {
            console.error('Error parsing special requests:', e);
        }
    }

    // Format date and time
    const eventDate = new Date(booking.event_date);
    const formattedDate = eventDate.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    const startTime = new Date(`${booking.event_date}T${booking.start_time}`);
    const endTime = new Date(`${booking.event_date}T${booking.end_time}`);
    const formattedTime = `${startTime.toLocaleTimeString([], {hour: 'numeric', minute:'2-digit'})} - ${endTime.toLocaleTimeString([], {hour: 'numeric', minute:'2-digit'})}`;

    // Get status badge class
    const statusClass = booking.status === 'Completed' ? 'success' :
                        (booking.status === 'Confirmed' ? 'primary' :
                        (booking.status === 'Pending' ? 'warning' : 'danger'));

    // Get payment status badge class
    const paymentStatusClass = booking.payment_status === 'Success' ? 'success' :
                              (booking.payment_status === 'Pending' ? 'warning' : 'danger');

    // Build HTML
    const html = `
        <div class="booking-details">
            <div class="booking-header mb-4">
                <div class="d-flex align-center gap-2">
                    <h4 class="mb-0">Booking #${booking.id}</h4>
                    <span class="badge ${statusClass}">${booking.status}</span>
                </div>
                <div class="booking-actions">
                    <a href="?action=update_status&id=${booking.id}&status=Pending" class="btn btn-sm ${booking.status === 'Pending' ? 'btn-primary' : 'btn-outline'} mr-1">
                        <i class="fas fa-clock"></i> Pending
                    </a>
                    <a href="?action=update_status&id=${booking.id}&status=Confirmed" class="btn btn-sm ${booking.status === 'Confirmed' ? 'btn-primary' : 'btn-outline'} mr-1">
                        <i class="fas fa-check"></i> Confirm
                    </a>
                    <a href="?action=update_status&id=${booking.id}&status=Completed" class="btn btn-sm ${booking.status === 'Completed' ? 'btn-success' : 'btn-outline'} mr-1">
                        <i class="fas fa-check-circle"></i> Complete
                    </a>
                    <a href="?action=update_status&id=${booking.id}&status=Cancelled" class="btn btn-sm ${booking.status === 'Cancelled' ? 'btn-danger' : 'btn-outline'}">
                        <i class="fas fa-ban"></i> Cancel
                    </a>
                </div>
            </div>

            <div class="booking-info-grid">
                <div class="booking-info-item">
                    <div class="info-label"><i class="fas fa-user text-primary"></i> Customer</div>
                    <div class="info-value">${booking.full_name}</div>
                </div>

                <div class="booking-info-item">
                    <div class="info-label"><i class="fas fa-envelope text-primary"></i> Contact</div>
                    <div class="info-value">
                        <div>${booking.email}</div>
                        <div>${booking.phone || 'N/A'}</div>
                    </div>
                </div>

                <div class="booking-info-item">
                    <div class="info-label"><i class="fas fa-calendar-day text-primary"></i> Event Date</div>
                    <div class="info-value">${formattedDate}</div>
                </div>

                <div class="booking-info-item">
                    <div class="info-label"><i class="fas fa-clock text-primary"></i> Event Time</div>
                    <div class="info-value">${formattedTime}</div>
                </div>

                <div class="booking-info-item">
                    <div class="info-label"><i class="fas fa-ice-cream text-primary"></i> Package</div>
                    <div class="info-value">${booking.package_type}</div>
                </div>

                <div class="booking-info-item">
                    <div class="info-label"><i class="fas fa-users text-primary"></i> Guest Count</div>
                    <div class="info-value">${booking.guest_count} people</div>
                </div>

                <div class="booking-info-item">
                    <div class="info-label"><i class="fas fa-map-marker-alt text-primary"></i> Venue</div>
                    <div class="info-value">${booking.venue_address}</div>
                </div>

                <div class="booking-info-item">
                    <div class="info-label"><i class="fas fa-peso-sign text-primary"></i> Amount</div>
                    <div class="info-value">₱${parseFloat(booking.total_amount).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                </div>

                <div class="booking-info-item">
                    <div class="info-label"><i class="fas fa-credit-card text-primary"></i> Payment Method</div>
                    <div class="info-value">${booking.payment_method || 'N/A'}</div>
                </div>

                <div class="booking-info-item">
                    <div class="info-label"><i class="fas fa-money-check text-primary"></i> Payment Status</div>
                    <div class="info-value">
                        <span class="badge ${paymentStatusClass}">${booking.payment_status || 'N/A'}</span>
                    </div>
                </div>
            </div>

            <div class="booking-section">
                <h5><i class="fas fa-ice-cream"></i> Selected Flavors</h5>
                ${flavorsHtml}
            </div>

            <div class="booking-section">
                <h5><i class="fas fa-comment-alt"></i> Special Requests</h5>
                <p>${booking.special_requests ? booking.special_requests : 'None'}</p>
            </div>

            <div class="booking-section">
                <h5><i class="fas fa-history"></i> Booking Timeline</h5>
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-title">Booking Created</div>
                            <div class="timeline-date">${new Date(booking.created_at).toLocaleString()}</div>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-title">Last Updated</div>
                            <div class="timeline-date">${new Date(booking.updated_at).toLocaleString()}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    bookingDetailsContent.innerHTML = html;

    // Add event listeners to the action buttons
    setTimeout(() => {
        const actionButtons = document.querySelectorAll('.booking-actions a');
        actionButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                const url = this.getAttribute('href');
                const statusText = this.textContent.trim();

                // Show confirmation
                if (confirm(`Are you sure you want to mark this booking as "${statusText}"?`)) {
                    // Show loading state
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                    // Disable all buttons
                    actionButtons.forEach(btn => btn.classList.add('disabled'));

                    // Send AJAX request to update status
                    fetch(`/ayskrimWebsite/api/events/updateBookingStatus.php${url.substring(url.indexOf('?'))}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                // Close modal
                                document.getElementById('booking-details-modal').classList.remove('active');
                                document.body.style.overflow = '';

                                // Reload page to show updated data
                                window.location.href = '?status_updated=1';
                            } else {
                                throw new Error(data.error || 'Failed to update booking status');
                            }
                        })
                        .catch(error => {
                            console.error('Error updating booking status:', error);
                            alert('An error occurred while updating the booking status. Please try again.');

                            // Restore buttons
                            actionButtons.forEach(btn => btn.classList.remove('disabled'));

                            // Restore original button text
                            const statusClass = this.classList.contains('btn-primary') ? 'Pending' :
                                              (this.classList.contains('btn-success') ? 'Complete' :
                                              (this.classList.contains('btn-danger') ? 'Cancel' : 'Confirm'));

                            const iconClass = statusClass === 'Pending' ? 'fa-clock' :
                                           (statusClass === 'Complete' ? 'fa-check-circle' :
                                           (statusClass === 'Cancel' ? 'fa-ban' : 'fa-check'));

                            this.innerHTML = `<i class="fas ${iconClass}"></i> ${statusClass}`;
                        });
                } else {
                    // User cancelled the action
                    return false;
                }
            });
        });
    }, 100);
}

/**
 * Export bookings data to CSV
 */
function exportBookingsToCSV() {
    // Get table headers
    const headers = Array.from(document.querySelectorAll('.admin-table thead th'))
        .map(th => th.textContent.trim())
        .filter(header => header !== 'Actions');

    // Get table rows
    const rows = Array.from(document.querySelectorAll('.admin-table tbody tr'));

    // Convert rows to CSV data
    const csvData = rows.map(row => {
        return Array.from(row.querySelectorAll('td'))
            .slice(0, -1) // Remove actions column
            .map(cell => {
                // Get text content, removing any HTML
                let text = cell.textContent.trim();
                // Escape double quotes
                text = text.replace(/"/g, '""');
                return `"${text}"`;
            });
    });

    // Create CSV content
    const csvContent = [
        headers.join(','),
        ...csvData.map(row => row.join(','))
    ].join('\n');

    // Create download link
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', 'bookings.csv');
    link.style.display = 'none';

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
