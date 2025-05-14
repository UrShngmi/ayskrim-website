<?php
// Admin bookings management
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
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$view = isset($_GET['view']) ? $_GET['view'] : 'list';

// Handle actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($action === 'update_status' && $id > 0 && isset($_GET['status'])) {
        $newStatus = $_GET['status'];
        $validStatuses = ['Pending', 'Confirmed', 'Completed', 'Cancelled'];

        if (in_array($newStatus, $validStatuses)) {
            $pdo = DB::getConnection();
            $stmt = $pdo->prepare("UPDATE events SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$newStatus, $id]);

            // Redirect to remove action from URL
            header("Location: " . ADMIN_PAGES['bookings'] . "?status_updated=1");
            exit;
        }
    }
}

// Build query
$whereClause = "WHERE e.is_deleted = 0";
$params = [];

if (!empty($search)) {
    $whereClause .= " AND (e.id LIKE ? OR u.full_name LIKE ? OR e.venue_address LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($status)) {
    $whereClause .= " AND e.status = ?";
    $params[] = $status;
}

if (!empty($startDate)) {
    $whereClause .= " AND e.event_date >= ?";
    $params[] = $startDate;
}

if (!empty($endDate)) {
    $whereClause .= " AND e.event_date <= ?";
    $params[] = $endDate;
}

// Get total count for pagination
$pdo = DB::getConnection();
$countQuery = "SELECT COUNT(*) FROM events e JOIN users u ON e.user_id = u.id $whereClause";
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$totalBookings = $stmt->fetchColumn();
$totalPages = ceil($totalBookings / $limit);

// Get bookings
$query = "
    SELECT
        e.id,
        e.user_id,
        e.event_date,
        e.start_time,
        e.end_time,
        e.guest_count,
        e.venue_address,
        e.package_type,
        e.total_amount,
        e.special_requests,
        e.status,
        e.created_at,
        e.updated_at,
        u.full_name,
        u.email,
        u.phone,
        ep.payment_method,
        ep.payment_status
    FROM events e
    JOIN users u ON e.user_id = u.id
    LEFT JOIN event_payments ep ON e.id = ep.event_id
    $whereClause
    ORDER BY
        CASE
            WHEN e.status = 'Pending' THEN 1
            WHEN e.status = 'Confirmed' THEN 2
            WHEN e.status = 'Completed' THEN 3
            WHEN e.status = 'Cancelled' THEN 4
        END,
        e.event_date ASC
    LIMIT $limit OFFSET $offset
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// Get booking statistics
$statsQuery = "
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'Confirmed' THEN 1 ELSE 0 END) as confirmed,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled,
        SUM(total_amount) as total_revenue
    FROM events
    WHERE is_deleted = 0
";
$stmt = $pdo->query($statsQuery);
$stats = $stmt->fetch();

// Get upcoming bookings for calendar view
$upcomingQuery = "
    SELECT
        id,
        event_date,
        start_time,
        end_time,
        venue_address,
        package_type,
        status
    FROM events
    WHERE is_deleted = 0 AND status != 'Cancelled' AND event_date >= CURDATE()
    ORDER BY event_date ASC, start_time ASC
    LIMIT 50
";
$stmt = $pdo->query($upcomingQuery);
$upcomingBookings = $stmt->fetchAll();

// Page-specific scripts
$pageScripts = [
    'https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/flatpickr.min.js',
    'https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js',
    '/ayskrimWebsite/adminPage/bookings/bookings.js'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Management | Ayskrim Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
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
                <!-- Status message -->
                <?php if (isset($_GET['status_updated'])): ?>
                    <div class="alert alert-success" data-auto-close="5000">
                        <div class="alert-content">
                            <i class="fas fa-check-circle alert-icon"></i>
                            <div class="alert-message">Booking status updated successfully!</div>
                        </div>
                        <button class="alert-close"><i class="fas fa-times"></i></button>
                    </div>
                <?php endif; ?>

                <!-- Booking statistics -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Total Bookings</div>
                            <div class="stat-icon blue">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['total']); ?></div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Pending</div>
                            <div class="stat-icon gray">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['pending']); ?></div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Confirmed</div>
                            <div class="stat-icon blue">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['confirmed']); ?></div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Completed</div>
                            <div class="stat-icon green">
                                <i class="fas fa-flag-checkered"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['completed']); ?></div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Cancelled</div>
                            <div class="stat-icon red">
                                <i class="fas fa-ban"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['cancelled']); ?></div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Total Revenue</div>
                            <div class="stat-icon green">
                                <i class="fas fa-peso-sign"></i>
                            </div>
                        </div>
                        <div class="stat-value">₱<?php echo number_format($stats['total_revenue'], 2); ?></div>
                    </div>
                </div>

                <!-- Booking management card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Booking Management</h3>
                        <div class="card-actions">
                            <div class="view-options">
                                <button class="view-btn <?php echo $view === 'list' ? 'active' : ''; ?>" data-view="list" title="List View">
                                    <i class="fas fa-list"></i>
                                </button>
                                <button class="view-btn <?php echo $view === 'calendar' ? 'active' : ''; ?>" data-view="calendar" title="Calendar View">
                                    <i class="fas fa-calendar-alt"></i>
                                </button>
                            </div>
                            <button class="btn btn-primary export-btn">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filter section -->
                        <div class="filter-section">
                            <form action="" method="GET" class="filter-form">
                                <div class="form-row">
                                    <div class="form-group">
                                        <input type="text" name="search" placeholder="Search bookings..." class="form-control" value="<?php echo htmlspecialchars($search); ?>">
                                    </div>
                                    <div class="form-group">
                                        <select name="status" class="form-select">
                                            <option value="">All Statuses</option>
                                            <option value="Pending" <?php echo $status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Confirmed" <?php echo $status === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="Completed" <?php echo $status === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="Cancelled" <?php echo $status === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" name="start_date" placeholder="Start Date" class="form-control date-picker" value="<?php echo htmlspecialchars($startDate); ?>">
                                    </div>
                                    <div class="form-group">
                                        <input type="text" name="end_date" placeholder="End Date" class="form-control date-picker" value="<?php echo htmlspecialchars($endDate); ?>">
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                        <a href="<?php echo ADMIN_PAGES['bookings']; ?>" class="btn btn-outline">
                                            <i class="fas fa-redo"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- List view -->
                        <div id="list-view" class="view-container <?php echo $view === 'list' ? 'active' : ''; ?>">
                            <div class="table-container">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Customer</th>
                                            <th>Event Date</th>
                                            <th>Time</th>
                                            <th>Package</th>
                                            <th>Guests</th>
                                            <th>Amount</th>
                                            <th>Payment</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($bookings)): ?>
                                            <tr>
                                                <td colspan="10" class="text-center py-4">No bookings found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($bookings as $booking): ?>
                                                <tr data-booking-id="<?php echo $booking['id']; ?>">
                                                    <td>#<?php echo $booking['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($booking['full_name']); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($booking['event_date'])); ?></td>
                                                    <td><?php echo date('g:i A', strtotime($booking['start_time'])) . ' - ' . date('g:i A', strtotime($booking['end_time'])); ?></td>
                                                    <td><?php echo $booking['package_type']; ?></td>
                                                    <td><?php echo number_format($booking['guest_count']); ?></td>
                                                    <td>₱<?php echo number_format($booking['total_amount'], 2); ?></td>
                                                    <td>
                                                        <span class="badge <?php echo $booking['payment_status'] === 'Success' ? 'success' : ($booking['payment_status'] === 'Pending' ? 'warning' : 'danger'); ?>">
                                                            <?php echo $booking['payment_status'] ?? 'N/A'; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?php
                                                            echo $booking['status'] === 'Completed' ? 'success' :
                                                                ($booking['status'] === 'Confirmed' ? 'primary' :
                                                                ($booking['status'] === 'Pending' ? 'warning' : 'danger'));
                                                        ?>">
                                                            <?php echo $booking['status']; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <button class="btn-icon view-booking" data-id="<?php echo $booking['id']; ?>" title="View Details">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <div class="dropdown">
                                                                <button class="btn-icon dropdown-toggle" title="More Actions">
                                                                    <i class="fas fa-ellipsis-v"></i>
                                                                </button>
                                                                <div class="dropdown-menu">
                                                                    <a href="?action=update_status&id=<?php echo $booking['id']; ?>&status=Pending" class="dropdown-item">
                                                                        <i class="fas fa-clock text-warning"></i> Mark as Pending
                                                                    </a>
                                                                    <a href="?action=update_status&id=<?php echo $booking['id']; ?>&status=Confirmed" class="dropdown-item">
                                                                        <i class="fas fa-check text-primary"></i> Mark as Confirmed
                                                                    </a>
                                                                    <a href="?action=update_status&id=<?php echo $booking['id']; ?>&status=Completed" class="dropdown-item">
                                                                        <i class="fas fa-check-circle text-success"></i> Mark as Completed
                                                                    </a>
                                                                    <a href="?action=update_status&id=<?php echo $booking['id']; ?>&status=Cancelled" class="dropdown-item">
                                                                        <i class="fas fa-ban text-danger"></i> Mark as Cancelled
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
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
                                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?>" class="page-item">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?>" class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>

                                    <?php if ($page < $totalPages): ?>
                                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?>" class="page-item">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Calendar view -->
                        <div id="calendar-view" class="view-container <?php echo $view === 'calendar' ? 'active' : ''; ?>">
                            <div id="booking-calendar"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Include footer -->
            <?php include_once(__DIR__ . '/../shared/footer.php'); ?>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div id="booking-details-modal" class="modal-backdrop">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Booking Details</h3>
                <button class="modal-close" title="Close"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="booking-details-content">
                    <!-- Content will be loaded dynamically -->
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Events Data -->
    <script>
        const calendarEvents = <?php echo json_encode(array_map(function($booking) {
            $startDateTime = $booking['event_date'] . ' ' . $booking['start_time'];
            $endDateTime = $booking['event_date'] . ' ' . $booking['end_time'];

            $statusColors = [
                'Pending' => '#808080',
                'Confirmed' => '#0000FF',
                'Completed' => '#008000',
                'Cancelled' => '#FF0000'
            ];

            return [
                'id' => $booking['id'],
                'title' => 'Booking #' . $booking['id'] . ' - ' . $booking['package_type'],
                'start' => $startDateTime,
                'end' => $endDateTime,
                'backgroundColor' => $statusColors[$booking['status']] ?? '#6b7280',
                'borderColor' => $statusColors[$booking['status']] ?? '#6b7280',
                'extendedProps' => [
                    'status' => $booking['status'],
                    'location' => $booking['venue_address']
                ]
            ];
        }, $upcomingBookings)); ?>;
    </script>
</body>
</html>