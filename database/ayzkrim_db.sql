-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2025 at 04:26 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ayzkrim_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(105, 9, 9, 2, '2025-05-13 18:12:57', '2025-05-13 18:12:57');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0 COMMENT 'Soft delete category'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `image_url`, `is_active`, `is_deleted`) VALUES
(4, 'Bestseller', 'Our most popular flavors', NULL, 1, 0),
(5, 'Seasonal', 'Limited time seasonal flavors', NULL, 1, 0),
(6, 'Limited Edition', 'Exclusive limited edition treats', NULL, 1, 0),
(7, 'Packages', 'Freshly launched flavors', NULL, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `delivery_tracking`
--

CREATE TABLE `delivery_tracking` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `delivery_person` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `estimated_arrival` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('Dispatched','In Transit','Delivered') DEFAULT 'Dispatched',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `guest_count` int(11) NOT NULL,
  `venue_address` text DEFAULT NULL,
  `package_type` enum('Basic','Premium','Custom') NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `special_requests` text DEFAULT NULL,
  `status` enum('Pending','Confirmed','Completed','Cancelled') DEFAULT 'Pending',
  `is_deleted` tinyint(1) DEFAULT 0 COMMENT 'Soft delete event',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `user_id`, `event_date`, `start_time`, `end_time`, `guest_count`, `venue_address`, `package_type`, `total_amount`, `special_requests`, `status`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 9, '2025-09-09', '10:00:00', '14:00:00', 150, 'here', 'Basic', 3300.00, NULL, 'Pending', 0, '2025-05-14 01:14:41', '2025-05-14 01:14:41'),
(2, 9, '2025-07-11', '10:50:00', '13:43:00', 250, 'narnia', 'Premium', 5500.00, NULL, 'Completed', 0, '2025-05-14 01:17:30', '2025-05-14 01:17:55'),
(11, 9, '2025-11-11', '10:00:00', '14:00:00', 150, 'here', 'Basic', 3300.00, NULL, 'Pending', 0, '2025-05-14 02:24:21', '2025-05-14 02:24:21');

-- --------------------------------------------------------

--
-- Table structure for table `event_packages`
--

CREATE TABLE `event_packages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `max_guests` int(11) NOT NULL,
  `included_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`included_items`)),
  `is_active` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0 COMMENT 'Soft delete event package'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_payments`
--

CREATE TABLE `event_payments` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Credit Card','COD','PayPal','GCash') NOT NULL,
  `transaction_id` varchar(50) NOT NULL,
  `payment_status` enum('Success','Failed','Pending','Refunded') DEFAULT 'Pending',
  `payment_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_details`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_payments`
--

INSERT INTO `event_payments` (`id`, `event_id`, `user_id`, `amount`, `payment_method`, `transaction_id`, `payment_status`, `payment_details`, `created_at`) VALUES
(1, 11, 9, 3300.00, 'COD', 'EVT202505140424218945', 'Pending', '{\"payment_method\":\"COD\",\"payment_date\":\"2025-05-14 04:24:21\",\"event_date\":\"2025-11-11\",\"package_type\":\"Basic\",\"venue_address\":\"here\",\"guest_count\":150}', '2025-05-14 02:24:21');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `expense_type` varchar(100) NOT NULL,
  `vendor_name` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash','Credit Card','Bank Transfer') NOT NULL,
  `description` text DEFAULT NULL,
  `expense_date` date NOT NULL,
  `receipt_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guest_carts`
--

CREATE TABLE `guest_carts` (
  `token` varchar(36) NOT NULL COMMENT 'Unique guest identifier',
  `cart_data` text NOT NULL COMMENT 'JSON-encoded cart items',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_log`
--

CREATE TABLE `inventory_log` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity_change` int(11) NOT NULL,
  `action_type` enum('Restock','Sale','Adjustment','Waste') NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_type` enum('Pickup','Delivery') NOT NULL DEFAULT 'Delivery',
  `shipping_address` text NOT NULL,
  `special_instructions` text DEFAULT NULL,
  `tracking_code` varchar(20) NOT NULL,
  `estimated_delivery_time` timestamp NULL DEFAULT NULL,
  `order_status` enum('Pending','Preparing','Out for Delivery','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  `payment_status` enum('Pending','Paid','Failed','Refunded') NOT NULL DEFAULT 'Pending',
  `is_deleted` tinyint(1) DEFAULT 0 COMMENT 'Soft delete order',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `delivery_type`, `shipping_address`, `special_instructions`, `tracking_code`, `estimated_delivery_time`, `order_status`, `payment_status`, `is_deleted`, `created_at`, `updated_at`) VALUES
(7, 9, 4550.00, 'Delivery', 'Holy Child IT Academy, C. Bangoy Street, Barangay 32-D, Poblacion District, Davao City, Davao Region, 8000, Philippines', '', 'AYZ2025050990CCE2', NULL, 'Out for Delivery', 'Pending', 0, '2025-05-09 15:57:45', '2025-05-13 16:14:49'),
(8, 9, 6800.00, 'Delivery', 'President Diosdado P. Macapagal Highway, Santiago, Rang-ay, Lupon, Davao Oriental, Davao Region, 8208, Philippines', '', 'AYZ202505098DD0D5', NULL, 'Delivered', 'Pending', 0, '2025-05-09 16:50:16', '2025-05-13 23:02:35'),
(9, 9, 10000.00, 'Delivery', 'Kainan ni Maria, MacArthur Highway, Matina, 74-A Matina Crossing, Davao City, Davao Region, 8000, Philippines', 'faster', 'AYZ20250510E80549', NULL, 'Out for Delivery', 'Pending', 0, '2025-05-10 15:55:10', '2025-05-13 22:15:31'),
(10, 9, 1050.00, 'Delivery', 'Paniquian, Banaybanay, Davao Oriental, Davao Region, 8208, Philippines', '', 'AYZ2025051094583C', NULL, 'Preparing', 'Pending', 0, '2025-05-10 16:59:37', '2025-05-14 00:08:03'),
(11, 9, 1450.00, 'Delivery', 'Sampaguita, Taguibo, Mati, Davao Oriental, Davao Region, 8200, Philippines', 'none', 'AYZ20250510BB9CEA', NULL, 'Preparing', 'Pending', 0, '2025-05-10 17:10:19', '2025-05-14 00:08:11'),
(12, 9, 2100.00, 'Delivery', 'Nabunturan, San Isidro, Kaputian District, Samal, Davao del Norte, Davao Region, 8120, Philippines', '', 'AYZ20250510819A10', NULL, 'Delivered', 'Pending', 0, '2025-05-10 17:14:16', '2025-05-14 00:07:30'),
(13, 9, 650.00, 'Delivery', 'President Diosdado P. Macapagal Highway, Santiago, Rang-ay, Lupon, Davao Oriental, Davao Region, 8208, Philippines', '', 'AYZ2025051079B625', NULL, 'Delivered', 'Pending', 0, '2025-05-10 18:29:59', '2025-05-14 00:07:30'),
(14, 9, 1250.00, 'Delivery', 'Maragatas, Lupon, Davao Oriental, Davao Region, 8207, Philippines', '', 'AYZ202505103BEFF1', NULL, 'Pending', 'Pending', 0, '2025-05-10 19:43:47', '2025-05-14 00:08:24'),
(15, 9, 1400.00, 'Delivery', 'Davao Evangelical Church, Ignacio Villamor Street, Barangay 12-B, Poblacion District, Davao City, Davao Region, 8000, Philippines', '', 'AYZ20250513BBDFDB', NULL, 'Pending', 'Pending', 0, '2025-05-13 18:11:55', '2025-05-13 18:11:55');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `special_instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `subtotal`, `special_instructions`) VALUES
(15, 7, 11, 15, 300.00, 4500.00, ''),
(16, 8, 9, 15, 200.00, 3000.00, ''),
(17, 8, 10, 15, 250.00, 3750.00, ''),
(18, 9, 9, 16, 200.00, 3200.00, 'faster'),
(19, 9, 10, 15, 250.00, 3750.00, 'faster'),
(20, 9, 11, 10, 300.00, 3000.00, 'faster'),
(21, 10, 9, 5, 200.00, 1000.00, ''),
(22, 11, 9, 7, 200.00, 1400.00, 'none'),
(23, 12, 8, 4, 150.00, 600.00, ''),
(24, 12, 9, 6, 200.00, 1200.00, ''),
(25, 12, 10, 1, 250.00, 250.00, ''),
(26, 13, 8, 4, 150.00, 600.00, ''),
(27, 14, 8, 8, 150.00, 1200.00, ''),
(28, 15, 8, 5, 150.00, 750.00, ''),
(29, 15, 9, 3, 200.00, 600.00, '');

-- --------------------------------------------------------

--
-- Table structure for table `order_ratings`
--

CREATE TABLE `order_ratings` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL CHECK (`rating` between 1 and 5),
  `review` text DEFAULT NULL,
  `delivery_rating` int(1) DEFAULT NULL CHECK (`delivery_rating` between 1 and 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_timeline`
--

CREATE TABLE `order_timeline` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` enum('Pending','Processing','Out for Delivery','Delivered','Cancelled') NOT NULL,
  `description` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_timeline`
--

INSERT INTO `order_timeline` (`id`, `order_id`, `status`, `description`, `timestamp`) VALUES
(4, 7, 'Pending', 'Order has been placed and is awaiting confirmation', '2025-05-09 15:57:45'),
(5, 8, 'Pending', 'Order has been placed and is awaiting confirmation', '2025-05-09 16:50:16'),
(6, 9, 'Pending', 'Order has been placed and is awaiting confirmation', '2025-05-10 15:55:10'),
(7, 10, 'Pending', 'Order has been placed and is awaiting confirmation', '2025-05-10 16:59:37'),
(8, 11, 'Pending', 'Order has been placed and is awaiting confirmation', '2025-05-10 17:10:19'),
(9, 12, 'Pending', 'Order has been placed and is awaiting confirmation', '2025-05-10 17:14:16'),
(10, 13, 'Pending', 'Order has been placed and is awaiting confirmation', '2025-05-10 18:29:59'),
(11, 14, 'Pending', 'Order has been placed and is awaiting confirmation', '2025-05-10 19:43:47'),
(12, 15, 'Pending', 'Order has been placed and is awaiting confirmation', '2025-05-13 18:11:55');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Credit Card','COD','PayPal','GCash') NOT NULL,
  `transaction_id` varchar(50) NOT NULL,
  `payment_status` enum('Success','Failed','Pending','Refunded') DEFAULT 'Pending',
  `payment_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_details`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `user_id`, `amount`, `payment_method`, `transaction_id`, `payment_status`, `payment_details`, `created_at`) VALUES
(5, 7, 9, 4550.00, '', 'TRX202505091757457847', 'Pending', '{\"payment_method\":\"cod\",\"payment_date\":\"2025-05-09 17:57:45\",\"customer_name\":\"jinsbil\",\"customer_phone\":\"12131312313\"}', '2025-05-09 15:57:45'),
(6, 8, 9, 6800.00, 'GCash', 'TRX202505091850162914', 'Pending', '{\"payment_method\":\"gcash\",\"payment_date\":\"2025-05-09 18:50:16\",\"customer_name\":\"jinsbil\",\"customer_phone\":\"12131312313\"}', '2025-05-09 16:50:16'),
(7, 9, 9, 10000.00, '', 'TRX202505101755107074', 'Pending', '{\"payment_method\":\"cod\",\"payment_date\":\"2025-05-10 17:55:10\",\"customer_name\":\"Jensville\",\"customer_phone\":\"09285548332\"}', '2025-05-10 15:55:10'),
(8, 10, 9, 1050.00, '', 'TRX202505101859374546', 'Pending', '{\"payment_method\":\"cod\",\"payment_date\":\"2025-05-10 18:59:37\",\"customer_name\":\"Ror\",\"customer_phone\":\"09285548332\"}', '2025-05-10 16:59:37'),
(9, 11, 9, 1450.00, '', 'TRX202505101910192704', 'Pending', '{\"payment_method\":\"COD\",\"payment_date\":\"2025-05-10 19:10:19\",\"customer_name\":\"jinsbil\",\"customer_phone\":\"09285548332\"}', '2025-05-10 17:10:19'),
(10, 12, 9, 2100.00, 'COD', 'TRX202505101914166612', 'Pending', '{\"payment_method\":\"COD\",\"payment_date\":\"2025-05-10 19:14:16\",\"customer_name\":\"Arf\",\"customer_phone\":\"09285548332\"}', '2025-05-10 17:14:16'),
(11, 13, 9, 650.00, 'COD', 'TRX202505102029596835', 'Pending', '{\"payment_method\":\"COD\",\"payment_date\":\"2025-05-10 20:29:59\",\"customer_name\":\"jinsbil\",\"customer_phone\":\"09285548332\"}', '2025-05-10 18:29:59'),
(12, 14, 9, 1250.00, 'COD', 'TRX202505102143471205', 'Pending', '{\"payment_method\":\"COD\",\"payment_date\":\"2025-05-10 21:43:47\",\"customer_name\":\"jinsbil\",\"customer_phone\":\"09285548332\"}', '2025-05-10 19:43:47'),
(13, 15, 9, 1400.00, 'GCash', 'TRX202505132011554808', 'Pending', '{\"payment_method\":\"GCash\",\"payment_date\":\"2025-05-13 20:11:55\",\"customer_name\":\"Ror\",\"customer_phone\":\"09285548332\"}', '2025-05-13 18:11:55');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) NOT NULL,
  `image_url` varchar(255) DEFAULT 'default.png',
  `flavor_profile` varchar(100) DEFAULT NULL,
  `ingredients` text DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `availability_status` enum('Available','Out of Stock','Seasonal') DEFAULT 'Available',
  `is_active` tinyint(1) DEFAULT 1,
  `dietary_type` enum('Regular','Sugar-Free','Dairy-Free','Vegan') DEFAULT 'Regular',
  `is_deleted` tinyint(1) DEFAULT 0 COMMENT 'Soft delete product',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `category_id`, `image_url`, `flavor_profile`, `ingredients`, `stock`, `availability_status`, `is_active`, `dietary_type`, `is_deleted`, `created_at`, `updated_at`) VALUES
(8, 'Strawberry Dream', 'Sweet strawberry ice cream with fresh berries', 150.00, 4, '1.jpeg', 'Strawberry, Creamy', 'Strawberries, Cream, Sugar', 38, 'Available', 1, 'Regular', 0, '2025-05-08 01:59:20', '2025-05-13 18:11:55'),
(9, 'Forest Mama', 'Blueberry and raspberry blend with cream', 200.00, 5, '2.jpeg', 'Berry, Creamy', 'Blueberries, Raspberries, Cream, Sugar', 13, 'Available', 1, 'Regular', 0, '2025-05-08 01:59:20', '2025-05-13 18:11:55'),
(10, 'Forest Prince', 'Mint chocolate-infused ice cream', 250.00, 6, '3.jpeg', 'Mint, Chocolate', 'Mint, Chocolate, Cream, Sugar', 1, 'Available', 1, 'Regular', 0, '2025-05-08 01:59:20', '2025-05-10 17:14:16'),
(11, 'Purple Paradise', 'Creamy vanilla with ube swirl and coconut', 300.00, 7, '4.jpeg', 'Ube, Coconut, Vanilla', 'Ube, Coconut, Vanilla, Cream, Sugar', 3, 'Available', 1, 'Regular', 0, '2025-05-08 01:59:20', '2025-05-10 15:55:10');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_verified_purchase` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('Open','In Progress','Closed') DEFAULT 'Open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('admin','customer') NOT NULL DEFAULT 'customer',
  `profile_picture` varchar(255) DEFAULT 'default.png',
  `verification_status` enum('Unverified','Verified') DEFAULT 'Unverified',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Soft deactivate user',
  `is_deleted` tinyint(1) DEFAULT 0 COMMENT 'Soft delete user',
  `password_reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `username`, `email`, `password`, `phone`, `address`, `role`, `profile_picture`, `verification_status`, `is_active`, `is_deleted`, `password_reset_token`, `token_expiry`, `email_verified_at`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'kirk', 'kg_fc65f1', 'kg@gmail.com', '$2y$10$GKZbsD7Oxy3MSODA1T0b2OcyKNsMMLPJ5ZBPsmmtJl0bCqPLEQYum', NULL, NULL, 'customer', 'default.png', 'Unverified', 1, 0, NULL, NULL, NULL, NULL, '2025-05-07 19:27:39', '2025-05-07 19:27:39'),
(2, 'Admin User', 'admin', 'admin@ayskrim.com', '$2y$10$adminpwseed', '09170000001', '123 Admin St, City', 'admin', 'admin.png', 'Verified', 1, 0, NULL, NULL, NULL, '2025-05-07 19:58:45', '2025-05-07 19:58:45', '2025-05-07 19:58:45'),
(3, 'Jane Customer', 'jane', 'jane@ayskrim.com', '$2y$10$janepwseed', '09170000002', '456 Customer Ave, City', 'customer', '1.png', 'Verified', 1, 0, NULL, NULL, NULL, '2025-05-07 19:58:45', '2025-05-07 19:58:45', '2025-05-07 19:58:45'),
(4, 'John Customer', 'john', 'john@ayskrim.com', '$2y$10$johnpwseed', '09170000003', '789 Customer Blvd, City', 'customer', '2.png', 'Unverified', 1, 0, NULL, NULL, NULL, NULL, '2025-05-07 19:58:45', '2025-05-07 19:58:45'),
(8, 'kerrrk', 'kk_29a453', 'kk@gmail.com', '$2y$10$r7DdjjEHXsoV11eFbcKXkebpvSww3tF4cqe/Bb7eYpzqrDAQYsnKS', NULL, NULL, 'customer', 'default.png', 'Unverified', 1, 0, NULL, NULL, NULL, NULL, '2025-05-08 02:06:52', '2025-05-08 02:06:52'),
(9, 'jinsbil', 'jj_3053a4', 'jj@gmail.com', '$2y$10$Wx3uo8DbQ4lBCJJbH.ezUOZKWLBBvM9K15EbkpGy5D2l5LIT1aMqS', '09285548332', NULL, 'customer', 'default.png', 'Unverified', 1, 0, NULL, NULL, NULL, NULL, '2025-05-08 02:42:53', '2025-05-10 15:55:10'),
(10, 'diddi', 'dd_981fcd', 'dd@gmail.com', '$2y$10$6XnW9wdm1Kogr5nXuK4zmeNy8PXew82kMBzcyw.WsnsTH67G6CHju', NULL, NULL, 'customer', 'default.png', 'Unverified', 1, 0, NULL, NULL, NULL, NULL, '2025-05-08 03:01:16', '2025-05-08 03:01:16'),
(11, 'waddw', 'dawd_a6b95d', 'dawd@gmail.com', '$2y$10$yw4pldPJW5YBctVHm7Ke5uYdaD6FQGKScfkUZMoiEVJUkTlDc29Ay', NULL, NULL, 'customer', 'default.png', 'Unverified', 1, 0, NULL, NULL, NULL, NULL, '2025-05-08 15:31:19', '2025-05-08 15:31:19'),
(12, 'workpls', 'works_8ac141', 'works@gmail.com', '$2y$10$2MIkpZtp0RwSlvzkNuFwyuSb701tNtB.VddXIYcZ2NuYYxkXEY04O', NULL, NULL, 'customer', 'default.png', 'Unverified', 1, 0, NULL, NULL, NULL, NULL, '2025-05-08 20:06:47', '2025-05-08 20:06:47');

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `priority_level` enum('Low','Medium','High') DEFAULT 'Medium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product` (`user_id`,`product_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `delivery_tracking`
--
ALTER TABLE `delivery_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `event_packages`
--
ALTER TABLE `event_packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_payments`
--
ALTER TABLE `event_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `guest_carts`
--
ALTER TABLE `guest_carts`
  ADD PRIMARY KEY (`token`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `inventory_log`
--
ALTER TABLE `inventory_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tracking_code` (`tracking_code`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `order_ratings`
--
ALTER TABLE `order_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`);

--
-- Indexes for table `order_timeline`
--
ALTER TABLE `order_timeline`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_status` (`is_deleted`,`is_active`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `delivery_tracking`
--
ALTER TABLE `delivery_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `event_packages`
--
ALTER TABLE `event_packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_payments`
--
ALTER TABLE `event_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_log`
--
ALTER TABLE `inventory_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `order_ratings`
--
ALTER TABLE `order_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_timeline`
--
ALTER TABLE `order_timeline`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_tracking`
--
ALTER TABLE `delivery_tracking`
  ADD CONSTRAINT `delivery_tracking_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_payments`
--
ALTER TABLE `event_payments`
  ADD CONSTRAINT `event_payments_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `inventory_log`
--
ALTER TABLE `inventory_log`
  ADD CONSTRAINT `inventory_log_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `inventory_log_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_ratings`
--
ALTER TABLE `order_ratings`
  ADD CONSTRAINT `order_ratings_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_timeline`
--
ALTER TABLE `order_timeline`
  ADD CONSTRAINT `order_timeline_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
