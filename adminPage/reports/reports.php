<?php
// Analytics reports
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// Ensure user is admin
if (!isLoggedIn() || !isAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    exit('Admin access required.');
}

// Get current admin user
$admin = getCurrentUser();

// Initialize variables
$reportType = isset($_GET['type']) ? $_GET['type'] : 'sales';
$period = isset($_GET['period']) ? $_GET['period'] : 'month';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Connect to database
$pdo = DB::getConnection();

// Get sales data
$salesQuery = "
    SELECT
        DATE(created_at) as date,
        COUNT(*) as order_count,
        SUM(total_amount) as revenue
    FROM orders
    WHERE is_deleted = 0
    AND created_at BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date ASC
";
$stmt = $pdo->prepare($salesQuery);
$stmt->execute([$startDate, $endDate . ' 23:59:59']);
$salesData = $stmt->fetchAll();

// Get product sales data
$productSalesQuery = "
    SELECT
        p.name,
        p.category_id,
        c.name as category_name,
        COUNT(oi.id) as quantity_sold,
        SUM(oi.price * oi.quantity) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.is_deleted = 0
    AND o.created_at BETWEEN ? AND ?
    GROUP BY p.id
    ORDER BY revenue DESC
    LIMIT 10
";
$stmt = $pdo->prepare($productSalesQuery);
$stmt->execute([$startDate, $endDate . ' 23:59:59']);
$productSalesData = $stmt->fetchAll();

// Get category sales data
$categorySalesQuery = "
    SELECT
        c.name as category_name,
        COUNT(oi.id) as quantity_sold,
        SUM(oi.price * oi.quantity) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.is_deleted = 0
    AND o.created_at BETWEEN ? AND ?
    GROUP BY c.id
    ORDER BY revenue DESC
";
$stmt = $pdo->prepare($categorySalesQuery);
$stmt->execute([$startDate, $endDate . ' 23:59:59']);
$categorySalesData = $stmt->fetchAll();

// Get booking data
$bookingQuery = "
    SELECT
        DATE(event_date) as date,
        COUNT(*) as booking_count,
        SUM(total_amount) as revenue
    FROM events
    WHERE is_deleted = 0
    AND event_date BETWEEN ? AND ?
    GROUP BY DATE(event_date)
    ORDER BY date ASC
";
$stmt = $pdo->prepare($bookingQuery);
$stmt->execute([$startDate, $endDate]);
$bookingData = $stmt->fetchAll();

// Get expense data
$expenseQuery = "
    SELECT
        DATE(expense_date) as date,
        SUM(amount) as total_expense
    FROM expenses
    WHERE expense_date BETWEEN ? AND ?
    GROUP BY DATE(expense_date)
    ORDER BY date ASC
";
$stmt = $pdo->prepare($expenseQuery);
$stmt->execute([$startDate, $endDate]);
$expenseData = $stmt->fetchAll();

// Get expense by type data
$expenseCategoryQuery = "
    SELECT
        expense_type as category_name,
        SUM(amount) as total_expense
    FROM expenses
    WHERE expense_date BETWEEN ? AND ?
    GROUP BY expense_type
    ORDER BY total_expense DESC
";
$stmt = $pdo->prepare($expenseCategoryQuery);
$stmt->execute([$startDate, $endDate]);
$expenseCategoryData = $stmt->fetchAll();

// Calculate summary metrics
$totalRevenue = array_sum(array_column($salesData, 'revenue')) + array_sum(array_column($bookingData, 'revenue'));
$totalExpenses = array_sum(array_column($expenseData, 'total_expense'));
$totalProfit = $totalRevenue - $totalExpenses;
$totalOrders = array_sum(array_column($salesData, 'order_count'));
$totalBookings = array_sum(array_column($bookingData, 'booking_count'));

// Prepare data for charts
$salesChartLabels = [];
$salesChartData = [];
$expenseChartData = [];

// Create date range array
$dateRange = [];
$currentDate = new DateTime($startDate);
$endDateObj = new DateTime($endDate);

while ($currentDate <= $endDateObj) {
    $dateStr = $currentDate->format('Y-m-d');
    $dateRange[] = $dateStr;
    $salesChartLabels[] = $currentDate->format('M d');
    $currentDate->modify('+1 day');
}

// Fill in sales data for each date
foreach ($dateRange as $date) {
    $found = false;
    foreach ($salesData as $sale) {
        if ($sale['date'] === $date) {
            $salesChartData[] = floatval($sale['revenue']);
            $found = true;
            break;
        }
    }
    if (!$found) {
        $salesChartData[] = 0;
    }

    // Fill in expense data for each date
    $found = false;
    foreach ($expenseData as $expense) {
        if ($expense['date'] === $date) {
            $expenseChartData[] = floatval($expense['total_expense']);
            $found = true;
            break;
        }
    }
    if (!$found) {
        $expenseChartData[] = 0;
    }
}

// Prepare category data for charts
$categoryLabels = array_column($categorySalesData, 'category_name');
$categoryData = array_column($categorySalesData, 'revenue');

// Prepare expense category data for charts
$expenseCategoryLabels = array_column($expenseCategoryData, 'category_name');
$expenseCategoryValues = array_column($expenseCategoryData, 'total_expense');

// Page-specific scripts
$pageScripts = [
    'https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/flatpickr.min.js',
    'https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js',
    '/ayskrimWebsite/adminPage/reports/reports.js'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics | Ayskrim Admin</title>
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
                <!-- Report filters -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Report Filters</h3>
                        <div class="card-actions">
                            <button class="btn btn-primary export-report-btn">
                                <i class="fas fa-download"></i> Export Report
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="" method="GET" class="filter-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="report-type">Report Type</label>
                                    <select name="type" id="report-type" class="form-select">
                                        <option value="sales" <?php echo $reportType === 'sales' ? 'selected' : ''; ?>>Sales Report</option>
                                        <option value="products" <?php echo $reportType === 'products' ? 'selected' : ''; ?>>Product Performance</option>
                                        <option value="bookings" <?php echo $reportType === 'bookings' ? 'selected' : ''; ?>>Booking Analytics</option>
                                        <option value="expenses" <?php echo $reportType === 'expenses' ? 'selected' : ''; ?>>Expense Report</option>
                                        <option value="profit" <?php echo $reportType === 'profit' ? 'selected' : ''; ?>>Profit & Loss</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="period">Time Period</label>
                                    <select name="period" id="period" class="form-select">
                                        <option value="today" <?php echo $period === 'today' ? 'selected' : ''; ?>>Today</option>
                                        <option value="week" <?php echo $period === 'week' ? 'selected' : ''; ?>>This Week</option>
                                        <option value="month" <?php echo $period === 'month' ? 'selected' : ''; ?>>This Month</option>
                                        <option value="quarter" <?php echo $period === 'quarter' ? 'selected' : ''; ?>>This Quarter</option>
                                        <option value="year" <?php echo $period === 'year' ? 'selected' : ''; ?>>This Year</option>
                                        <option value="custom" <?php echo $period === 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                                    </select>
                                </div>
                                <div class="form-group date-range <?php echo $period !== 'custom' ? 'hidden' : ''; ?>">
                                    <label for="start-date">Start Date</label>
                                    <input type="text" name="start_date" id="start-date" class="form-control date-picker" value="<?php echo htmlspecialchars($startDate); ?>">
                                </div>
                                <div class="form-group date-range <?php echo $period !== 'custom' ? 'hidden' : ''; ?>">
                                    <label for="end-date">End Date</label>
                                    <input type="text" name="end_date" id="end-date" class="form-control date-picker" value="<?php echo htmlspecialchars($endDate); ?>">
                                </div>
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary">Generate Report</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Summary metrics -->
                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="stat-card-icon primary">
                            <i class="fas fa-peso-sign"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Total Revenue</div>
                            <div class="stat-card-value">₱<?php echo number_format($totalRevenue, 2); ?></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-icon red">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Total Expenses</div>
                            <div class="stat-card-value">₱<?php echo number_format($totalExpenses, 2); ?></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-icon green">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Net Profit</div>
                            <div class="stat-card-value">₱<?php echo number_format($totalProfit, 2); ?></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-icon blue">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Total Orders</div>
                            <div class="stat-card-value"><?php echo number_format($totalOrders); ?></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-icon primary">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Total Bookings</div>
                            <div class="stat-card-value"><?php echo number_format($totalBookings); ?></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-icon yellow">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Profit Margin</div>
                            <div class="stat-card-value"><?php echo $totalRevenue > 0 ? number_format(($totalProfit / $totalRevenue) * 100, 1) : 0; ?>%</div>
                        </div>
                    </div>
                </div>

                <!-- Report content -->
                <div class="report-content">
                    <!-- Sales Report -->
                    <div class="report-section <?php echo $reportType === 'sales' ? 'active' : ''; ?>" id="sales-report">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Sales Overview</h3>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="salesChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h3 class="card-title">Sales by Category</h3>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="categorySalesChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Performance -->
                    <div class="report-section <?php echo $reportType === 'products' ? 'active' : ''; ?>" id="product-report">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Top Selling Products</h3>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="productSalesChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h3 class="card-title">Product Sales Details</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-container">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Category</th>
                                                <th>Quantity Sold</th>
                                                <th>Revenue</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($productSalesData)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center py-4">No product sales data available for the selected period.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($productSalesData as $product): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                                        <td><?php echo number_format($product['quantity_sold']); ?></td>
                                                        <td>₱<?php echo number_format($product['revenue'], 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Analytics -->
                    <div class="report-section <?php echo $reportType === 'bookings' ? 'active' : ''; ?>" id="booking-report">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Booking Trends</h3>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="bookingChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h3 class="card-title">Package Distribution</h3>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="packageChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Expense Report -->
                    <div class="report-section <?php echo $reportType === 'expenses' ? 'active' : ''; ?>" id="expense-report">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Expense Trends</h3>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="expenseChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h3 class="card-title">Expenses by Category</h3>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="expenseCategoryChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profit & Loss -->
                    <div class="report-section <?php echo $reportType === 'profit' ? 'active' : ''; ?>" id="profit-report">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Revenue vs Expenses</h3>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="profitLossChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h3 class="card-title">Profit Margin Trend</h3>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="profitMarginChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Include footer -->
            <?php include_once(__DIR__ . '/../shared/footer.php'); ?>
        </div>
    </div>

    <!-- Chart Data -->
    <script>
        // Sales chart data
        const salesChartLabels = <?php echo json_encode($salesChartLabels); ?>;
        const salesChartData = <?php echo json_encode($salesChartData); ?>;
        const expenseChartData = <?php echo json_encode($expenseChartData); ?>;

        // Category sales data
        const categoryLabels = <?php echo json_encode($categoryLabels); ?>;
        const categoryData = <?php echo json_encode($categoryData); ?>;

        // Expense category data
        const expenseCategoryLabels = <?php echo json_encode($expenseCategoryLabels); ?>;
        const expenseCategoryData = <?php echo json_encode($expenseCategoryValues); ?>;

        // Product sales data
        const productLabels = <?php echo json_encode(array_column($productSalesData, 'name')); ?>;
        const productData = <?php echo json_encode(array_column($productSalesData, 'revenue')); ?>;

        // Booking data
        const bookingLabels = <?php echo json_encode($salesChartLabels); ?>;
        const bookingData = <?php echo json_encode(array_map(function($date) use ($bookingData, $dateRange) {
            foreach ($bookingData as $booking) {
                if ($booking['date'] === $date) {
                    return floatval($booking['revenue']);
                }
            }
            return 0;
        }, $dateRange)); ?>;
    </script>
</body>
</html>
