<?php
$page_title = "Contact Us";
require_once 'config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $subject = sanitizeInput($_POST['subject'] ?? '');
        $message = sanitizeInput($_POST['message'] ?? '');
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Validation
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $error = 'Please fill all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (!preg_match('/^[6-9]\d{9}$/', $phone) && !empty($phone)) {
            $error = 'Please enter a valid 10-digit mobile number.';
        } else {
            // Save to database
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $phone, $subject, $message, $ip_address, $user_agent])) {
                $success = 'Thank you for contacting us. We will get back to you soon!';
                // Clear form
                $_POST = [];
            } else {
                $error = 'Failed to send message. Please try again.';
            }
        }
    }
}

$csrf_token = generateCSRFToken();
?>
<?php include 'includes/header.php'; ?>

<div class="container">
    <h1>Contact Us</h1>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
        <!-- Contact Form -->
        <div class="card">
            <h2><i class="fas fa-paper-plane"></i> Send us a Message</h2>
            
            <?php if ($success): ?>
                <div class="flash-message flash-success"><?php echo escapeOutput($success); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="flash-message flash-error"><?php echo escapeOutput($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" data-validate="true">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label for="name">Your Name *</label>
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Mobile Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="subject">Subject *</label>
                    <input type="text" id="subject" name="subject" required value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" rows="5" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-envelope"></i> Send Message
                </button>
            </form>
        </div>
        
        <!-- Contact Information -->
        <div>
            <div class="card" style="margin-bottom: 1.5rem;">
                <h2><i class="fas fa-address-card"></i> Get in Touch</h2>
                <?php
                $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
                $settings = [];
                while ($row = $stmt->fetch()) {
                    $settings[$row['setting_key']] = $row['setting_value'];
                }
                ?>
                <p><i class="fas fa-map-marker-alt"></i> <strong>Address:</strong><br>
                <?php echo nl2br(escapeOutput($settings['site_address'] ?? 'School Address Here')); ?></p>
                
                <p><i class="fas fa-phone"></i> <strong>Phone:</strong><br>
                <?php echo escapeOutput($settings['site_phone'] ?? '+91 1234567890'); ?></p>
                
                <p><i class="fas fa-envelope"></i> <strong>Email:</strong><br>
                <a href="mailto:<?php echo escapeOutput($settings['site_email'] ?? 'info@school.com'); ?>">
                    <?php echo escapeOutput($settings['site_email'] ?? 'info@school.com'); ?>
                </a></p>
                
                <p><i class="far fa-clock"></i> <strong>Office Hours:</strong><br>
                Monday - Friday: 8:00 AM - 4:00 PM<br>
                Saturday: 8:00 AM - 1:00 PM</p>
            </div>
            
            <!-- Map (Google Maps iframe - replace with your location) -->
            <div class="card">
                <h3><i class="fas fa-map"></i> Find Us</h3>
                <div style="background: var(--light-color); padding: 1rem; text-align: center; border-radius: var(--radius);">
                    <p><i class="fas fa-map-marker-alt"></i> Map will appear here</p>
                    <small>Replace with Google Maps embed code</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>