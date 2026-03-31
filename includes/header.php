<?php
// Load configuration if not already loaded
require_once dirname(__DIR__) . '/config.php';

// Get site settings from database
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$site_name = $settings['site_name'] ?? SITE_NAME;
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="description" content="Official website of <?php echo escapeOutput($site_name); ?> - Quality education since 1990">
    <meta name="keywords" content="school, education, best school, <?php echo escapeOutput($site_name); ?>">
    <meta name="author" content="<?php echo escapeOutput($site_name); ?>">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph tags for social media -->
    <meta property="og:title" content="<?php echo escapeOutput($site_name); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo SITE_URL; ?>">
    <meta property="og:description" content="Official website of <?php echo escapeOutput($site_name); ?>">
    
    <title><?php echo isset($page_title) ? escapeOutput($page_title) . ' - ' : ''; ?><?php echo escapeOutput($site_name); ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo SITE_URL; ?>/assets/favicon.ico">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    
    <!-- Font Awesome Icons (CDN with fallback) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Skip to content link for accessibility -->
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="<?php echo SITE_URL; ?>/index.php">
                    <i class="fas fa-school"></i>
                    <span><?php echo escapeOutput($site_name); ?></span>
                </a>
            </div>
            
            <button class="nav-toggle" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="nav-menu">
                <ul>
                    <li><a href="<?php echo SITE_URL; ?>/index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/about.php" class="<?php echo $current_page == 'about.php' ? 'active' : ''; ?>"><i class="fas fa-info-circle"></i> About</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/announcements.php" class="<?php echo $current_page == 'announcements.php' ? 'active' : ''; ?>"><i class="fas fa-bullhorn"></i> Announcements</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/calendar.php" class="<?php echo $current_page == 'calendar.php' ? 'active' : ''; ?>"><i class="fas fa-calendar-alt"></i> Calendar</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/contact.php" class="<?php echo $current_page == 'contact.php' ? 'active' : ''; ?>"><i class="fas fa-envelope"></i> Contact</a></li>
                    
                    <?php if (isLoggedIn()): ?>
                        <li><a href="<?php echo SITE_URL; ?>/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo SITE_URL; ?>/login.php" class="btn-login"><i class="fas fa-key"></i> Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="flash-message flash-<?php echo $_SESSION['flash_type'] ?? 'info'; ?>">
            <div class="container">
                <?php 
                    echo escapeOutput($_SESSION['flash_message']);
                    unset($_SESSION['flash_message']);
                    unset($_SESSION['flash_type']);
                ?>
                <button class="flash-close">&times;</button>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Main Content Start -->
    <main id="main-content">