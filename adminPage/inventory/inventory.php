<?php
// Admin inventory management
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

// Handle restock action
if (isset($_GET['action']) && $_GET['action'] === 'restock' && isset($_GET['id'])) {
    $productId = (int)$_GET['id'];

    // Get product details
    $stmt = $pdo->prepare("SELECT name, stock FROM products WHERE id = ? AND is_deleted = 0");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if ($product) {
        // Show restock form
        $showRestockForm = true;
        $restockProduct = $product;
        $restockProductId = $productId;
    } else {
        $message = "Product not found.";
        $messageType = "danger";
    }
}

// Handle restock form submission
if (isset($_POST['action']) && $_POST['action'] === 'restock') {
    $productId = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $notes = $_POST['notes'] ?? '';

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Update product stock
        $stmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        $stmt->execute([$quantity, $productId]);

        // Log inventory change
        $stmt = $pdo->prepare("
            INSERT INTO inventory_log (product_id, quantity_change, action_type, notes, created_by, created_at)
            VALUES (?, ?, 'Restock', ?, ?, NOW())
        ");
        $stmt->execute([$productId, $quantity, $notes, $admin['id']]);

        // Commit transaction
        $pdo->commit();

        $message = "Product stock updated successfully.";
        $messageType = "success";
    } catch (PDOException $e) {
        // Rollback transaction
        $pdo->rollBack();

        $message = "Error updating stock: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Get inventory data with low stock first
$stmt = $pdo->query("
    SELECT p.*, c.name as category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.is_deleted = 0
    ORDER BY
        CASE WHEN p.stock < 10 THEN 0 ELSE 1 END,
        p.stock ASC,
        p.name ASC
");
$inventory = $stmt->fetchAll();

// Get inventory log
$stmt = $pdo->query("
    SELECT l.*, p.name as product_name, u.full_name as admin_name
    FROM inventory_log l
    JOIN products p ON l.product_id = p.id
    JOIN users u ON l.created_by = u.id
    ORDER BY l.created_at DESC
    LIMIT 20
");
$inventoryLog = $stmt->fetchAll();

// Page-specific scripts
$pageScripts = ['/ayskrimWebsite/adminPage/inventory/inventory.js'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management | Ayskrim Admin</title>
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
                        <h1 class="text-2xl font-semibold mb-2">Inventory Management</h1>
                        <p class="text-gray-500">Track and manage your product inventory</p>
                    </div>
                    <div class="page-actions">
                        <button class="btn btn-primary" data-modal-target="batchRestockModal">
                            <i class="fas fa-boxes-stacked btn-icon"></i>
                            Batch Restock
                        </button>
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

                <!-- Restock form if needed -->
                <?php if (isset($showRestockForm) && $showRestockForm): ?>
                    <div class="card mb-6">
                        <div class="card-header">
                            <h3 class="card-title">Restock: <?php echo htmlspecialchars($restockProduct['name']); ?></h3>
                        </div>
                        <div class="card-body">
                            <form action="" method="post">
                                <input type="hidden" name="action" value="restock">
                                <input type="hidden" name="product_id" value="<?php echo $restockProductId; ?>">

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="currentStock" class="form-label">Current Stock</label>
                                        <input type="text" id="currentStock" class="form-input" value="<?php echo $restockProduct['stock']; ?>" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="quantity" class="form-label">Add Quantity</label>
                                        <input type="number" id="quantity" name="quantity" class="form-input" min="1" value="10" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea id="notes" name="notes" class="form-textarea" placeholder="Optional notes about this restock"></textarea>
                                </div>

                                <div class="form-actions">
                                    <a href="inventory.php" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">Update Stock</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Inventory Overview -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h3 class="card-title">Inventory Overview</h3>
                        <div class="card-actions">
                            <div class="search-box">
                                <input type="text" id="searchInventory" class="form-input" placeholder="Search products...">
                                <i class="fas fa-search search-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-container">
                            <table class="admin-table sortable" id="inventoryTable">
                                <thead>
                                    <tr>
                                        <th data-sort="id">ID</th>
                                        <th data-sort="name">Product Name</th>
                                        <th data-sort="category">Category</th>
                                        <th data-sort="stock">Current Stock</th>
                                        <th data-sort="status">Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($inventory)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">No products found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($inventory as $item): ?>
                                            <tr class="<?php echo $item['stock'] < 10 ? 'low-stock' : ''; ?>">
                                                <td data-column="id">#<?php echo $item['id']; ?></td>
                                                <td data-column="name"><?php echo htmlspecialchars($item['name']); ?></td>
                                                <td data-column="category"><?php echo htmlspecialchars($item['category_name']); ?></td>
                                                <td data-column="stock">
                                                    <span class="stock-badge <?php echo getStockClass($item['stock']); ?>">
                                                        <?php echo $item['stock']; ?> units
                                                    </span>
                                                </td>
                                                <td data-column="status">
                                                    <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $item['availability_status'])); ?>">
                                                        <?php echo $item['availability_status']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="actions">
                                                        <a href="?action=restock&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-plus"></i> Restock
                                                        </a>
                                                        <button class="btn btn-sm btn-secondary view-history" data-id="<?php echo $item['id']; ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>">
                                                            <i class="fas fa-history"></i>
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
                </div>

                <!-- Recent Inventory Changes -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Inventory Changes</h3>
                        <a href="#" class="view-all">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-container">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Product</th>
                                        <th>Action</th>
                                        <th>Quantity</th>
                                        <th>Admin</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($inventoryLog)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">No inventory changes found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($inventoryLog as $log): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></td>
                                                <td><?php echo htmlspecialchars($log['product_name']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo getActionClass($log['action_type']); ?>">
                                                        <?php echo $log['action_type']; ?>
                                                    </span>
                                                </td>
                                                <td class="<?php echo $log['quantity_change'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                                    <?php echo $log['quantity_change'] > 0 ? '+' : ''; ?><?php echo $log['quantity_change']; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($log['admin_name']); ?></td>
                                                <td><?php echo htmlspecialchars($log['notes'] ?: 'N/A'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Include footer -->
            <?php include_once(__DIR__ . '/../shared/footer.php'); ?>
        </div>
    </div>

    <!-- Batch Restock Modal -->
    <div class="modal-backdrop" id="batchRestockModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Batch Restock</h3>
                <button class="modal-close" data-modal-close>&times;</button>
            </div>
            <form action="/ayskrimWebsite/api/admin/inventory/batch-restock.php" method="post" class="needs-validation">
                <div class="modal-body">
                    <p class="mb-4">Select multiple products to restock at once.</p>

                    <div class="form-group">
                        <label class="form-label">Select Products</label>
                        <div class="product-selection">
                            <?php foreach ($inventory as $item): ?>
                                <div class="product-checkbox">
                                    <input type="checkbox" id="product_<?php echo $item['id']; ?>" name="products[]" value="<?php echo $item['id']; ?>">
                                    <label for="product_<?php echo $item['id']; ?>">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                        <span class="current-stock">(Current: <?php echo $item['stock']; ?>)</span>
                                    </label>
                                    <input type="number" name="quantity_<?php echo $item['id']; ?>" class="form-input quantity-input" min="1" value="10">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="batchNotes" class="form-label">Notes</label>
                        <textarea id="batchNotes" name="notes" class="form-textarea" placeholder="Optional notes about this batch restock"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">Restock Selected Products</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Product History Modal -->
    <div class="modal-backdrop" id="productHistoryModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Product History: <span id="historyProductName"></span></h3>
                <button class="modal-close" data-modal-close>&times;</button>
            </div>
            <div class="modal-body">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i> Loading history...
                </div>
                <div class="history-content" style="display: none;">
                    <!-- Will be populated dynamically -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close>Close</button>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// Helper functions
function getStockClass($stock) {
    if ($stock <= 0) {
        return 'out-of-stock';
    } elseif ($stock < 5) {
        return 'critical';
    } elseif ($stock < 10) {
        return 'low';
    } else {
        return 'normal';
    }
}

function getActionClass($action) {
    switch ($action) {
        case 'Restock':
            return 'success';
        case 'Sale':
            return 'info';
        case 'Adjustment':
            return 'warning';
        case 'Waste':
            return 'danger';
        default:
            return 'secondary';
    }
}
?>
