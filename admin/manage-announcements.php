<?php
$page_title = "Manage Announcements";
require_once '../config.php';
requireRole('admin');

$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['flash_message'] = 'Announcement deleted successfully.';
        $_SESSION['flash_type'] = 'success';
        header('Location: manage-announcements.php');
        exit();
    }
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'add' || $action === 'edit')) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $title = sanitizeInput($_POST['title'] ?? '');
        $content = sanitizeInput($_POST['content'] ?? '');
        $category = sanitizeInput($_POST['category'] ?? 'general');
        $is_published = isset($_POST['is_published']) ? 1 : 0;
        
        if (empty($title) || empty($content)) {
            $error = 'Please fill all required fields.';
        } else {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO announcements (title, content, category, is_published, posted_by) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$title, $content, $category, $is_published, $_SESSION['user_id']])) {
                    $_SESSION['flash_message'] = 'Announcement added successfully.';
                    $_SESSION['flash_type'] = 'success';
                    header('Location: manage-announcements.php');
                    exit();
                }
            } else {
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("UPDATE announcements SET title = ?, content = ?, category = ?, is_published = ? WHERE id = ?");
                if ($stmt->execute([$title, $content, $category, $is_published, $id])) {
                    $_SESSION['flash_message'] = 'Announcement updated successfully.';
                    $_SESSION['flash_type'] = 'success';
                    header('Location: manage-announcements.php');
                    exit();
                }
            }
        }
    }
}

// Get announcement for editing
$announcement = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    $announcement = $stmt->fetch();
    if (!$announcement) {
        header('Location: manage-announcements.php');
        exit();
    }
}

// Get all announcements
$stmt = $pdo->prepare("SELECT a.*, u.full_name as author FROM announcements a LEFT JOIN users u ON a.posted_by = u.id ORDER BY a.created_at DESC");
$stmt->execute();
$announcements = $stmt->fetchAll();

$csrf_token = generateCSRFToken();
?>
<?php include '../includes/header.php'; ?>

<div class="container">
    <h1>Manage Announcements</h1>
    
    <?php if ($action === 'add' || $action === 'edit'): ?>
        <!-- Add/Edit Form -->
        <div class="card">
            <h2><?php echo $action === 'add' ? 'Add New' : 'Edit'; ?> Announcement</h2>
            
            <?php if ($error): ?>
                <div class="flash-message flash-error"><?php echo escapeOutput($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="id" value="<?php echo $announcement['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" required value="<?php echo escapeOutput($announcement['title'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="general" <?php echo (isset($announcement['category']) && $announcement['category'] === 'general') ? 'selected' : ''; ?>>General</option>
                        <option value="academic" <?php echo (isset($announcement['category']) && $announcement['category'] === 'academic') ? 'selected' : ''; ?>>Academic</option>
                        <option value="exam" <?php echo (isset($announcement['category']) && $announcement['category'] === 'exam') ? 'selected' : ''; ?>>Exam</option>
                        <option value="holiday" <?php echo (isset($announcement['category']) && $announcement['category'] === 'holiday') ? 'selected' : ''; ?>>Holiday</option>
                        <option value="event" <?php echo (isset($announcement['category']) && $announcement['category'] === 'event') ? 'selected' : ''; ?>>Event</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="content">Content *</label>
                    <textarea id="content" name="content" rows="8" required><?php echo escapeOutput($announcement['content'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_published" value="1" <?php echo (!isset($announcement) || $announcement['is_published']) ? 'checked' : ''; ?>>
                        Publish immediately
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Announcement</button>
                <a href="manage-announcements.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
        
    <?php else: ?>
        <!-- List View -->
        <a href="?action=add" class="btn btn-primary" style="margin-bottom: 1rem;">
            <i class="fas fa-plus"></i> Add New Announcement
        </a>
        
        <?php if (count($announcements) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($announcements as $item): ?>
                        <tr>
                            <td><?php echo $item['id']; ?></td>
                            <td><?php echo escapeOutput($item['title']); ?></td>
                            <td><span class="announcement-category category-<?php echo $item['category']; ?>"><?php echo ucfirst($item['category']); ?></span></td>
                            <td><?php echo escapeOutput($item['author']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($item['created_at'])); ?></td>
                            <td>
                                <?php if ($item['is_published']): ?>
                                    <span style="color: var(--secondary-color);">Published</span>
                                <?php else: ?>
                                    <span style="color: var(--danger-color);">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-primary" style="padding: 0.25rem 0.5rem;">Edit</a>
                                <a href="?delete=<?php echo $item['id']; ?>" class="btn btn-danger" style="padding: 0.25rem 0.5rem;" data-confirm="Are you sure you want to delete this announcement?">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No announcements yet. Click "Add New Announcement" to get started.</p>
        <?php endif; ?>
        
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>