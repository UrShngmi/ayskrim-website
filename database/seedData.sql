-- USERS
INSERT INTO users (full_name, username, email, password, phone, address, role, profile_picture, verification_status, is_active, is_deleted, last_login, created_at, updated_at)
VALUES
('Admin User', 'admin', 'admin@ayskrim.com', '$2y$10$adminpwseed', '09170000001', '123 Admin St, City', 'admin', 'default.png', 'Verified', 1, 0, NOW(), NOW(), NOW()),
('Jane Customer', 'jane', 'jane@ayskrim.com', '$2y$10$janepwseed', '09170000002', '456 Customer Ave, City', 'customer', 'default.png', 'Verified', 1, 0, NOW(), NOW(), NOW()),
('John Customer', 'john', 'john@ayskrim.com', '$2y$10$johnpwseed', '09170000003', '789 Customer Blvd, City', 'customer', 'default.png', 'Unverified', 1, 0, NULL, NOW(), NOW());

-- CATEGORIES
INSERT INTO categories (id, name, description, image_url, is_active, is_deleted) VALUES
(1, 'Classic', 'Traditional ice cream favorites', '1.jpg', 1, 0),
(2, 'Premium', 'Gourmet and premium flavors', '2.jpg', 1, 0),
(3, 'Vegan', 'Dairy-free and vegan options', '3.jpg', 1, 0),
(4, 'Bestseller', 'Our most popular flavors', NULL, 1, 0),
(5, 'Seasonal', 'Limited time seasonal flavors', NULL, 1, 0),
(6, 'Limited Edition', 'Exclusive limited edition treats', NULL, 1, 0),
(7, 'New Arrivals', 'Freshly launched flavors', NULL, 1, 0);

-- PRODUCTS
INSERT INTO products (name, description, price, category_id, image_url, flavor_profile, ingredients, stock, availability_status, dietary_type, is_deleted, created_at, updated_at)
VALUES
('Strawberry Dream', 'Sweet strawberry ice cream with fresh berries', 4.99, 4, '1.jpeg', 'Strawberry, Creamy', 'Strawberries, Cream, Sugar', 100, 'Available', 'Regular', 0, NOW(), NOW()),
('Forest Mama', 'Blueberry and raspberry blend with cream', 5.49, 5, '2.jpeg', 'Berry, Creamy', 'Blueberries, Raspberries, Cream, Sugar', 80, 'Available', 'Regular', 0, NOW(), NOW()),
('Forest Prince', 'Mint chocolate-infused ice cream', 5.49, 6, '3.jpeg', 'Mint, Chocolate', 'Mint, Chocolate, Cream, Sugar', 60, 'Available', 'Regular', 0, NOW(), NOW()),
('Purple Paradise', 'Creamy vanilla with ube swirl and coconut', 5.99, 7, '4.jpeg', 'Ube, Coconut, Vanilla', 'Ube, Coconut, Vanilla, Cream, Sugar', 50, 'Available', 'Regular', 0, NOW(), NOW()),
('Mango Tango', 'Alphonso mango with a hint of lime', 4.99, 4, '5.jpeg', 'Mango, Citrus', 'Mango, Lime, Cream, Sugar', 90, 'Available', 'Regular', 0, NOW(), NOW()),
('Cherry On Top', 'Cherry ice cream with chocolate chunks', 4.79, 5, '6.jpeg', 'Cherry, Chocolate', 'Cherries, Chocolate, Cream, Sugar', 70, 'Available', 'Regular', 0, NOW(), NOW()),
('Vanilla Sky', 'Smooth bourbon vanilla beans from Madagascar', 4.49, 6, '7.jpeg', 'Vanilla', 'Vanilla Beans, Cream, Sugar', 120, 'Available', 'Regular', 0, NOW(), NOW()),
('Dark Temptation', 'Intense dark chocolate from Belgium', 5.29, 7, '8.jpeg', 'Dark Chocolate', 'Dark Chocolate, Cream, Sugar', 110, 'Available', 'Regular', 0, NOW(), NOW());

-- 
INSERT INTO orders (user_id, total_amount, delivery_type, shipping_address, tracking_code, order_status, payment_status, is_deleted, created_at, updated_at)
VALUES
(2, 270.00, 'Delivery', '456 Customer Ave, City', 'TRACK123', 'Out for Delivery', 'Paid', 0, NOW(), NOW()),
(3, 120.00, 'Pickup', '789 Customer Blvd, City', 'TRACK456', 'Pending', 'Pending', 0, NOW(), NOW());

-- ORDER ITEMS
INSERT INTO order_items (order_id, product_id, quantity, price, subtotal, special_instructions)
VALUES
(1, 1, 1, 120.00, 120.00, 'No nuts'),
(1, 2, 1, 150.00, 150.00, NULL),
(2, 1, 1, 120.00, 120.00, 'Extra cold');

-- PAYMENTS
INSERT INTO payments (order_id, user_id, amount, payment_method, transaction_id, payment_status, payment_details, created_at)
VALUES
(1, 2, 270.00, 'GCash', 'TXN123', 'Success', '{"gcash_ref": "GC123456"}', NOW()),
(2, 3, 120.00, 'Cash on Delivery', 'TXN456', 'Pending', NULL, NOW());

-- REVIEWS
INSERT INTO reviews (user_id, product_id, rating, comment, image_url, is_verified_purchase, created_at)
VALUES
(2, 1, 5, 'Absolutely delicious!', NULL, 1, NOW()),
(3, 2, 4, 'Rich chocolate flavor.', NULL, 0, NOW());

-- WISHLIST
INSERT INTO wishlist (user_id, product_id, priority_level, created_at, updated_at)
VALUES
(2, 3, 'High', NOW(), NOW()),
(3, 2, 'Medium', NOW(), NOW());

-- CART ITEMS
INSERT INTO cart_items (user_id, product_id, quantity, created_at, updated_at)
VALUES
(2, 2, 2, NOW(), NOW()),
(3, 3, 1, NOW(), NOW());

-- DELIVERY TRACKING
INSERT INTO delivery_tracking (order_id, delivery_person, latitude, longitude, estimated_arrival, notes, status, created_at, updated_at)
VALUES
(1, 'Rider Mike', 14.5995, 120.9842, DATE_ADD(NOW(), INTERVAL 30 MINUTE), 'On the way', 'In Transit', NOW(), NOW());

-- EVENTS
INSERT INTO events (user_id, event_date, start_time, end_time, guest_count, venue_address, package_type, total_amount, special_requests, status, is_deleted, created_at, updated_at)
VALUES
(2, DATE_ADD(CURDATE(), INTERVAL 7 DAY), '14:00:00', '17:00:00', 20, '789 Party Hall, City', 'Premium', 5000.00, 'Vegan options needed', 'Confirmed', 0, NOW(), NOW());

-- EVENT PACKAGES
INSERT INTO event_packages (name, description, price, max_guests, included_items, is_active, is_deleted)
VALUES
('Basic', '3 flavors, cones, 1 server', 3000.00, 20, '{"flavors": ["Vanilla", "Chocolate", "Strawberry"]}', 1, 0),
('Premium', '5 flavors, cones/cups, 2 servers, toppings', 5000.00, 40, '{"flavors": ["Vanilla", "Chocolate", "Mango", "Ube", "Matcha"], "toppings": true}', 1, 0);

-- EXPENSES
INSERT INTO expenses (expense_type, vendor_name, amount, payment_method, description, expense_date, receipt_url, created_at, created_by)
VALUES
('Supplies', 'Dairy Supplier', 2000.00, 'Bank Transfer', 'Milk and cream', CURDATE(), NULL, NOW(), 1),
('Utilities', 'Electric Co.', 1500.00, 'Cash', 'Monthly bill', CURDATE(), NULL, NOW(), 1);

-- ADMIN LOGS
INSERT INTO admin_logs (admin_id, action, details, ip_address, created_at)
VALUES
(1, 'Created product', '{"product_id": 1}', '127.0.0.1', NOW()),
(1, 'Updated order status', '{"order_id": 1, "status": "Out for Delivery"}', '127.0.0.1', NOW());

-- INVENTORY LOG
INSERT INTO inventory_log (product_id, quantity_change, action_type, notes, created_by, created_at)
VALUES
(1, 20, 'Restock', 'New batch arrived', 1, NOW()),
(2, -5, 'Sale', 'Sold 5 units', 1, NOW());

-- USER SETTINGS
INSERT INTO user_settings (user_id, setting_key, setting_value, created_at, updated_at)
VALUES
(2, 'theme', 'pastel', NOW(), NOW()),
(3, 'notifications', 'enabled', NOW(), NOW());

-- SUPPORT TICKETS
INSERT INTO support_tickets (user_id, subject, message, status, created_at, updated_at)
VALUES
(2, 'Order Inquiry', 'Where is my order?', 'Open', NOW(), NOW()),
(3, 'Account Issue', 'Cannot login to my account.', 'Closed', NOW(), NOW());
