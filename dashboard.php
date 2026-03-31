<?php
$page_title = "Dashboard";
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$user_name = $_SESSION['user_name'];

// Fetch user-specific data based on role
$announcements = [];
$events = [];
$stats = [];

// Get latest announcements
$stmt = $pdo->prepare("SELECT * FROM announcements WHERE is_published = 1 ORDER BY published_at DESC LIMIT 5");
$stmt->execute();
$announcements = $stmt->fetchAll();

// Get upcoming events
$stmt = $pdo->prepare("SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 5");
$stmt->execute();
$events = $stmt->fetchAll();

// Role-specific statistics
if ($user_role === 'admin') {
    // Count total users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $stats['users'] = $stmt->fetch()['count'];
    
    // Count pending admissions
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM admissions WHERE status = 'pending'");
    $stats['admissions'] = $stmt->fetch()['count'];
    
    // Count unread messages
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
    $stats['messages'] = $stmt->fetch()['count'];
} elseif ($user_role === 'parent') {
    // Get parent's children
    $stmt = $pdo->prepare("SELECT * FROM users WHERE parent_child_id = ? AND role = 'student'");
    $stmt->execute([$user_id]);
    $stats['children'] = $stmt->fetchAll();
}
?>
<?php include 'includes/header.php'; ?>

<div class="container">
    <h1>Welcome, <?php echo escapeOutput($user_name); ?>!</h1>
    <p>Here's what's happening at school.</p>
    
    <!-- Statistics Cards -->
    <?php if ($user_role === 'admin'): ?>
        <div class="dashboard-stats">
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <div class="stat-number"><?php echo $stats['users']; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-user-graduate"></i>
                <div class="stat-number"><?php echo $stats['admissions']; ?></div>
                <div class="stat-label">Pending Admissions</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-envelope"></i>
                <div class="stat-number"><?php echo $stats['messages']; ?></div>
                <div class="stat-label">Unread Messages</div>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($user_role === 'parent' && !empty($stats['children'])): ?>
        <div class="card" style="margin-bottom: 2rem;">
            <h3><i class="fas fa-child"></i> Your Children</h3>
            <table class="data-table">
                <thead>
                    <tr><th>Student Name</th><th>Roll Number</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['children'] as $child): ?>
                        <tr>
                            <td><?php echo escapeOutput($child['full_name']); ?></td>
                            <td><?php echo escapeOutput($child['student_roll_no'] ?? 'Not Assigned'); ?></td>
                            <td><span style="color: var(--secondary-color);">Active</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <!-- Admin Quick Actions -->
    <?php if ($user_role === 'admin'): ?>
        <div class="cards-grid" style="margin-bottom: 2rem;">
            <div class="card">
                <i class="fas fa-bullhorn"></i>
                <h3>Manage Announcements</h3>
                <p>Post or edit school announcements and news.</p>
                <a href="admin/manage-announcements.php" class="btn btn-primary">Go →</a>
            </div>
            <div class="card">
                <i class="fas fa-calendar-alt"></i>
                <h3>Manage Events</h3>
                <p>Add or update school events and holidays.</p>
                <a href="admin/manage-events.php" class="btn btn-primary">Go →</a>
            </div>
            <div class="card">
                <i class="fas fa-users"></i>
                <h3>Manage Users</h3>
                <p>View and manage all registered users.</p>
                <a href="admin/manage-users.php" class="btn btn-primary">Go →</a>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Two Column Layout -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem;">
        <!-- Latest Announcements -->
        <div>
            <h2><i class="fas fa-bullhorn"></i> Latest Announcements</h2>
            <?php if (count($announcements) > 0): ?>
                <?php foreach ($announcements as $announcement): ?>
                    <div class="announcement-item">
                        <div class="announcement-title"><?php echo escapeOutput($announcement['title']); ?></div>
                        <div class="announcement-date">
                            <?php echo date('F j, Y', strtotime($announcement['published_at'])); ?>
                        </div>
                        <div><?php echo nl2br(escapeOutput(substr($announcement['content'], 0, 150))); ?>...</div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No announcements yet.</p>
            <?php endif; ?>
        </div>
        
        <!-- Upcoming Events -->
        <div>
            <h2><i class="fas fa-calendar-alt"></i> Upcoming Events</h2>
            <?php if (count($events) > 0): ?>
                <?php foreach ($events as $event): ?>
                    <div class="announcement-item" style="border-left-color: var(--secondary-color);">
                        <div class="announcement-title"><?php echo escapeOutput($event['title']); ?></div>
                        <div class="announcement-date">
                            <i class="far fa-calendar"></i> <?php echo date('F j, Y', strtotime($event['event_date'])); ?>
                            <?php if ($event['event_time']): ?>
                                | <i class="far fa-clock"></i> <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                            <?php endif; ?>
                        </div>
                        <div><?php echo nl2br(escapeOutput(substr($event['description'] ?? '', 0, 100))); ?>...</div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No upcoming events.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>