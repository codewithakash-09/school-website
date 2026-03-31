<?php
$page_title = "Reset Password";
require_once 'config.php';

$error = '';
$success = '';
$step = isset($_GET['step']) ? $_GET['step'] : 'request';
$token = $_GET['token'] ?? '';

if ($step === 'request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } else {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
            $stmt->execute([$token, $expires, $user['id']]);
            
            // In production, send email here
            // For demo, show the reset link
            $reset_link = SITE_URL . "/reset-password.php?step=reset&token=" . $token;
            $success = "Password reset link generated. <br><a href='$reset_link'>$reset_link</a><br>(In production, this would be emailed to you.)";
        } else {
            // Don't reveal if email exists for security
            $success = 'If an account exists with that email, you will receive a reset link.';
        }
    }
}

if ($step === 'reset' && !empty($token)) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $error = 'Invalid or expired reset token. Please request a new one.';
        $step = 'request';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
            $stmt->execute([$password_hash, $user['id']]);
            
            $_SESSION['flash_message'] = 'Password reset successful. Please login with your new password.';
            $_SESSION['flash_type'] = 'success';
            header('Location: login.php');
            exit();
        }
    }
}

$csrf_token = generateCSRFToken();
?>
<?php include 'includes/header.php'; ?>

<div class="container" style="max-width: 500px; margin: 3rem auto;">
    <div class="card" style="padding: 2rem;">
        <h2 style="text-align: center;">Reset Password</h2>
        
        <?php if ($error): ?>
            <div class="flash-message flash-error"><?php echo escapeOutput($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="flash-message flash-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($step === 'request'): ?>
            <form method="POST" action="?step=request">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your registered email">
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Send Reset Link
                </button>
            </form>
            
            <p style="text-align: center; margin-top: 1rem;">
                <a href="login.php">Back to Login</a>
            </p>
            
        <?php elseif ($step === 'reset' && !$error): ?>
            <form method="POST" action="?step=reset&token=<?php echo urlencode($token); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required data-strength="true">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required data-match="password">
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Reset Password
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>