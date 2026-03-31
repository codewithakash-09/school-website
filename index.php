<?php
$page_title = "Home";
require_once 'config.php';

// Fetch latest announcements
$stmt = $pdo->prepare("SELECT * FROM announcements WHERE is_published = 1 ORDER BY published_at DESC LIMIT 5");
$stmt->execute();
$announcements = $stmt->fetchAll();

// Fetch upcoming events
$stmt = $pdo->prepare("SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 5");
$stmt->execute();
$upcoming_events = $stmt->fetchAll();

// Get site settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>
<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Welcome to <?php echo escapeOutput($settings['site_name'] ?? SITE_NAME); ?></h1>
        <p>Empowering young minds for a brighter future. Quality education with modern facilities and experienced faculty.</p>
        <a href="/admission.php" class="btn btn-primary" style="margin-top: 1.5rem; display: inline-block;">Apply for Admission</a>
    </div>
</section>

<!-- Quick Links Section -->
<div class="container">
    <div class="cards-grid">
        <div class="card">
            <i class="fas fa-calendar-alt"></i>
            <h3>Academic Calendar</h3>
            <p>View important dates, holidays, and examination schedules.</p>
            <a href="/calendar.php">View Calendar →</a>
        </div>
        <div class="card">
            <i class="fas fa-bullhorn"></i>
            <h3>Latest News</h3>
            <p>Stay updated with school announcements and events.</p>
            <a href="/announcements.php">Read More →</a>
        </div>
        <div class="card">
            <i class="fas fa-envelope"></i>
            <h3>Contact Us</h3>
            <p>Get in touch with us for any queries or feedback.</p>
            <a href="/contact.php">Contact Now →</a>
        </div>
    </div>
</div>

<!-- Announcements Section -->
<section class="announcements-section">
    <div class="container">
        <h2>Latest Announcements</h2>
        
        <?php if (count($announcements) > 0): ?>
            <?php foreach ($announcements as $announcement): ?>
                <div class="announcement-item">
                    <div class="announcement-title"><?php echo escapeOutput($announcement['title']); ?></div>
                    <div class="announcement-date">
                        <i class="far fa-calendar-alt"></i> 
                        <?php echo date('F j, Y', strtotime($announcement['published_at'])); ?>
                        <span class="announcement-category category-<?php echo $announcement['category']; ?>">
                            <?php echo ucfirst($announcement['category']); ?>
                        </span>
                    </div>
                    <div class="announcement-content">
                        <?php echo nl2br(escapeOutput(substr($announcement['content'], 0, 200))); ?>
                        <?php if (strlen($announcement['content']) > 200): ?>...<?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <a href="/announcements.php" class="btn btn-primary">View All Announcements →</a>
        <?php else: ?>
            <p>No announcements at this time. Please check back later.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Upcoming Events -->
<div class="container">
    <h2>Upcoming Events</h2>
    <?php if (count($upcoming_events) > 0): ?>
        <div class="cards-grid">
            <?php foreach ($upcoming_events as $event): ?>
                <div class="card">
                    <i class="fas fa-calendar-day"></i>
                    <h3><?php echo escapeOutput($event['title']); ?></h3>
                    <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($event['event_date'])); ?></p>
                    <?php if ($event['event_time']): ?>
                        <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($event['event_time'])); ?></p>
                    <?php endif; ?>
                    <?php if ($event['location']): ?>
                        <p><strong>Venue:</strong> <?php echo escapeOutput($event['location']); ?></p>
                    <?php endif; ?>
                    <p><?php echo escapeOutput(substr($event['description'], 0, 100)); ?>...</p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No upcoming events scheduled.</p>
    <?php endif; ?>
</div>

<!-- About School Preview -->
<section style="background: var(--light-color); padding: 3rem 0; margin-top: 2rem;">
    <div class="container">
        <div class="cards-grid">
            <div>
                <h2>About Our School</h2>
                <p><?php echo nl2br(escapeOutput($settings['about_school'] ?? 'Welcome to our school. We are committed to providing quality education and nurturing young minds.')); ?></p>
                <a href="/about.php" class="btn btn-primary" style="margin-top: 1rem;">Read More →</a>
            </div>
            <div>
                <h2>Why Choose Us?</h2>
                <ul style="list-style: none; padding: 0;">
                    <li><i class="fas fa-check-circle" style="color: var(--secondary-color);"></i> Experienced & Dedicated Faculty</li>
                    <li><i class="fas fa-check-circle" style="color: var(--secondary-color);"></i> Modern Infrastructure & Labs</li>
                    <li><i class="fas fa-check-circle" style="color: var(--secondary-color);"></i> Sports & Extracurricular Activities</li>
                    <li><i class="fas fa-check-circle" style="color: var(--secondary-color);"></i> Affordable Fee Structure</li>
                    <li><i class="fas fa-check-circle" style="color: var(--secondary-color);"></i> Safe & Secure Campus</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>