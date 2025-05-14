<?php
// API endpoint: login
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/constants.php';

// Create logs directory if it doesn't exist
if (!file_exists(__DIR__ . '/../../logs')) {
    mkdir(__DIR__ . '/../../logs', 0777, true);
}

// Debug logging function
function debug_log($message) {
    file_put_contents(
        __DIR__ . '/../../logs/login_debug.log',
        date('Y-m-d H:i:s') . ' - ' . $message . "\n",
        FILE_APPEND
    );
}

header('Content-Type: application/json');
startSession();
debug_log('Login attempt started. Method: ' . $_SERVER['REQUEST_METHOD']);

// Get credentials either from POST or session
$email = '';
$password = '';
$redirect = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Direct POST request
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $redirect = filter_input(INPUT_POST, 'redirect', FILTER_SANITIZE_STRING);
} else {
    // Session-based request
    $email = $_SESSION['temp_login_email'] ?? '';
    $password = $_SESSION['temp_login_password'] ?? '';
    $redirect = $_SESSION['redirect_after_login'] ?? CUSTOMER_PAGES['menu'];
}

// Clear temporary session data
unset($_SESSION['temp_login_email']);
unset($_SESSION['temp_login_password']);
unset($_SESSION['redirect_after_login']);

// Special case for admin login
if ($email === 'admin123' && $password === 'hanzamadmin') {
    // For admin login, use the stored admin email from the database
    debug_log('Admin login attempt with hardcoded credentials');
    $email = 'admin123';  // This should match the email in the database
}

if (empty($email) || empty($password)) {
    debug_log('Missing credentials');
    header('Location: ' . BASE_URL . '/landingPage/login.php?error=' . urlencode('Please provide both email and password.'));
    exit;
}

try {
    $pdo = DB::getConnection();
    debug_log('Connected to database');

    // Get user by email
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        debug_log('User not found: ' . $email);
        header('Location: ' . BASE_URL . '/landingPage/login.php?error=' . urlencode('Invalid email or password.'));
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        debug_log('Invalid password for user: ' . $email);
        header('Location: ' . BASE_URL . '/landingPage/login.php?error=' . urlencode('Invalid email or password.'));
        exit;
    }

    debug_log('Password verification successful for user ID: ' . $user['id']);
    loginUser((int)$user['id']);
    debug_log('User logged in successfully');

    // Set success message
    $_SESSION['auth_message'] = [
        'type' => 'success',
        'text' => 'Welcome back! You have been successfully logged in.'
    ];

    // Redirect based on role and stored redirect
    if ($user['role'] === ROLE_ADMIN) {
        // The ADMIN_PAGES constant already includes '/ayskrimWebsite' prefix
        // So we need to remove it from the BASE_URL to avoid duplication
        $adminUrl = str_replace('/ayskrimWebsite', '', BASE_URL) . ADMIN_PAGES['dashboard'];
        debug_log('Redirecting admin to: ' . ADMIN_PAGES['dashboard']);
        debug_log('Admin redirect URL: ' . $adminUrl);
        header('Location: ' . $adminUrl);
    } else {
        debug_log('Redirecting to: ' . $redirect);
        header('Location: ' . BASE_URL . $redirect);
    }
    exit;
} catch (Exception $e) {
    debug_log('ERROR: ' . $e->getMessage());
    header('Location: ' . BASE_URL . '/landingPage/login.php?error=' . urlencode('System error. Please try again later.'));
    exit;
}
?>