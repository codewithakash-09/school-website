<?php
$page_title = "About Us";
require_once 'config.php';

// Get site settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>
<?php include 'includes/header.php'; ?>

<div class="container">
    <h1>About Our School</h1>
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <div>
            <h2>Welcome Message</h2>
            <p><?php echo nl2br(escapeOutput($settings['about_school'] ?? 'Welcome to our school. We are committed to providing quality education and nurturing young minds to become future leaders.')); ?></p>
            
            <h2>Our Mission</h2>
            <p>To provide a nurturing environment that fosters academic excellence, character development, and lifelong learning skills in every student.</p>
            
            <h2>Our Vision</h2>
            <p>To be a premier educational institution that empowers students to reach their full potential and contribute positively to society.</p>
            
            <h2>Core Values</h2>
            <ul>
                <li><strong>Excellence:</strong> Striving for the highest standards in everything we do</li>
                <li><strong>Integrity:</strong> Being honest and ethical in all our actions</li>
                <li><strong>Respect:</strong> Valuing every individual and their unique contributions</li>
                <li><strong>Compassion:</strong> Showing kindness and empathy to all</li>
                <li><strong>Innovation:</strong> Embracing creativity and new ideas</li>
            </ul>
        </div>
        
        <div>
            <div class="card">
                <h3><i class="fas fa-chalkboard-user"></i> Quick Facts</h3>
                <ul style="list-style: none; padding: 0;">
                    <li><strong>Established:</strong> 1990</li>
                    <li><strong>Students:</strong> 1000+</li>
                    <li><strong>Faculty:</strong> 50+</li>
                    <li><strong>Classrooms:</strong> 30+</li>
                    <li><strong>Labs:</strong> 4 (Science, Computer, Math, Language)</li>
                    <li><strong>Sports Facilities:</strong> Cricket, Football, Basketball, Badminton</li>
                </ul>
            </div>
            
            <div class="card">
                <h3><i class="fas fa-trophy"></i> Achievements</h3>
                <ul>
                    <li>Best School Award - 2023</li>
                    <li>100% Board Exam Results - 5 Years Running</li>
                    <li>State Level Sports Champions - 2024</li>
                    <li>National Science Olympiad Winners</li>
                </ul>
            </div>
            
            <div class="card">
                <h3><i class="fas fa-handshake"></i> Infrastructure</h3>
                <ul>
                    <li>Smart Classrooms</li>
                    <li>Computer Lab with 50+ Systems</li>
                    <li>Science Laboratories</li>
                    <li>Library with 5000+ Books</li>
                    <li>Sports Ground</li>
                    <li>Transport Facility</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>