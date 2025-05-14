<?php
// Activity and transaction logs
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// Ensure user is admin
requireAdmin();

// Get current admin user
$admin = getCurrentUser();

// Initialize variables
$limit = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$logType = isset($_GET['type']) ? $_GET['type'] : 'admin';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Connect to database
$pdo = DB::getConnection();

// Build query based on log type
$whereClause = "";
$params = [];

if ($logType === 'admin') {
    $query = "
        SELECT
            al.id,
            al.admin_id,
            al.action,
            al.details,
            al.ip_address,
            al.created_at,
            u.full_name as admin_name
        FROM admin_logs al
        JOIN users u ON al.admin_id = u.id
        WHERE al.created_at BETWEEN ? AND ?
        ORDER BY al.created_at DESC
        LIMIT $limit OFFSET $offset
    ";
    $countQuery = "
        SELECT COUNT(*)
        FROM admin_logs
        WHERE created_at BETWEEN ? AND ?
    ";
    $params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
} elseif ($logType === 'inventory') {
    $query = "
        SELECT
            il.id,
            il.product_id,
            il.quantity_change,
            il.action_type,
            il.notes,
            il.created_at,
            u.full_name as admin_name,
            p.name as product_name
        FROM inventory_log il
        JOIN users u ON il.created_by = u.id
        JOIN products p ON il.product_id = p.id
        WHERE il.created_at BETWEEN ? AND ?
        ORDER BY il.created_at DESC
        LIMIT $limit OFFSET $offset
    ";
    $countQuery = "
        SELECT COUNT(*)
        FROM inventory_log
        WHERE created_at BETWEEN ? AND ?
    ";
    $params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
} else {
    // System logs (default)
    $query = "
        SELECT
            id,
            log_type,
            message,
            context,
            created_at
        FROM system_logs
        WHERE created_at BETWEEN ? AND ?
        ORDER BY created_at DESC
        LIMIT $limit OFFSET $offset
    ";
    $countQuery = "
        SELECT COUNT(*)
        FROM system_logs
        WHERE created_at BETWEEN ? AND ?
    ";
    $params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
}

// Get logs
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Get total count for pagination
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$totalLogs = $stmt->fetchColumn();
$totalPages = ceil($totalLogs / $limit);

// Page-specific scripts
$pageScripts = [
    'https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/flatpickr.min.js'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs | Ayskrim Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/flatpickr.min.css">
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
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">System Logs</h3>
                        <div class="card-actions">
                            <button class="btn btn-primary export-btn">
                                <i class="fas fa-download"></i> Export Logs
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filter section -->
                        <div class="filter-section">
                            <form action="" method="GET" class="filter-form">
                                <div class="form-row">
                                    <div class="form-group">
                                        <select name="type" class="form-select">
                                            <option value="admin" <?php echo $logType === 'admin' ? 'selected' : ''; ?>>Admin Actions</option>
                                            <option value="inventory" <?php echo $logType === 'inventory' ? 'selected' : ''; ?>>Inventory Changes</option>
                                            <option value="system" <?php echo $logType === 'system' ? 'selected' : ''; ?>>System Logs</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" name="start_date" placeholder="Start Date" class="form-control date-picker" value="<?php echo htmlspecialchars($startDate); ?>">
                                    </div>
                                    <div class="form-group">
                                        <input type="text" name="end_date" placeholder="End Date" class="form-control date-picker" value="<?php echo htmlspecialchars($endDate); ?>">
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                        <a href="<?php echo ADMIN_PAGES['logs']; ?>" class="btn btn-outline">Reset</a>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Logs table -->
                        <div class="table-container">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <?php if ($logType === 'admin'): ?>
                                            <th>ID</th>
                                            <th>Admin</th>
                                            <th>Action</th>
                                            <th>Details</th>
                                            <th>IP Address</th>
                                            <th>Date & Time</th>
                                        <?php elseif ($logType === 'inventory'): ?>
                                            <th>ID</th>
                                            <th>Product</th>
                                            <th>Change</th>
                                            <th>Action</th>
                                            <th>Notes</th>
                                            <th>Admin</th>
                                            <th>Date & Time</th>
                                        <?php else: ?>
                                            <th>ID</th>
                                            <th>Type</th>
                                            <th>Message</th>
                                            <th>Context</th>
                                            <th>Date & Time</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($logs)): ?>
                                        <tr>
                                            <td colspan="<?php echo $logType === 'inventory' ? 7 : ($logType === 'admin' ? 6 : 5); ?>" class="text-center py-4">No logs found for the selected period.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <?php if ($logType === 'admin'): ?>
                                                    <td>#<?php echo $log['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($log['admin_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                                                    <td>
                                                        <?php
                                                            if (!empty($log['details'])) {
                                                                $details = json_decode($log['details'], true);
                                                                if (is_array($details)) {
                                                                    foreach ($details as $key => $value) {
                                                                        echo htmlspecialchars($key) . ': ' . htmlspecialchars($value) . '<br>';
                                                                    }
                                                                } else {
                                                                    echo htmlspecialchars($log['details']);
                                                                }
                                                            } else {
                                                                echo '-';
                                                            }
                                                        ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                                    <td><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></td>
                                                <?php elseif ($logType === 'inventory'): ?>
                                                    <td>#<?php echo $log['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($log['product_name']); ?></td>
                                                    <td>
                                                        <span class="<?php echo $log['quantity_change'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                                            <?php echo $log['quantity_change'] > 0 ? '+' : ''; ?><?php echo $log['quantity_change']; ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($log['action_type']); ?></td>
                                                    <td><?php echo htmlspecialchars($log['notes']); ?></td>
                                                    <td><?php echo htmlspecialchars($log['admin_name']); ?></td>
                                                    <td><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></td>
                                                <?php else: ?>
                                                    <td>#<?php echo $log['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($log['log_type']); ?></td>
                                                    <td><?php echo htmlspecialchars($log['message']); ?></td>
                                                    <td>
                                                        <?php
                                                            if (!empty($log['context'])) {
                                                                $context = json_decode($log['context'], true);
                                                                if (is_array($context)) {
                                                                    foreach ($context as $key => $value) {
                                                                        echo htmlspecialchars($key) . ': ' . htmlspecialchars($value) . '<br>';
                                                                    }
                                                                } else {
                                                                    echo htmlspecialchars($log['context']);
                                                                }
                                                            } else {
                                                                echo '-';
                                                            }
                                                        ?>
                                                    </td>
                                                    <td><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>&type=<?php echo urlencode($logType); ?>&start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?>" class="page-item">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&type=<?php echo urlencode($logType); ?>&start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?>" class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&type=<?php echo urlencode($logType); ?>&start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?>" class="page-item">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Include footer -->
            <?php include_once(__DIR__ . '/../shared/footer.php'); ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize date pickers
            if (typeof flatpickr !== 'undefined') {
                flatpickr('.date-picker', {
                    dateFormat: 'Y-m-d',
                    allowInput: true
                });
            }

            // Export logs
            const exportBtn = document.querySelector('.export-btn');
            if (exportBtn) {
                exportBtn.addEventListener('click', function() {
                    const table = document.querySelector('.admin-table');
                    if (!table) return;

                    // Get headers
                    const headers = Array.from(table.querySelectorAll('thead th'))
                        .map(th => th.textContent.trim());

                    // Get rows
                    const rows = Array.from(table.querySelectorAll('tbody tr'));

                    // Convert rows to CSV data
                    const csvData = rows.map(row => {
                        return Array.from(row.querySelectorAll('td'))
                            .map(cell => {
                                // Get text content, removing any HTML
                                let text = cell.textContent.trim().replace(/\s+/g, ' ');
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
                    link.setAttribute('download', 'logs.csv');
                    link.style.display = 'none';

                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                });
            }
        });
    </script>
</body>
</html>
