<?php
// Sample order/booking data
// In a real application, this would come from your database
$sampleOrders = [
  [
    "id" => "ord-001",
    "title" => "Ice Cream Cake",
    "date" => "2025-04-25T14:00:00",
    "status" => "completed",
    "amount" => "$35.99",
    "type" => "order",
    "items" => ["Vanilla Ice Cream Cake", "Candles"],
    "image" => "/placeholder.svg?height=80&width=80",
  ],
  [
    "id" => "ord-002",
    "title" => "Strawberry Sundae",
    "date" => "2025-04-28T16:30:00",
    "status" => "completed",
    "amount" => "$12.50",
    "type" => "order",
    "items" => ["Strawberry Sundae", "Extra Toppings"],
    "image" => "/placeholder.svg?height=80&width=80",
  ],
  [
    "id" => "ord-003",
    "title" => "Chocolate Milkshake",
    "date" => "2025-05-01T10:15:00",
    "status" => "completed",
    "amount" => "$8.99",
    "type" => "order",
    "items" => ["Large Chocolate Milkshake"],
    "image" => "/placeholder.svg?height=80&width=80",
  ],
  [
    "id" => "ord-004",
    "title" => "Birthday Party",
    "date" => "2025-05-05T15:00:00",
    "status" => "upcoming",
    "amount" => "$89.99",
    "type" => "booking",
    "items" => ["10 Ice Cream Cups", "Birthday Cake", "Party Supplies"],
    "image" => "/placeholder.svg?height=80&width=80",
  ],
  [
    "id" => "ord-005",
    "title" => "Banana Split",
    "date" => "2025-05-10T13:45:00",
    "status" => "upcoming",
    "amount" => "$9.50",
    "type" => "order",
    "items" => ["Banana Split", "Extra Cherries"],
    "image" => "/placeholder.svg?height=80&width=80",
  ],
  [
    "id" => "ord-006",
    "title" => "Corporate Event",
    "date" => "2025-05-15T11:00:00",
    "status" => "upcoming",
    "amount" => "$150.00",
    "type" => "booking",
    "items" => ["25 Ice Cream Cups", "5 Toppings", "Delivery"],
    "image" => "/placeholder.svg?height=80&width=80",
  ]
];

// Convert PHP array to JSON for JavaScript
$ordersJson = json_encode($sampleOrders);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Calendar</title>
    <link rel="stylesheet" href="calendar.css">
    <!-- Updated Lucide icons loading -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        // Make sure Lucide is loaded before using it
        window.onload = function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            } else {
                console.error('Lucide library not loaded properly');
            }
        };
    </script>
</head>
<body>
    <div class="container">
        <div class="max-w-7xl mx-auto">
            <div class="card">
                <div class="card-content">
                    <!-- Calendar View Only -->
                    <div class="view-controls">
                        <!-- Calendar icon removed as requested -->
                    </div>

                    <!-- Search removed and Filters repositioned -->
                    <div class="search-filters">
                        <!-- Date range picker moved to search position -->
                        <div class="date-range-picker main-filter">
                            <button id="date-range-btn" class="btn btn-outline">
                                <i data-lucide="calendar" class="icon-pink"></i>
                                <span id="date-range-text">Filter by date range</span>
                            </button>
                            <div id="date-range-popup" class="popup">
                                <div id="date-range-calendar" class="calendar-container"></div>
                                <div class="popup-footer">
                                    <button id="clear-date-range" class="btn btn-ghost">Clear</button>
                                    <button id="apply-date-range" class="btn btn-pink">Apply</button>
                                </div>
                            </div>
                        </div>

                        <div class="filter-select">
                            <select id="status-filter" class="select-input">
                                <option value="all">All Statuses</option>
                                <option value="completed">Completed</option>
                                <option value="upcoming">Upcoming</option>
                            </select>
                        </div>

                        <div class="filter-select">
                            <select id="type-filter" class="select-input">
                                <option value="all">All Types</option>
                                <option value="order">Orders</option>
                                <option value="booking">Bookings</option>
                            </select>
                        </div>
                    </div>

                    <!-- Selected Date Display (initially hidden) -->
                    <div id="selected-date-container" class="selected-date-container" style="display: none;">
                        <div class="selected-date-text">
                            <span class="font-medium">Selected date:</span> <span id="selected-date-value"></span>
                        </div>
                        <button id="clear-selected-date" class="btn btn-ghost btn-sm">
                            <i data-lucide="x" class="icon-small"></i>
                            Clear
                        </button>
                    </div>

                    <!-- Loading State (initially shown) -->
                    <div id="loading-container" class="loading-container">
                        <div class="loading-spinner"></div>
                        <p class="loading-text">Loading your orders...</p>
                    </div>

                    <!-- Calendar View -->
                    <div id="calendar-view" class="calendar-view" style="display: none;">
                        <div class="date-navigation">
                            <div class="month-year-controls">
                                <div class="current-month-year-wrapper">
                                    <h3 class="current-month-year">May 2025</h3>
                                </div>
                                <div class="month-selector">
                                    <button id="prev-month-btn" class="nav-btn">
                                        <i data-lucide="chevron-left" class="nav-icon"></i>
                                    </button>
                                    <div class="select-wrapper">
                                        <select id="month-select" class="month-select">
                                            <option value="0">January</option>
                                            <option value="1">February</option>
                                            <option value="2">March</option>
                                            <option value="3">April</option>
                                            <option value="4">May</option>
                                            <option value="5">June</option>
                                            <option value="6">July</option>
                                            <option value="7">August</option>
                                            <option value="8">September</option>
                                            <option value="9">October</option>
                                            <option value="10">November</option>
                                            <option value="11">December</option>
                                        </select>
                                        <i data-lucide="chevron-down" class="select-icon"></i>
                                    </div>
                                    <button id="next-month-btn" class="nav-btn">
                                        <i data-lucide="chevron-right" class="nav-icon"></i>
                                    </button>
                                </div>

                                <div class="year-selector">
                                    <button id="prev-year-btn" class="nav-btn">
                                        <i data-lucide="chevron-left" class="nav-icon"></i>
                                    </button>
                                    <div class="select-wrapper">
                                        <select id="year-select" class="year-select"></select>
                                        <i data-lucide="chevron-down" class="select-icon"></i>
                                    </div>
                                    <button id="next-year-btn" class="nav-btn">
                                        <i data-lucide="chevron-right" class="nav-icon"></i>
                                    </button>
                                </div>

                                <div id="date-range-filter-indicator" class="date-filter-indicator" style="display: none;">
                                    <button id="clear-date-filter" class="clear-filter-btn">
                                        Clear date filter
                                    </button>
                                </div>
                            </div>

                            <div class="current-time" id="current-time">
                                <i data-lucide="clock" class="clock-icon"></i>
                                <span id="time-display"></span>
                            </div>
                        </div>

                        <div class="calendar-header">
                            <div class="weekday">Sun</div>
                            <div class="weekday">Mon</div>
                            <div class="weekday">Tue</div>
                            <div class="weekday">Wed</div>
                            <div class="weekday">Thu</div>
                            <div class="weekday">Fri</div>
                            <div class="weekday">Sat</div>
                        </div>

                        <div id="calendar-grid" class="calendar-grid">
                            <!-- Calendar days will be generated here by JavaScript -->
                        </div>
                    </div>

                    <!-- Grid View -->
                    <div id="grid-view" class="grid-view" style="display: none;">
                        <div id="no-items-message" class="no-items-message" style="display: none;">
                            <div class="icon-container">
                                <i data-lucide="calendar" class="large-icon"></i>
                            </div>
                            <h3 class="no-items-title">No items found</h3>
                            <p id="no-items-description" class="no-items-description">
                                You don't have any items matching the selected filters
                            </p>
                            <button id="clear-all-filters" class="btn btn-outline">Clear filters</button>
                        </div>

                        <div id="orders-grid" class="orders-grid">
                            <!-- Order cards will be generated here by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Modal (initially hidden) -->
    <div id="order-modal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <div class="modal-title">
                    <div id="modal-icon-container" class="modal-icon-container"></div>
                    <h3 id="modal-title" class="modal-title-text"></h3>
                </div>
                <button id="close-modal" class="close-modal-btn">
                    <i data-lucide="x" class="close-icon"></i>
                </button>
            </div>
            <div class="modal-content">
                <div class="modal-details">
                    <div class="detail-row">
                        <div>
                            <p class="detail-label">Date & Time</p>
                            <p id="modal-datetime" class="detail-value"></p>
                        </div>
                        <div id="modal-status" class="status-badge"></div>
                    </div>

                    <div class="detail-section">
                        <p class="detail-label">Type</p>
                        <div class="type-container">
                            <div id="modal-type-icon" class="type-icon"></div>
                            <span id="modal-type" class="type-text"></span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <p class="detail-label">Items</p>
                        <div class="items-container">
                            <ul id="modal-items" class="items-list"></ul>
                        </div>
                    </div>

                    <div class="detail-section">
                        <p class="detail-label">Total</p>
                        <p id="modal-amount" class="amount-value"></p>
                    </div>

                    <div id="modal-image-container" class="modal-image-container">
                        <img id="modal-image" src="/placeholder.svg" alt="Order image" class="modal-image">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="modal-close-btn" class="btn btn-outline">Close</button>
                <button id="modal-action-btn" class="btn btn-pink"></button>
            </div>
        </div>
    </div>

    <?php
    // Convert the PHP array to a JSON string for JavaScript
    $ordersJson = json_encode($sampleOrders);
    ?>
    <script>
        // Pass PHP data to JavaScript
        const sampleOrders = <?php echo $ordersJson; ?>;
    </script>
    <script src="calendar.js"></script>
</body>
</html>