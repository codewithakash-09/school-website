<?php
$page_title = "Login";
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip_address = $_SERVER['REMOTE_ADDR'];
        
        // Check rate limiting
        if (!checkRateLimit($pdo, $ip_address, MAX_LOGIN_ATTEMPTS, LOCKOUT_TIME)) {
            $error = 'Too many failed attempts. Please try again after 15 minutes.';
        } else {
            // Fetch user by email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Successful login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_email'] = $user['email'];
                
                // Update last login info
                $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW(), last_ip = ?, login_attempts = 0 WHERE id = ?");
                $updateStmt->execute([$ip_address, $user['id']]);
                
                // Clear login attempts
                $clearStmt = $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
                $clearStmt->execute([$ip_address]);
                
                // Redirect to intended page or dashboard
                $redirect = $_SESSION['redirect_after_login'] ?? 'dashboard.php';
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . $redirect);
                exit();
            } else {
                // Failed login - record attempt
                recordLoginAttempt($pdo, $ip_address, $email);
                $error = 'Invalid email or password.';
            }
        }
    }
}

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>
<?php include 'includes/header.php'; ?>

<div class="container" style="max-width: 500px; margin: 3rem auto;">
    <div class="card" style="padding: 2rem;">
        <h2 style="text-align: center; margin-bottom: 1.5rem;">
            <i class="fas fa-key"></i> Login to Dashboard
        </h2>
        
        <?php if ($error): ?>
            <div class="flash-message flash-error" style="margin-bottom: 1rem;">
                <?php echo escapeOutput($error); ?>
                <button class="flash-close">&times;</button>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="flash-message flash-success" style="margin-bottom: 1rem;">
                <?php echo escapeOutput($success); ?>
                <button class="flash-close">&times;</button>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" data-validate="true">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email">
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
            </div>
            
            <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                <label style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="remember" value="1">
                    <span>Remember me</span>
                </label>
                <a href="reset-password.php">Forgot Password?</a>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        
        <hr style="margin: 1.5rem 0;">
        
        <p style="text-align: center; margin-bottom: 0;">
            Don't have an account? 
            <a href="register.php">Register as Parent</a>
        </p>
        
        <p style="text-align: center; font-size: 0.875rem; color: var(--gray-color); margin-top: 1rem;">
            <strong>Demo Credentials:</strong><br>
            Admin: admin@school.com / Admin@123<br>
            (Change password after first login)
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>