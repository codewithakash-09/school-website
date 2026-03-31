<?php
// Get footer settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$site_phone = $settings['site_phone'] ?? '+91 1234567890';
$site_email = $settings['site_email'] ?? 'info@school.com';
$site_address = $settings['site_address'] ?? 'School Address';
$school_timing = $settings['school_timing'] ?? '9:00 AM - 3:30 PM';
?>
    </main>
    <!-- Main Content End -->
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo escapeOutput($site_address); ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo escapeOutput($site_phone); ?></p>
                    <p><i class="fas fa-envelope"></i> <a href="mailto:<?php echo escapeOutput($site_email); ?>"><?php echo escapeOutput($site_email); ?></a></p>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>/admission.php">Admission Inquiry</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/calendar.php">Academic Calendar</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/results.php">Exam Results</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/careers.php">Careers</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>School Timing</h3>
                    <p><i class="far fa-clock"></i> <?php echo escapeOutput($school_timing); ?></p>
                    <p><i class="fas fa-calendar-week"></i> Monday - Friday</p>
                    <p><i class="fas fa-calendar-day"></i> Saturday: 9:00 AM - 1:00 PM</p>
                </div>
                
                <div class="footer-section">
                    <h3>Follow Us</h3>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo escapeOutput($site_name); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/script.js"></script>
    
    <?php if (isset($page_specific_js)): ?>
        <script><?php echo $page_specific_js; ?></script>
    <?php endif; ?>
</body>
</html>