<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
startSession();

// If user is already logged in, redirect to customer menu page
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/customerPage/menu/menu.php');
    exit;
}

// Initialize error variables
$signin_error = '';
$signup_error = '';
$signin_success = '';
$signup_success = '';

// Check for redirect parameter
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
    $form_type = filter_input(INPUT_POST, 'form_type', FILTER_SANITIZE_STRING);

    if (!$csrf_token || $csrf_token !== $_SESSION[CSRF_TOKEN_KEY]) {
        $signin_error = $signup_error = 'Security verification failed. Please try again.';
    } else {
        if ($form_type === 'signin') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';

            // Special case for admin login - allow "admin123" without @ symbol
            if ($email === 'admin123' && $password === 'hanzamadmin') {
                // Hardcoded admin credentials - set proper email format for the API
                $_SESSION['temp_login_email'] = 'admin123';
                $_SESSION['temp_login_password'] = 'hanzamadmin';

                // Redirect to login API
                header('Location: ' . BASE_URL . '/api/auth/loginApi.php');
                exit;
            }

            if (empty($email)) {
                $signin_error = 'Please enter your email address.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $signin_error = 'Please enter a valid email address.';
            } elseif (empty($password)) {
                $signin_error = 'Please enter your password.';
            } else {
                // Store credentials in session for API processing
                $_SESSION['temp_login_email'] = $email;
                $_SESSION['temp_login_password'] = $password;

                // Only set redirect for customer pages, admin redirection is handled in loginApi.php
                if ($redirect === 'cart') {
                    $_SESSION['redirect_after_login'] = '/customerPage/checkout/checkout.php';
                } else {
                    $_SESSION['redirect_after_login'] = '/customerPage/menu/menu.php';
                }

                // Redirect to login API
                header('Location: ' . BASE_URL . '/api/auth/loginApi.php');
                exit;
            }
        } elseif ($form_type === 'signup') {
            $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';

            if (empty($full_name)) {
                $signup_error = 'Please enter your full name.';
            } elseif (strlen($full_name) < 2) {
                $signup_error = 'Your name must be at least 2 characters long.';
            } elseif (empty($email)) {
                $signup_error = 'Please enter your email address.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $signup_error = 'Please enter a valid email address.';
            } elseif (empty($password)) {
                $signup_error = 'Please enter a password.';
            } elseif (strlen($password) < 8) {
                $signup_error = 'Password must be at least 8 characters long.';
            } else {
                // Store registration data in session for API processing
                $_SESSION['temp_register_name'] = $full_name;
                $_SESSION['temp_register_email'] = $email;
                $_SESSION['temp_register_password'] = $password;
                $_SESSION['redirect_after_login'] = $redirect === 'cart' ? '/customerPage/checkout/checkout.php' : '/customerPage/menu/menu.php';

                // Redirect to register API
                header('Location: ' . BASE_URL . '/api/auth/registerApi.php');
                exit;
            }
        }
    }
}

// Check for error/success messages from redirect
if (isset($_GET['error'])) {
    $error_message = filter_input(INPUT_GET, 'error', FILTER_SANITIZE_STRING);
    if (isset($_GET['form']) && $_GET['form'] === 'signup') {
        $signup_error = urldecode($error_message);
    } else {
        $signin_error = urldecode($error_message);
    }
}

if (isset($_GET['success'])) {
    $success_message = filter_input(INPUT_GET, 'success', FILTER_SANITIZE_STRING);
    if (isset($_GET['form']) && $_GET['form'] === 'signup') {
        $signup_success = urldecode($success_message);
    } else {
        $signin_success = urldecode($success_message);
    }
}

// Generate CSRF token if not exists
if (!isset($_SESSION[CSRF_TOKEN_KEY])) {
    $_SESSION[CSRF_TOKEN_KEY] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Ayskrim</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Fredoka:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Existing CSS unchanged */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        :root {
            --primary: #ec4899;
            --primary-light: #f472b6;
            --primary-dark: #db2777;
            --secondary: #8b5cf6;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --white: #ffffff;
            --error: #ef4444;
            --success: #22c55e;
            --input-border: rgba(209, 213, 219, 0.8);
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.2);
            --glass-shadow: rgba(0, 0, 0, 0.07);
            --card-shadow: rgba(236, 72, 153, 0.15);
        }
        body {
            background: radial-gradient(circle at 50% 50%, #ffcfdf 0%, #fce0fe 50%, #eeddff 100%);
            min-height: 100vh;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            color: var(--text-dark);
            line-height: 1.5;
        }
        .auth-main {
            width: 100%;
            max-width: 1200px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            position: relative;
            padding: 1.5rem;
        }
        .welcome-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow:
                0 5px 15px var(--glass-shadow),
                0 15px 35px var(--card-shadow),
                inset 0 1px 1px rgba(255, 255, 255, 0.5);
            padding: 3rem;
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: all 0.5s ease;
        }
        .welcome-header {
            margin-bottom: 2.5rem;
            position: relative;
            padding-bottom: 1.5rem;
        }
        .welcome-header::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 10px;
            background: url("data:image/svg+xml,%3Csvg viewBox='0 0 1200 120' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 0v46.29c47.79 22.2 103.59 32.17 158 28 70.36-5.37 136.33-33.31 206.8-37.5 73.84-4.36 147.54 16.88 218.2 35.26 69.27 18.17 138.3 24.88 209.4 13.08 36.15-6 69.85-17.84 104.45-29.34C989.49 25 1113-14.29 1200 52.47V0z' fill='%23ffb8df' opacity='.2'/%3E%3C/svg%3E") no-repeat;
            background-size: cover;
        }
        .welcome-title {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-size: 2.5rem;
            margin: 0 0 0.5rem;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .welcome-subtitle {
            color: var(--text-light);
            font-size: 1.1rem;
            font-weight: 400;
        }
        .auth-tabs {
            display: flex;
            position: relative;
            margin-bottom: 40px;
            border-radius: 12px;
            background: rgba(236, 72, 153, 0.1);
            padding: 5px;
            max-width: 300px;
            margin-left: auto;
            margin-right: auto;
            z-index: 1;
        }
        .auth-tab {
            flex: 1;
            padding: 12px 0;
            text-align: center;
            color: var(--text-light);
            cursor: pointer;
            font-weight: 500;
            transition: color 0.3s;
            position: relative;
            z-index: 2;
            border: none;
            background: transparent;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
        }
        .auth-tab.active {
            color: var(--white);
        }
        .tab-indicator {
            position: absolute;
            height: calc(100% - 10px);
            width: calc(50% - 10px);
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            border-radius: 8px;
            top: 5px;
            left: 5px;
            transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            z-index: 1;
        }
        .auth-tabs[data-active-tab="signup"] .tab-indicator {
            transform: translateX(100%);
        }
        .auth-forms-container {
            position: relative;
            overflow: visible;
            height: 430px;
            margin-bottom: 20px;
        }
        .auth-form {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            transition: transform 0.5s ease, opacity 0.3s ease;
            opacity: 0;
            transform: translateX(-20px);
            pointer-events: none;
            display: block;
            z-index: 0;
        }
        .auth-form.active {
            opacity: 1;
            transform: translateX(0);
            pointer-events: all;
            z-index: 1;
        }
        .auth-form-signin {
            transform: translateX(-20px);
        }
        .auth-form-signup {
            transform: translateX(20px);
        }
        .form-group {
            margin-bottom: 1.2rem;
            text-align: left;
            position: relative;
        }
        .form-group label {
            display: block;
            font-size: 0.9rem;
            color: var(--text-dark);
            margin-bottom: 0.4rem;
            font-weight: 500;
        }
        .form-input {
            width: 100%;
            height: 48px;
            padding: 0 16px;
            border: 1px solid var(--input-border);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
            transition: all 0.3s;
            outline: none;
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            position: relative;
            z-index: 1;
        }
        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.2);
            background: var(--white);
        }
        .input-with-icon {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            z-index: 2;
        }
        .input-with-icon .form-input {
            padding-left: 45px;
        }
        .auth-submit {
            width: 100%;
            height: 50px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            border: none;
            color: var(--white);
            padding: 14px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            box-shadow: 0 4px 10px rgba(236, 72, 153, 0.3);
            position: relative;
            overflow: hidden;
        }
        .auth-submit:hover {
            background: linear-gradient(90deg, var(--primary-dark), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(236, 72, 153, 0.4);
        }
        .auth-submit:active {
            transform: translateY(1px);
        }
        .auth-submit::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }
        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            100% {
                transform: scale(20, 20);
                opacity: 0;
            }
        }
        .auth-submit:focus:not(:active)::after {
            animation: ripple 1s ease-out;
        }
        .auth-options {
            display: flex;
            align-items: center;
            margin-top: 1.5rem;
            justify-content: space-between;
        }
        .auth-remember {
            display: flex;
            align-items: center;
        }
        .auth-remember input {
            margin-right: 0.5rem;
            accent-color: var(--primary);
        }
        .auth-forgot {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.2s;
        }
        .auth-forgot:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        .welcome-links {
            margin-top: 20px;
            text-align: center;
        }
        .welcome-link {
            color: var(--text-light);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.2s;
            border-radius: 8px;
            padding: 10px 16px;
            display: inline-block;
        }
        .welcome-link:hover {
            color: var(--primary);
            background: rgba(236, 72, 153, 0.1);
        }
        .auth-message {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .auth-error {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--error);
        }
        .auth-success {
            background-color: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: var(--success);
        }
        .field-error {
            color: var(--error);
            font-size: 0.8rem;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        @media (max-width: 480px) {
            .welcome-container {
                padding: 2rem;
                max-width: 90%;
            }
            .welcome-title {
                font-size: 2rem;
            }
            .auth-forms-container {
                height: 450px;
            }
        }
        .animated-background {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            overflow: hidden;
            z-index: -1;
        }
        .bg-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(40px);
            opacity: 0.5;
        }
        .shape-1 {
            background: rgba(236, 72, 153, 0.3);
            width: 300px;
            height: 300px;
            top: 20%;
            left: 15%;
            animation: float 20s infinite alternate ease-in-out;
        }
        .shape-2 {
            background: rgba(139, 92, 246, 0.3);
            width: 350px;
            height: 350px;
            bottom: 10%;
            right: 15%;
            animation: float 25s infinite alternate-reverse ease-in-out;
        }
        .shape-3 {
            background: rgba(255, 114, 182, 0.3);
            width: 250px;
            height: 250px;
            top: 50%;
            right: 30%;
            animation: float 18s infinite alternate ease-in-out 5s;
        }
        @keyframes float {
            0% {
                transform: translate(0, 0) rotate(0deg) scale(1);
            }
            33% {
                transform: translate(30px, -50px) rotate(5deg) scale(1.1);
            }
            66% {
                transform: translate(-20px, 20px) rotate(-3deg) scale(0.9);
            }
            100% {
                transform: translate(0, 0) rotate(0deg) scale(1);
            }
        }
    </style>
</head>
<body class="homepage">
    <main class="auth-main">
        <div class="animated-background">
            <div class="bg-shape shape-1"></div>
            <div class="bg-shape shape-2"></div>
            <div class="bg-shape shape-3"></div>
        </div>
        <div class="welcome-container">
            <div class="welcome-header">
                <?php if ($redirect === 'cart'): ?>
                    <h1 class="welcome-title">Sign In to Checkout</h1>
                    <p class="welcome-subtitle">Please sign in to proceed with your order.</p>
                <?php else: ?>
                    <h1 class="welcome-title">Welcome to Ayskrim</h1>
                    <p class="welcome-subtitle">The premium ice cream experience</p>
                <?php endif; ?>
            </div>
            <div class="auth-tabs" data-active-tab="signin">
                <div class="tab-indicator"></div>
                <button type="button" class="auth-tab active" data-tab="signin">Sign In</button>
                <button type="button" class="auth-tab" data-tab="signup">Sign Up</button>
            </div>
            <div class="auth-forms-container">
                <form class="auth-form auth-form-signin active" id="signinForm" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION[CSRF_TOKEN_KEY] ?? ''); ?>">
                    <input type="hidden" name="form_type" value="signin">
                    <input type="hidden" name="referer" value="<?php echo htmlspecialchars($_SERVER['HTTP_REFERER'] ?? ''); ?>">
                    <?php if (isset($_GET['redirect'])): ?>
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect']); ?>">
                    <?php endif; ?>
                    <?php if (!empty($signin_error)): ?>
                        <div class="auth-message auth-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($signin_error); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($signin_success)): ?>
                        <div class="auth-message auth-success">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($signin_success); ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="signin-email">Email</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" id="signin-email" name="email" class="form-input" placeholder="your@email.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="signin-password">Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="signin-password" name="password" class="form-input" placeholder="•••••••••" required>
                        </div>
                    </div>
                    <div class="auth-options">
                        <label class="auth-remember">
                            <input type="checkbox" name="remember"> Remember me
                        </label>
                        <a href="#" class="auth-forgot">Forgot password?</a>
                    </div>
                    <button type="submit" class="auth-submit">Sign In</button>
                </form>
                <form class="auth-form auth-form-signup" id="signupForm" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION[CSRF_TOKEN_KEY] ?? ''); ?>">
                    <input type="hidden" name="form_type" value="signup">
                    <input type="hidden" name="referer" value="<?php echo htmlspecialchars($_SERVER['HTTP_REFERER'] ?? ''); ?>">
                    <?php if (isset($_GET['redirect'])): ?>
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect']); ?>">
                    <?php endif; ?>
                    <?php if (!empty($signup_error)): ?>
                        <div class="auth-message auth-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($signup_error); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($signup_success)): ?>
                        <div class="auth-message auth-success">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($signup_success); ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="signup-name">Full Name</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="signup-name" name="full_name" class="form-input" placeholder="John Doe" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="signup-email">Email</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" id="signup-email" name="email" class="form-input" placeholder="your@email.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="signup-password">Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="signup-password" name="password" class="form-input" placeholder="•••••••••" required>
                        </div>
                    </div>
                    <button type="submit" class="auth-submit">Create Account</button>
                </form>
            </div>
            <div class="welcome-links">
                <a href="/ayskrimWebsite/landingPage/home/home.php" class="welcome-link">
                    <i class="fas fa-home"></i> Continue as Guest
                </a>
            </div>
        </div>
    </main>
    <script src="/ayskrimWebsite/shared/scripts/cart-transfer.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure all form elements are visible
            document.querySelectorAll('.form-input').forEach(input => {
                input.style.display = 'block';
                input.style.opacity = '1';
                input.style.visibility = 'visible';
            });

            // Tab switching functionality
            const tabs = document.querySelectorAll('.auth-tab');
            const forms = document.querySelectorAll('.auth-form');
            const tabsContainer = document.querySelector('.auth-tabs');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const targetTab = tab.getAttribute('data-tab');
                    tabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    tabsContainer.setAttribute('data-active-tab', targetTab);
                    forms.forEach(form => {
                        form.classList.remove('active');
                        if (form.id === `${targetTab}Form`) {
                            form.classList.add('active');
                        }
                    });
                });
            });

            // Add ripple effect to buttons
            const buttons = document.querySelectorAll('.auth-submit');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const rect = button.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    const ripple = document.createElement('span');
                    ripple.style.position = 'absolute';
                    ripple.style.top = y + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.width = '5px';
                    ripple.style.height = '5px';
                    ripple.style.background = 'rgba(255, 255, 255, 0.5)';
                    ripple.style.borderRadius = '100%';
                    ripple.style.transform = 'scale(0)';
                    ripple.style.animation = 'ripple 0.6s linear';
                    ripple.style.opacity = '0.5';
                    button.appendChild(ripple);
                    setTimeout(() => ripple.remove(), 600);
                });
            });

            // Client-side validation for Sign In form
            const signinForm = document.getElementById('signinForm');
            if (signinForm) {
                signinForm.addEventListener('submit', function(e) {
                    const email = document.getElementById('signin-email').value.trim();
                    const password = document.getElementById('signin-password').value;
                    let isValid = true;

                    // Special case for admin login - allow "admin123" without @ symbol
                    if (email === 'admin123' && password === 'hanzamadmin') {
                        // Admin credentials are valid, allow form submission
                        return true;
                    }

                    if (!email) {
                        isValid = false;
                        showFieldError('signin-email', 'Email is required');
                    } else if (!isValidEmail(email)) {
                        isValid = false;
                        showFieldError('signin-email', 'Please enter a valid email');
                    }
                    if (!password) {
                        isValid = false;
                        showFieldError('signin-password', 'Password is required');
                    }
                    if (!isValid) {
                        e.preventDefault();
                    }
                });
            }

            // Client-side validation for Sign Up form
            const signupForm = document.getElementById('signupForm');
            if (signupForm) {
                signupForm.addEventListener('submit', function(e) {
                    const name = document.getElementById('signup-name').value.trim();
                    const email = document.getElementById('signup-email').value.trim();
                    const password = document.getElementById('signup-password').value;
                    let isValid = true;

                    if (!name) {
                        isValid = false;
                        showFieldError('signup-name', 'Name is required');
                    } else if (name.length < 2) {
                        isValid = false;
                        showFieldError('signup-name', 'Name must be at least 2 characters');
                    }
                    if (!email) {
                        isValid = false;
                        showFieldError('signup-email', 'Email is required');
                    } else if (!isValidEmail(email)) {
                        isValid = false;
                        showFieldError('signup-email', 'Please enter a valid email');
                    }
                    if (!password) {
                        isValid = false;
                        showFieldError('signup-password', 'Password is required');
                    } else if (password.length < 8) {
                        isValid = false;
                        showFieldError('signup-password', 'Password must be at least 8 characters');
                    }
                    if (!isValid) {
                        e.preventDefault();
                    }
                });
            }

            // Save guest cart before form submission
            function saveGuestCart() {
                if (sessionStorage.getItem('guestCart')) {
                    localStorage.setItem('pendingGuestCart', sessionStorage.getItem('guestCart'));
                }
            }
            signinForm.addEventListener('submit', saveGuestCart);
            signupForm.addEventListener('submit', saveGuestCart);

            // Field error display
            function showFieldError(fieldId, message) {
                const field = document.getElementById(fieldId);
                if (!field) return;
                field.style.borderColor = '#ef4444';
                const existingError = field.parentNode.parentNode.querySelector('.field-error');
                if (existingError) existingError.remove();
                const errorDiv = document.createElement('div');
                errorDiv.className = 'field-error';
                errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
                field.parentNode.parentNode.appendChild(errorDiv);
                field.addEventListener('input', function() {
                    field.style.borderColor = '';
                    const error = field.parentNode.parentNode.querySelector('.field-error');
                    if (error) error.remove();
                }, { once: true });
            }

            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            // Password visibility toggle
            const passwordInputs = document.querySelectorAll('input[type="password"]');
            passwordInputs.forEach(input => {
                const container = input.parentNode;
                const toggleBtn = document.createElement('button');
                toggleBtn.type = 'button';
                toggleBtn.className = 'password-toggle';
                toggleBtn.innerHTML = '<i class="far fa-eye"></i>';
                toggleBtn.style.position = 'absolute';
                toggleBtn.style.right = '12px';
                toggleBtn.style.top = '50%';
                toggleBtn.style.transform = 'translateY(-50%)';
                toggleBtn.style.background = 'none';
                toggleBtn.style.border = 'none';
                toggleBtn.style.color = '#6b7280';
                toggleBtn.style.cursor = 'pointer';
                toggleBtn.style.zIndex = '2';
                toggleBtn.addEventListener('click', () => {
                    if (input.type === 'password') {
                        input.type = 'text';
                        toggleBtn.innerHTML = '<i class="far fa-eye-slash"></i>';
                    } else {
                        input.type = 'password';
                        toggleBtn.innerHTML = '<i class="far fa-eye"></i>';
                    }
                });
                container.appendChild(toggleBtn);
            });

            // Show signup tab if needed
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('action') === 'register' || <?php echo !empty($signup_error) ? 'true' : 'false'; ?>) {
                document.querySelector('.auth-tab[data-tab="signup"]').click();
            }
        });
    </script>
</body>
</html>