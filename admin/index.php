<?php
$page_title = "Admin Dashboard";
require_once '../config.php';
requireRole('admin');

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$total_users = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM announcements");
$total_announcements = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM events WHERE event_date >= CURDATE()");
$upcoming_events = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
$unread_messages = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM admissions WHERE status = 'pending'");
$pending_admissions = $stmt->fetch()['count'];

// Get recent contact messages
$stmt = $pdo->prepare("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_messages = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<div class="container">
    <h1>Admin Dashboard</h1>
    <p>Welcome back, <?php echo escapeOutput($_SESSION['user_name']); ?>!</p>
    
    <!-- Statistics Cards -->
    <div class="dashboard-stats">
        <div class="stat-card">
            <i class="fas fa-users"></i>
            <div class="stat-number"><?php echo $total_users; ?></div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-bullhorn"></i>
            <div class="stat-number"><?php echo $total_announcements; ?></div>
            <div class="stat-label">Announcements</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-calendar-alt"></i>
            <div class="stat-number"><?php echo $upcoming_events; ?></div>
            <div class="stat-label">Upcoming Events</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-envelope"></i>
            <div class="stat-number"><?php echo $unread_messages; ?></div>
            <div class="stat-label">Unread Messages</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-user-graduate"></i>
            <div class="stat-number"><?php echo $pending_admissions; ?></div>
            <div class="stat-label">Pending Admissions</div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <h2>Quick Actions</h2>
    <div class="cards-grid" style="margin-bottom: 2rem;">
        <div class="card">
            <i class="fas fa-plus-circle"></i>
            <h3>Add Announcement</h3>
            <p>Post a new announcement for parents and students.</p>
            <a href="manage-announcements.php?action=add" class="btn btn-primary">Add →</a>
        </div>
        <div class="card">
            <i class="fas fa-plus-circle"></i>
            <h3>Add Event</h3>
            <p>Schedule a new school event or holiday.</p>
            <a href="manage-events.php?action=add" class="btn btn-primary">Add →</a>
        </div>
        <div class="card">
            <i class="fas fa-users"></i>
            <h3>Manage Users</h3>
            <p>View, edit, or delete user accounts.</p>
            <a href="manage-users.php" class="btn btn-primary">Manage →</a>
        </div>
        <div class="card">
            <i class="fas fa-cog"></i>
            <h3>Site Settings</h3>
            <p>Update school information and settings.</p>
            <a href="settings.php" class="btn btn-primary">Settings →</a>
        </div>
    </div>
    
    <!-- Recent Messages -->
    <h2>Recent Contact Messages</h2>
    <?php if (count($recent_messages) > 0): ?>
        <table class="data-table">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Subject</th><th>Date</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($recent_messages as $msg): ?>
                    <tr>
                        <td><?php echo escapeOutput($msg['name']); ?></td>
                        <td><?php echo escapeOutput($msg['email']); ?></td>
                        <td><?php echo escapeOutput($msg['subject']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($msg['created_at'])); ?></td>
                        <td>
                            <?php if (!$msg['is_read']): ?>
                                <span style="color: var(--danger-color);">Unread</span>
                            <?php else: ?>
                                <span style="color: var(--secondary-color);">Read</span>
                            <?php endif; ?>
                        </td>
                        <td><a href="view-message.php?id=<?php echo $msg['id']; ?>" class="btn btn-primary" style="padding: 0.25rem 0.5rem;">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No messages yet.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>