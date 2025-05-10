<?php
// Centralized constants for Ayskrim E-Commerce
// Usage: require_once __DIR__ . '/constants.php';

// User Roles
const ROLE_ADMIN = 'admin';
const ROLE_CUSTOMER = 'customer';

// Status Enums
const ORDER_STATUS = ['Pending', 'Processing', 'Out for Delivery', 'Delivered', 'Cancelled'];
const PAYMENT_STATUS = ['Pending', 'Paid', 'Failed', 'Refunded', 'Success'];
const DELIVERY_STATUS = ['Dispatched', 'In Transit', 'Delivered'];
const REVIEW_STATUS = ['Unverified', 'Verified'];
const EVENT_STATUS = ['Pending', 'Confirmed', 'Completed', 'Cancelled'];
const TICKET_STATUS = ['Open', 'In Progress', 'Closed'];

// Paths
const CUSTOMER_PAGES = [
    'menu' => '/ayskrimWebsite/customerPage/menu/menu.php',
    'cart' => '/ayskrimWebsite/customerPage/cart/cart.php',
    'checkout' => '/ayskrimWebsite/customerPage/checkout/checkout.php',
    'track_order' => '/ayskrimWebsite/customerPage/track-order/track-order.php',
    'orders' => '/ayskrimWebsite/customerPage/orders/orders.php',
    'profile' => '/ayskrimWebsite/customerPage/profile/profile.php',
    'wishlist' => '/ayskrimWebsite/customerPage/wishlist/wishlist.php',
    'search' => '/ayskrimWebsite/customerPage/search/search.php',
    'reviews' => '/ayskrimWebsite/customerPage/reviews/reviews.php',
];
const ADMIN_PAGES = [
    'dashboard' => '/ayskrimWebsite/adminPage/dashboard/dashboard.php',
    'orders' => '/ayskrimWebsite/adminPage/orders/orders.php',
    'menu' => '/ayskrimWebsite/adminPage/menu/menu.php',
    'inventory' => '/ayskrimWebsite/adminPage/inventory/inventory.php',
    'bookings' => '/ayskrimWebsite/adminPage/bookings/bookings.php',
    'reports' => '/ayskrimWebsite/adminPage/reports/reports.php',
    'expenses' => '/ayskrimWebsite/adminPage/expenses/expenses.php',
    'settings' => '/ayskrimWebsite/adminPage/settings/settings.php',
    'logs' => '/ayskrimWebsite/adminPage/logs/logs.php',
];

// API Endpoints
const API_AUTH = [
    'login' => '/api/auth/loginApi.php',
    'register' => '/api/auth/registerApi.php',
    'logout' => '/api/auth/logoutApi.php',
];
const API_ORDERS = [
    'update_status' => '/api/orders/updateOrderStatus.php',
    'get_location' => '/api/orders/getLiveLocation.php',
    'create' => '/api/orders/createOrder.php',
    'cancel' => '/api/orders/cancelOrder.php',
];
const API_WISHLIST = '/api/wishlist/wishlistApi.php';
const API_REVIEWS = [
    'submit' => '/api/reviews/submitReview.php',
    'get' => '/api/reviews/getReviews.php',
];
const API_SEARCH = '/api/search/searchApi.php';
const API_CHATBOT = '/api/chatbot/chatbot.php';
