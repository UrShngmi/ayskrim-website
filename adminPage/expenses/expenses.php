<?php
// Expenses management
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

// Ensure user is admin
if (!isLoggedIn() || !isAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    exit('Admin access required.');
}

// Get current admin user
$admin = getCurrentUser();

// Get database connection
$pdo = DB::getConnection();

// Handle form submissions
$message = '';
$messageType = '';

// Handle expense deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $expenseId = (int)$_GET['id'];

    try {
        // Hard delete the expense since is_deleted column doesn't exist
        $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ?");
        $stmt->execute([$expenseId]);

        $message = "Expense successfully deleted.";
        $messageType = "success";
    } catch (PDOException $e) {
        $message = "Error deleting expense: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Handle expense form submission
if (isset($_POST['action']) && $_POST['action'] === 'add_expense') {
    $amount = (float)$_POST['amount'];
    $categoryId = (int)$_POST['category_id'];
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'] ?? date('Y-m-d');
    $paymentMethod = $_POST['payment_method'] ?? 'Cash';
    $receiptNumber = $_POST['receipt_number'] ?? '';

    try {
        $stmt = $pdo->prepare("
            INSERT INTO expenses (amount, expense_type, description, expense_date, payment_method, vendor_name, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$amount, $categoryId, $description, $date, $paymentMethod, $receiptNumber, $admin['id']]);

        $message = "Expense successfully added.";
        $messageType = "success";
    } catch (PDOException $e) {
        $message = "Error adding expense: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Define expense types (since expense_categories table doesn't exist)
$categories = [
    ['id' => 'Supplies', 'name' => 'Supplies'],
    ['id' => 'Utilities', 'name' => 'Utilities'],
    ['id' => 'Rent', 'name' => 'Rent'],
    ['id' => 'Salaries', 'name' => 'Salaries'],
    ['id' => 'Marketing', 'name' => 'Marketing'],
    ['id' => 'Equipment', 'name' => 'Equipment'],
    ['id' => 'Maintenance', 'name' => 'Maintenance'],
    ['id' => 'Other', 'name' => 'Other']
];

// Get expenses with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Apply filters if any
$whereClause = "WHERE 1=1";
$params = [];

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $whereClause .= " AND e.expense_type = ?";
    $params[] = $_GET['category'];
}

if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $whereClause .= " AND e.expense_date >= ?";
    $params[] = $_GET['start_date'];
}

if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $whereClause .= " AND e.expense_date <= ?";
    $params[] = $_GET['end_date'];
}

// Get total count for pagination
$countQuery = "SELECT COUNT(*) FROM expenses e $whereClause";
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$totalExpenses = $stmt->fetchColumn();
$totalPages = ceil($totalExpenses / $limit);

// Get expenses
$query = "
    SELECT e.*, u.full_name as admin_name
    FROM expenses e
    JOIN users u ON e.created_by = u.id
    $whereClause
    ORDER BY e.expense_date DESC
    LIMIT $limit OFFSET $offset
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$expenses = $stmt->fetchAll();

// Get expense summary by type
$summaryQuery = "
    SELECT expense_type as category_name, SUM(amount) as total_amount
    FROM expenses
    GROUP BY expense_type
    ORDER BY total_amount DESC
";
$stmt = $pdo->query($summaryQuery);
$expenseSummary = $stmt->fetchAll();

// Get total expenses
$stmt = $pdo->query("SELECT SUM(amount) FROM expenses");
$totalAmount = $stmt->fetchColumn() ?: 0;

// Page-specific scripts
$pageScripts = [
    'https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/flatpickr.min.js',
    '/ayskrimWebsite/adminPage/expenses/expenses.js'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expenses Management | Ayskrim Admin</title>
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
                <!-- Page header with actions -->
                <div class="page-header mb-6">
                    <div>
                        <h1 class="text-2xl font-semibold mb-2">Expenses Management</h1>
                        <p class="text-gray-500">Track and manage your business expenses</p>
                    </div>
                    <div class="page-actions">
                        <button class="btn btn-primary" data-modal-target="addExpenseModal">
                            <i class="fas fa-plus btn-icon"></i>
                            Add New Expense
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

                <!-- Expense Summary -->
                <div class="stats-grid mb-6">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Total Expenses</div>
                            <div class="stat-icon red">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                        <div class="stat-value">₱<?php echo number_format($totalAmount, 2); ?></div>
                        <div class="stat-description">
                            <span>All time</span>
                        </div>
                    </div>

                    <?php foreach(array_slice($expenseSummary, 0, 3) as $summary): ?>
                        <div class="stat-card">
                            <div class="stat-header">
                                <div class="stat-title"><?php echo htmlspecialchars($summary['category_name']); ?></div>
                                <div class="stat-icon blue">
                                    <i class="fas fa-tag"></i>
                                </div>
                            </div>
                            <div class="stat-value">₱<?php echo number_format($summary['total_amount'], 2); ?></div>
                            <div class="stat-description">
                                <span><?php echo round(($summary['total_amount'] / $totalAmount) * 100); ?>% of total</span>
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
                                    <label for="categoryFilter" class="form-label">Category</label>
                                    <select id="categoryFilter" name="category" class="form-select">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $category['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="startDate" class="form-label">Start Date</label>
                                    <input type="text" id="startDate" name="start_date" class="form-input date-picker" value="<?php echo $_GET['start_date'] ?? ''; ?>" placeholder="YYYY-MM-DD">
                                </div>

                                <div class="form-group">
                                    <label for="endDate" class="form-label">End Date</label>
                                    <input type="text" id="endDate" name="end_date" class="form-input date-picker" value="<?php echo $_GET['end_date'] ?? ''; ?>" placeholder="YYYY-MM-DD">
                                </div>
                            </div>

                            <div class="form-actions">
                                <a href="expenses.php" class="btn btn-secondary">Reset</a>
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Expenses Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Expenses</h3>
                        <div class="card-actions">
                            <button class="btn btn-secondary" id="exportExpenses">
                                <i class="fas fa-download btn-icon"></i>
                                Export
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-container">
                            <table class="admin-table sortable" id="expensesTable">
                                <thead>
                                    <tr>
                                        <th data-sort="date">Date</th>
                                        <th data-sort="category">Category</th>
                                        <th data-sort="description">Description</th>
                                        <th data-sort="amount">Amount</th>
                                        <th data-sort="payment">Payment Method</th>
                                        <th data-sort="receipt">Vendor</th>
                                        <th>Added By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($expenses)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">No expenses found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($expenses as $expense): ?>
                                            <tr>
                                                <td data-column="date"><?php echo date('M d, Y', strtotime($expense['expense_date'])); ?></td>
                                                <td data-column="category"><?php echo htmlspecialchars($expense['expense_type']); ?></td>
                                                <td data-column="description"><?php echo htmlspecialchars($expense['description']); ?></td>
                                                <td data-column="amount">₱<?php echo number_format($expense['amount'], 2); ?></td>
                                                <td data-column="payment"><?php echo htmlspecialchars($expense['payment_method']); ?></td>
                                                <td data-column="receipt"><?php echo htmlspecialchars($expense['vendor_name'] ?: 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($expense['admin_name']); ?></td>
                                                <td>
                                                    <div class="actions">
                                                        <button class="btn btn-sm btn-secondary edit-expense" data-id="<?php echo $expense['id']; ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger delete-expense" data-id="<?php echo $expense['id']; ?>" data-description="<?php echo htmlspecialchars($expense['description']); ?>">
                                                            <i class="fas fa-trash"></i>
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
                                    <a href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['start_date']) ? '&start_date=' . $_GET['start_date'] : ''; ?><?php echo isset($_GET['end_date']) ? '&end_date=' . $_GET['end_date'] : ''; ?>" class="page-item">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="page-item disabled">
                                        <i class="fas fa-chevron-left"></i>
                                    </span>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <a href="?page=<?php echo $i; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['start_date']) ? '&start_date=' . $_GET['start_date'] : ''; ?><?php echo isset($_GET['end_date']) ? '&end_date=' . $_GET['end_date'] : ''; ?>" class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <a href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['start_date']) ? '&start_date=' . $_GET['start_date'] : ''; ?><?php echo isset($_GET['end_date']) ? '&end_date=' . $_GET['end_date'] : ''; ?>" class="page-item">
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

    <!-- Add Expense Modal -->
    <div class="modal-backdrop" id="addExpenseModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Add New Expense</h3>
                <button class="modal-close" data-modal-close>&times;</button>
            </div>
            <form action="" method="post" class="needs-validation">
                <input type="hidden" name="action" value="add_expense">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expenseAmount" class="form-label">Amount (₱)</label>
                            <input type="number" id="expenseAmount" name="amount" class="form-input" step="0.01" min="0" required>
                        </div>

                        <div class="form-group">
                            <label for="expenseCategory" class="form-label">Category</label>
                            <select id="expenseCategory" name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="expenseDescription" class="form-label">Description</label>
                        <textarea id="expenseDescription" name="description" class="form-textarea" required></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="expenseDate" class="form-label">Date</label>
                            <input type="text" id="expenseDate" name="date" class="form-input date-picker" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="paymentMethod" class="form-label">Payment Method</label>
                            <select id="paymentMethod" name="payment_method" class="form-select">
                                <option value="Cash">Cash</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="Debit Card">Debit Card</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Check">Check</option>
                                <option value="Digital Wallet">Digital Wallet</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="receiptNumber" class="form-label">Receipt Number (Optional)</label>
                        <input type="text" id="receiptNumber" name="receipt_number" class="form-input">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Expense</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Expense Modal (will be populated dynamically) -->
    <div class="modal-backdrop" id="editExpenseModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Edit Expense</h3>
                <button class="modal-close" data-modal-close>&times;</button>
            </div>
            <form action="/ayskrimWebsite/api/admin/expenses/update.php" method="post" class="needs-validation">
                <input type="hidden" id="editExpenseId" name="id">
                <div class="modal-body">
                    <!-- Form fields will be populated dynamically -->
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin"></i> Loading expense data...
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Expense</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
