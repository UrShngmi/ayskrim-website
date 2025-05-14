<?php
// Admin orders management
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/db.php';

// Ensure user is admin
requireAdmin();

// Get current admin user
$admin = getCurrentUser();

// Get database connection
$pdo = DB::getConnection();

// Handle form submissions
$message = '';
$messageType = '';

// Handle order status update
if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $orderId = (int)$_POST['order_id'];
    $orderStatus = $_POST['order_status'];
    $paymentStatus = $_POST['payment_status'];

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Update order status
        $stmt = $pdo->prepare("
            UPDATE orders
            SET order_status = ?, payment_status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$orderStatus, $paymentStatus, $orderId]);

        // Log status change
        $stmt = $pdo->prepare("
            INSERT INTO order_status_log (order_id, status, payment_status, notes, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$orderId, $orderStatus, $paymentStatus, $_POST['notes'] ?? '', $admin['id']]);

        // Commit transaction
        $pdo->commit();

        $message = "Order status updated successfully.";
        $messageType = "success";
    } catch (PDOException $e) {
        // Rollback transaction
        $pdo->rollBack();

        $message = "Error updating order status: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Get orders with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Apply filters if any
$whereClause = "WHERE o.is_deleted = 0";
$params = [];

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $whereClause .= " AND o.order_status = ?";
    $params[] = $_GET['status'];
}

if (isset($_GET['payment']) && !empty($_GET['payment'])) {
    $whereClause .= " AND o.payment_status = ?";
    $params[] = $_GET['payment'];
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $whereClause .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR o.id LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}

// Get total count for pagination
$countQuery = "SELECT COUNT(*) FROM orders o JOIN users u ON o.user_id = u.id $whereClause";
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$totalOrders = $stmt->fetchColumn();
$totalPages = ceil($totalOrders / $limit);

// Get orders
$query = "
    SELECT o.*, u.full_name, u.email, u.phone
    FROM orders o
    JOIN users u ON o.user_id = u.id
    $whereClause
    ORDER BY o.created_at DESC
    LIMIT $limit OFFSET $offset
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Get order status options
$orderStatuses = ['Pending', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'];
$paymentStatuses = ['Pending', 'Paid', 'Failed', 'Refunded'];

// Page-specific scripts
$pageScripts = ['/ayskrimWebsite/adminPage/orders/orders.js'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management | Ayskrim Admin</title>
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
                <!-- Page header with actions -->
                <div class="page-header mb-6">
                    <div>
                        <h1 class="text-2xl font-semibold mb-2">Order Management</h1>
                        <p class="text-gray-500">View and manage customer orders</p>
                    </div>
                    <div class="page-actions">
                        <a href="<?php echo ADMIN_PAGES['reports']; ?>?report=orders" class="btn btn-secondary">
                            <i class="fas fa-chart-bar btn-icon"></i>
                            Order Reports
                        </a>
                    </div>
                </div>

                <!-- Display messages if any -->
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> mb-6" data-auto-close="5000">
                        <div class="alert-content">
                            <div class="alert-title"><?php echo ucfirst($messageType); ?></div>
                            <p><?php echo $message; ?></p>
                        </div>
                        <button class="alert-close">&times;</button>
                    </div>
                <?php endif; ?>

                <!-- Order Status Summary -->
                <div class="stats-grid mb-6">
                    <?php
                    // Get counts for each status
                    $statusCounts = [];
                    $stmt = $pdo->query("
                        SELECT order_status, COUNT(*) as count
                        FROM orders
                        WHERE is_deleted = 0
                        GROUP BY order_status
                    ");
                    while ($row = $stmt->fetch()) {
                        $statusCounts[$row['order_status']] = $row['count'];
                    }

                    // Define status cards with icons and colors
                    $statusCards = [
                        'Pending' => ['icon' => 'clock', 'color' => 'gray'],
                        'Preparing' => ['icon' => 'blender', 'color' => 'yellow'],
                        'Out for Delivery' => ['icon' => 'truck', 'color' => 'blue'],
                        'Delivered' => ['icon' => 'check-circle', 'color' => 'green'],
                        'Cancelled' => ['icon' => 'times-circle', 'color' => 'red']
                    ];

                    foreach ($statusCards as $status => $config):
                        $count = $statusCounts[$status] ?? 0;
                    ?>
                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-title"><?php echo $status; ?> Orders</div>
                                <div class="stat-icon <?php echo $config['color']; ?>">
                                    <i class="fas fa-<?php echo $config['icon']; ?>"></i>
                                </div>
                            </div>
                            <div class="stat-value"><?php echo $count; ?></div>
                            <div class="stat-description">
                                <a href="?status=<?php echo urlencode($status); ?>" class="view-link">View orders</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Filters -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h3 class="card-title">Filters</h3>
                    </div>
                    <div class="card-body">
                        <form action="" method="get" class="filters-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="statusFilter" class="form-label">Order Status</label>
                                    <select id="statusFilter" name="status" class="form-select">
                                        <option value="">All Statuses</option>
                                        <?php foreach ($orderStatuses as $status): ?>
                                            <option value="<?php echo $status; ?>" <?php echo (isset($_GET['status']) && $_GET['status'] === $status) ? 'selected' : ''; ?>>
                                                <?php echo $status; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="paymentFilter" class="form-label">Payment Status</label>
                                    <select id="paymentFilter" name="payment" class="form-select">
                                        <option value="">All Payment Statuses</option>
                                        <?php foreach ($paymentStatuses as $status): ?>
                                            <option value="<?php echo $status; ?>" <?php echo (isset($_GET['payment']) && $_GET['payment'] === $status) ? 'selected' : ''; ?>>
                                                <?php echo $status; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="searchOrders" class="form-label">Search</label>
                                    <input type="text" id="searchOrders" name="search" class="form-input" placeholder="Search by customer name, email or order ID" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="form-actions">
                                <a href="orders.php" class="btn btn-secondary">Reset</a>
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Orders</h3>
                        <div class="card-actions">
                            <button class="btn btn-secondary" id="exportOrders">
                                <i class="fas fa-download btn-icon"></i>
                                Export
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-container">
                            <table class="admin-table sortable" id="ordersTable">
                                <thead>
                                    <tr>
                                        <th data-sort="id">Order ID</th>
                                        <th data-sort="date">Date</th>
                                        <th data-sort="customer">Customer</th>
                                        <th data-sort="amount">Amount</th>
                                        <th data-sort="status">Order Status</th>
                                        <th data-sort="payment">Payment Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($orders)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">No orders found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td data-column="id">#<?php echo $order['id']; ?></td>
                                                <td data-column="date"><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                                                <td data-column="customer">
                                                    <div><?php echo htmlspecialchars($order['full_name']); ?></div>
                                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($order['email']); ?></div>
                                                </td>
                                                <td data-column="amount">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                                <td data-column="status">
                                                    <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $order['order_status'])); ?>">
                                                        <?php echo $order['order_status']; ?>
                                                    </span>
                                                </td>
                                                <td data-column="payment">
                                                    <span class="status-badge <?php echo strtolower($order['payment_status']); ?>">
                                                        <?php echo $order['payment_status']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="actions">
                                                        <button class="btn btn-sm btn-primary view-order" data-id="<?php echo $order['id']; ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-secondary update-status" data-id="<?php echo $order['id']; ?>" data-status="<?php echo htmlspecialchars($order['order_status']); ?>" data-payment="<?php echo htmlspecialchars($order['payment_status']); ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="card-footer">
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['payment']) ? '&payment=' . $_GET['payment'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="page-item">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="page-item disabled">
                                        <i class="fas fa-chevron-left"></i>
                                    </span>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <a href="?page=<?php echo $i; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['payment']) ? '&payment=' . $_GET['payment'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <a href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['payment']) ? '&payment=' . $_GET['payment'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="page-item">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="page-item disabled">
                                        <i class="fas fa-chevron-right"></i>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Include footer -->
            <?php include_once(__DIR__ . '/../shared/footer.php'); ?>
        </div>
    </div>

    <!-- View Order Modal -->
    <div class="modal-backdrop" id="viewOrderModal">
        <div class="modal modal-lg">
            <div class="modal-header">
                <h3 class="modal-title">Order Details</h3>
                <button class="modal-close" data-modal-close>&times;</button>
            </div>
            <div class="modal-body">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i> Loading order details...
                </div>
                <div class="order-details" style="display: none;">
                    <!-- Will be populated dynamically -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close>Close</button>
                <button type="button" class="btn btn-primary print-order">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal-backdrop" id="updateStatusModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Update Order Status</h3>
                <button class="modal-close" data-modal-close>&times;</button>
            </div>
            <form id="updateStatusForm" class="needs-validation">
                <input type="hidden" name="order_id" id="updateOrderId">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="orderStatus" class="form-label">Order Status</label>
                        <select id="orderStatus" name="order_status" class="form-select" required>
                            <?php foreach ($orderStatuses as $status): ?>
                                <option value="<?php echo $status; ?>"><?php echo $status; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="paymentStatus" class="form-label">Payment Status</label>
                        <select id="paymentStatus" name="payment_status" class="form-select" required>
                            <?php foreach ($paymentStatuses as $status): ?>
                                <option value="<?php echo $status; ?>"><?php echo $status; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="statusNotes" class="form-label">Notes</label>
                        <textarea id="statusNotes" name="notes" class="form-textarea" placeholder="Optional notes about this status change"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
