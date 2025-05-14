<?php
// Admin dashboard
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/db.php';

// Ensure user is admin
requireAdmin();

// Get current admin user
$admin = getCurrentUser();

// Get dashboard data
$pdo = DB::getConnection();

// Get total orders
$stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE is_deleted = 0");
$totalOrders = $stmt->fetchColumn();

// Get pending orders
$stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'Pending' AND is_deleted = 0");
$pendingOrders = $stmt->fetchColumn();

// Get total revenue
$stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE is_deleted = 0");
$totalRevenue = $stmt->fetchColumn() ?: 0;

// Get total bookings
$stmt = $pdo->query("SELECT COUNT(*) FROM events WHERE is_deleted = 0");
$totalBookings = $stmt->fetchColumn();

// Get recent orders
$stmt = $pdo->query("
    SELECT o.id, o.user_id, o.total_amount, o.order_status, o.payment_status, o.created_at, u.full_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.is_deleted = 0
    ORDER BY o.created_at DESC
    LIMIT 5
");
$recentOrders = $stmt->fetchAll();

// Get recent bookings
$stmt = $pdo->query("
    SELECT e.id, e.user_id, e.event_date, e.guest_count, e.package_type, e.total_amount, e.status, u.full_name
    FROM events e
    JOIN users u ON e.user_id = u.id
    WHERE e.is_deleted = 0
    ORDER BY e.created_at DESC
    LIMIT 5
");
$recentBookings = $stmt->fetchAll();

// Get low stock products
$stmt = $pdo->query("
    SELECT id, name, stock, price, category_id
    FROM products
    WHERE stock < 10 AND is_deleted = 0 AND is_active = 1
    ORDER BY stock ASC
    LIMIT 5
");
$lowStockProducts = $stmt->fetchAll();

// Page-specific scripts
$pageScripts = ['/ayskrimWebsite/adminPage/dashboard/dashboard.js'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Ayskrim</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/ayskrimWebsite/adminPage/shared/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Include sidebar/navbar -->
        <?php include_once(__DIR__ . '/../shared/navbar.php'); ?>

        <div class="admin-main">
            <!-- Include header -->
            <?php include_once(__DIR__ . '/../shared/header.php'); ?>

            <div class="admin-content">
                <!-- Welcome section -->
                <div class="welcome-section mb-6">
                    <h1 class="text-2xl font-semibold mb-2">Welcome back, <?php echo htmlspecialchars($admin['full_name']); ?>!</h1>
                    <p class="text-gray-500">Here's what's happening with your ice cream business today.</p>
                </div>

                <!-- Stats overview -->
                <div class="stats-grid">
                    <!-- Total Orders -->
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Total Orders</div>
                            <div class="stat-icon blue">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($totalOrders); ?></div>
                        <div class="stat-description">
                            <div class="stat-trend up">
                                <i class="fas fa-arrow-up"></i>
                                <span>12.5%</span>
                            </div>
                            <span>vs. last month</span>
                        </div>
                    </div>

                    <!-- Pending Orders -->
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Pending Orders</div>
                            <div class="stat-icon gray">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($pendingOrders); ?></div>
                        <div class="stat-description">
                            <span>Needs attention</span>
                        </div>
                    </div>

                    <!-- Total Revenue -->
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Total Revenue</div>
                            <div class="stat-icon green">
                                <i class="fas fa-peso-sign"></i>
                            </div>
                        </div>
                        <div class="stat-value">₱<?php echo number_format($totalRevenue, 2); ?></div>
                        <div class="stat-description">
                            <div class="stat-trend up">
                                <i class="fas fa-arrow-up"></i>
                                <span>8.2%</span>
                            </div>
                            <span>vs. last month</span>
                        </div>
                    </div>

                    <!-- Total Bookings -->
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Total Bookings</div>
                            <div class="stat-icon red">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($totalBookings); ?></div>
                        <div class="stat-description">
                            <div class="stat-trend up">
                                <i class="fas fa-arrow-up"></i>
                                <span>5.3%</span>
                            </div>
                            <span>vs. last month</span>
                        </div>
                    </div>
                </div>

                <!-- Charts section -->
                <div class="grid-cols-2 gap-6 mb-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Sales Overview</h3>
                            <div class="card-actions">
                                <select class="form-select">
                                    <option value="year">This Year</option>
                                    <option value="month">This Month</option>
                                    <option value="week">This Week</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="salesChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Revenue by Category</h3>
                            <div class="card-actions">
                                <select class="form-select">
                                    <option value="year">This Year</option>
                                    <option value="month">This Month</option>
                                    <option value="week">This Week</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="categoryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent activity section -->
                <div class="grid-cols-2 gap-6 mb-6">
                    <!-- Recent Orders -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Orders</h3>
                            <a href="<?php echo ADMIN_PAGES['orders']; ?>" class="view-all">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-container">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Customer</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($recentOrders)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4">No recent orders found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($recentOrders as $order): ?>
                                                <tr>
                                                    <td>#<?php echo $order['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                                    <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                                    <td>
                                                        <span class="status-badge <?php echo strtolower($order['order_status']); ?>">
                                                            <?php echo $order['order_status']; ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Bookings -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Bookings</h3>
                            <a href="<?php echo ADMIN_PAGES['bookings']; ?>" class="view-all">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-container">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Customer</th>
                                            <th>Package</th>
                                            <th>Status</th>
                                            <th>Event Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($recentBookings)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4">No recent bookings found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($recentBookings as $booking): ?>
                                                <tr>
                                                    <td>#<?php echo $booking['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($booking['full_name']); ?></td>
                                                    <td><?php echo $booking['package_type']; ?></td>
                                                    <td>
                                                        <span class="status-badge <?php echo strtolower($booking['status']); ?>">
                                                            <?php echo $booking['status']; ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('M d, Y', strtotime($booking['event_date'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inventory alerts -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h3 class="card-title">Low Stock Alerts</h3>
                        <a href="<?php echo ADMIN_PAGES['inventory']; ?>" class="view-all">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-container">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Product ID</th>
                                        <th>Product Name</th>
                                        <th>Current Stock</th>
                                        <th>Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($lowStockProducts)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4">No low stock products found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($lowStockProducts as $product): ?>
                                            <tr>
                                                <td>#<?php echo $product['id']; ?></td>
                                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                <td>
                                                    <span class="<?php echo $product['stock'] < 5 ? 'text-danger' : 'text-warning'; ?>">
                                                        <?php echo $product['stock']; ?> units
                                                    </span>
                                                </td>
                                                <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                                <td>
                                                    <a href="<?php echo ADMIN_PAGES['inventory']; ?>?action=restock&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">
                                                        Restock
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Quick actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <a href="<?php echo ADMIN_PAGES['orders']; ?>?action=new" class="quick-action-card">
                                <div class="quick-action-icon blue">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div class="quick-action-title">New Order</div>
                            </a>

                            <a href="<?php echo ADMIN_PAGES['menu']; ?>?action=new" class="quick-action-card">
                                <div class="quick-action-icon green">
                                    <i class="fas fa-ice-cream"></i>
                                </div>
                                <div class="quick-action-title">Add Product</div>
                            </a>

                            <a href="<?php echo ADMIN_PAGES['inventory']; ?>?action=restock" class="quick-action-card">
                                <div class="quick-action-icon primary">
                                    <i class="fas fa-boxes-stacked"></i>
                                </div>
                                <div class="quick-action-title">Restock Inventory</div>
                            </a>

                            <a href="<?php echo ADMIN_PAGES['reports']; ?>" class="quick-action-card">
                                <div class="quick-action-icon red">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="quick-action-title">View Reports</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Include footer -->
            <?php include_once(__DIR__ . '/../shared/footer.php'); ?>
        </div>
    </div>
</body>
</html>
