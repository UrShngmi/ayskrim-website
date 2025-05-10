<?php
// API endpoint: register
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
function reg_debug_log($message) {
    file_put_contents(
        __DIR__ . '/../../logs/register_debug.log',
        date('Y-m-d H:i:s') . ' - ' . $message . "\n",
        FILE_APPEND
    );
}

header('Content-Type: application/json');
startSession();
reg_debug_log('Registration attempt started. Method: ' . $_SERVER['REQUEST_METHOD']);

// Get registration data either from POST or session
$fullName = '';
$email = '';
$password = '';
$redirect = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Direct POST request
    $fullName = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $redirect = filter_input(INPUT_POST, 'redirect', FILTER_SANITIZE_STRING);
} else {
    // Session-based request
    $fullName = $_SESSION['temp_register_name'] ?? '';
    $email = $_SESSION['temp_register_email'] ?? '';
    $password = $_SESSION['temp_register_password'] ?? '';
    $redirect = $_SESSION['redirect_after_login'] ?? CUSTOMER_PAGES['menu'];
}

// Clear temporary session data
unset($_SESSION['temp_register_name']);
unset($_SESSION['temp_register_email']);
unset($_SESSION['temp_register_password']);
unset($_SESSION['redirect_after_login']);

if (empty($fullName) || empty($email) || empty($password)) {
    reg_debug_log('Missing registration data');
    header('Location: ' . BASE_URL . '/landingPage/login.php?action=register&error=' . urlencode('Please provide all required information.'));
    exit;
}

try {
    $pdo = DB::getConnection();
    reg_debug_log('Connected to database');

    // Check for duplicates
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        reg_debug_log('Email already exists: ' . $email);
        header('Location: ' . BASE_URL . '/landingPage/login.php?action=register&error=' . urlencode('Email already exists.'));
        exit;
    }

    // Generate username
    $username = strtolower(explode('@', $email)[0]) . '_' . substr(md5(time()), 0, 6);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $pdo->prepare('
        INSERT INTO users (full_name, username, email, password, role, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    ');
    $stmt->execute([$fullName, $username, $email, $hashedPassword, 'customer']);
    $userId = (int)$pdo->lastInsertId();
    reg_debug_log('User created with ID: ' . $userId);

    loginUser($userId);
    reg_debug_log('User logged in');

    // Set success message
    $_SESSION['auth_message'] = [
        'type' => 'success',
        'text' => 'Registration successful! Welcome to Ayskrim.'
    ];

    reg_debug_log('Redirecting to: ' . $redirect);
    header('Location: ' . BASE_URL . $redirect);
    exit;
} catch (Exception $e) {
    reg_debug_log('ERROR: ' . $e->getMessage());
    header('Location: ' . BASE_URL . '/landingPage/login.php?action=register&error=' . urlencode('Registration failed. Please try again later.'));
    exit;
}
?>