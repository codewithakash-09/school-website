<?php
/**
 * SCHOOL WEBSITE - SECURE CONFIGURATION FILE
 * Place this file ABOVE your web root directory for maximum security
 * Example path: /home/username/config.php (outside public_html)
 */

// =============================================
// ERROR REPORTING (Disable in production)
// =============================================
// Configure PHP to use this file for errors
ini_set('log_errors', 1);
ini_set('error_log', LOG_FILE);
error_reporting(0);
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// =============================================
// SESSION SECURITY
// =============================================
session_name('School_Secure_Session');
session_set_cookie_params([
    'lifetime' => 7200, // 2 hours
    'path' => '/',
    'domain' => '', // Auto-detect
    'secure' => true, // HTTPS only
    'httponly' => true, // No JavaScript access
    'samesite' => 'Strict' // CSRF protection
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session ID periodically to prevent fixation
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// =============================================
// SECURITY HEADERS
// =============================================
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline';");

// =============================================
// DATABASE CONFIGURATION - CHANGE THESE VALUES
// =============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'school_db');
define('DB_USER', 'your_database_username');  // CHANGE THIS
define('DB_PASS', 'your_strong_password');     // CHANGE THIS
define('DB_CHARSET', 'utf8mb4');

// =============================================
// SECURITY KEYS - GENERATE RANDOM STRINGS
// Generate at: https://randomkeygen.com/
// =============================================
define('SECRET_KEY', 'replace_with_32_char_random_string_1a2b3c4d5e6f7g8h9i0j');
define('SECRET_SALT', 'replace_with_32_char_random_string_9z8y7x6w5v4u3t2s1r0q');
define('CSRF_TOKEN_KEY', 'replace_with_another_random_string_abcdef1234567890');

// =============================================
// SITE CONFIGURATION - DYNAMIC DETECTION
// =============================================
// Automatically detects protocol (http/https) and the current domain/host
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
define('SITE_URL', $protocol . "://" . $host);

// SECURITY LIMITS
// =============================================
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900);        // 15 minutes in seconds
define('SESSION_TIMEOUT', 7200);    // 2 hours
define('PASSWORD_MIN_LENGTH', 8);
define('UPLOAD_MAX_SIZE', 5242880); // 5MB

// =============================================
// FILE UPLOAD PATHS
// =============================================
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// ====================
//Define the log file path
define('LOG_FILE', __DIR__ . '/logs/error.log');
//=================================


// =============================================
// DATABASE CONNECTION (PDO with prepared statements)
// =============================================
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5
        ]
    );
} catch(PDOException $e) {
    // Log error without exposing details to users
    error_log("Database Error: " . $e->getMessage());
    die("We're experiencing technical difficulties. Please try again later.");
}

// =============================================
// CSRF TOKEN FUNCTIONS
// =============================================
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}

// =============================================
// SANITIZATION FUNCTIONS
// =============================================
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function escapeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// =============================================
// RATE LIMITING FUNCTION
// =============================================
function checkRateLimit($pdo, $ip, $limit = 10, $timeWindow = 300) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)");
    $stmt->execute([$ip, $timeWindow]);
    $attempts = $stmt->fetchColumn();
    
    if ($attempts >= $limit) {
        return false;
    }
    return true;
}

function recordLoginAttempt($pdo, $ip, $email = null) {
    $stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address, email, attempt_time) VALUES (?, ?, NOW())");
    $stmt->execute([$ip, $email]);
}

// =============================================
// USER AUTHENTICATION CHECK
// =============================================
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['user_role'] !== $role && $_SESSION['user_role'] !== 'admin') {
        header('Location: ' . SITE_URL . '/dashboard.php');
        exit();
    }
}

// Generate CSRF token for forms
$csrf_token = generateCSRFToken();
?>
