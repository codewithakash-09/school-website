<?php
$page_title = "Register";
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $full_name = sanitizeInput($_POST['full_name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $role = sanitizeInput($_POST['role'] ?? 'parent');
        $student_name = sanitizeInput($_POST['student_name'] ?? '');
        
        // Validation
        if (empty($full_name) || empty($email) || empty($password)) {
            $error = 'Please fill all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
            $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } elseif ($role === 'student' && empty($student_name)) {
            $error = 'Please enter student name.';
        } else {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email already registered. Please login instead.';
            } else {
                // Create new user
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$full_name, $email, $phone, $password_hash, $role])) {
                    $user_id = $pdo->lastInsertId();
                    
                    // If student role, create student record
                    if ($role === 'student') {
                        $stmt = $pdo->prepare("UPDATE users SET student_roll_no = CONCAT('STU', LPAD(id, 6, '0')), parent_child_id = ? WHERE id = ?");
                        $stmt->execute([$user_id, $user_id]);
                    }
                    
                    $_SESSION['flash_message'] = 'Registration successful! Please login.';
                    $_SESSION['flash_type'] = 'success';
                    header('Location: login.php');
                    exit();
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}

$csrf_token = generateCSRFToken();
?>
<?php include 'includes/header.php'; ?>

<div class="container" style="max-width: 600px; margin: 3rem auto;">
    <div class="card" style="padding: 2rem;">
        <h2 style="text-align: center; margin-bottom: 1.5rem;">
            <i class="fas fa-user-plus"></i> Create Account
        </h2>
        
        <?php if ($error): ?>
            <div class="flash-message flash-error" style="margin-bottom: 1rem;">
                <?php echo escapeOutput($error); ?>
                <button class="flash-close">&times;</button>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" data-validate="true">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="form-group">
                <label for="full_name"><i class="fas fa-user"></i> Full Name *</label>
                <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email Address *</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="phone"><i class="fas fa-phone"></i> Mobile Number</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="role"><i class="fas fa-tag"></i> Register as *</label>
                <select id="role" name="role" required>
                    <option value="parent">Parent/Guardian</option>
                    <option value="student">Student</option>
                </select>
            </div>
            
            <div class="form-group" id="student-name-group" style="display: none;">
                <label for="student_name"><i class="fas fa-child"></i> Student Name *</label>
                <input type="text" id="student_name" name="student_name">
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password *</label>
                <input type="password" id="password" name="password" required data-strength="true">
            </div>
            
            <div class="form-group">
                <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" required data-match="password">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="fas fa-check-circle"></i> Register
            </button>
        </form>
        
        <hr style="margin: 1.5rem 0;">
        
        <p style="text-align: center;">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </div>
</div>

<script>
// Show/hide student name field based on role selection
document.getElementById('role').addEventListener('change', function() {
    const studentGroup = document.getElementById('student-name-group');
    if (this.value === 'student') {
        studentGroup.style.display = 'block';
        document.getElementById('student_name').setAttribute('required', 'required');
    } else {
        studentGroup.style.display = 'none';
        document.getElementById('student_name').removeAttribute('required');
    }
});
</script>

<?php include 'includes/footer.php'; ?>