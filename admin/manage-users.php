<?php
$page_title = "Manage Users";
// require_once path adjusted to reach the config file from the admin subfolder
require_once '../config.php'; 
requireRole('admin'); // Restricts access to administrators only

$error = '';
$success = '';

// Handle Status Toggle (Activate/Deactivate)
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        $error = "Invalid security token.";
    } else {
        $user_id = (int)$_GET['id'];
        $new_status = (int)$_GET['toggle_status'];
        
        // Prevent admin from deactivating themselves
        if ($user_id == $_SESSION['user_id'] && $new_status == 0) {
            $error = "You cannot deactivate your own administrator account.";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
            if ($stmt->execute([$new_status, $user_id])) {
                $success = "User status updated successfully.";
            } else {
                $error = "Failed to update user status.";
            }
        }
    }
}

// Fetch all users
$stmt = $pdo->query("SELECT id, full_name, email, role, is_active, last_login FROM users ORDER BY role ASC, full_name ASC");
$users = $stmt->fetchAll();

$csrf_token = generateCSRFToken(); //
?>

<?php include '../includes/header.php'; ?>

<div class="container">
    <h1><i class="fas fa-users-cog"></i> User Management</h1>
    <p>Review and manage access for all school staff, parents, and students.</p>

    <?php if ($success): ?>
        <div class="flash-message flash-success"><?php echo escapeOutput($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="flash-message flash-error"><?php echo escapeOutput($error); ?></div>
    <?php endif; ?>

    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Last Login</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><strong><?php echo escapeOutput($user['full_name']); ?></strong></td>
                        <td><?php echo escapeOutput($user['email']); ?></td>
                        <td>
                            <span class="announcement-category category-<?php echo ($user['role'] === 'admin') ? 'event' : 'general'; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo $user['last_login'] ? date('M j, Y H:i', strtotime($user['last_login'])) : '<span class="gray-text">Never</span>'; ?>
                        </td>
                        <td>
                            <?php if ($user['is_active']): ?>
                                <span style="color: var(--secondary-color);"><i class="fas fa-check-circle"></i> Active</span>
                            <?php else: ?>
                                <span style="color: var(--danger-color);"><i class="fas fa-times-circle"></i> Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <?php if ($user['is_active']): ?>
                                    <a href="?id=<?php echo $user['id']; ?>&toggle_status=0&csrf_token=<?php echo $csrf_token; ?>" 
                                       class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;"
                                       data-confirm="Are you sure you want to deactivate this user?">
                                        Deactivate
                                    </a>
                                <?php else: ?>
                                    <a href="?id=<?php echo $user['id']; ?>&toggle_status=1&csrf_token=<?php echo $csrf_token; ?>" 
                                       class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                        Activate
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <small><em>Current Session</em></small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
