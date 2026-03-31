<?php
$page_title = "Manage Events";
require_once '../config.php';
requireRole('admin');

$action = $_GET['action'] ?? 'list';
$error = '';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['flash_message'] = 'Event deleted successfully.';
        $_SESSION['flash_type'] = 'success';
        header('Location: manage-events.php');
        exit();
    }
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'add' || $action === 'edit')) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $title = sanitizeInput($_POST['title'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $event_date = sanitizeInput($_POST['event_date'] ?? '');
        $event_time = sanitizeInput($_POST['event_time'] ?? '');
        $location = sanitizeInput($_POST['location'] ?? '');
        $event_type = sanitizeInput($_POST['event_type'] ?? 'other');
        $is_public = isset($_POST['is_public']) ? 1 : 0;
        
        if (empty($title) || empty($event_date)) {
            $error = 'Please fill all required fields.';
        } else {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, event_time, location, event_type, is_public, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$title, $description, $event_date, $event_time, $location, $event_type, $is_public, $_SESSION['user_id']])) {
                    $_SESSION['flash_message'] = 'Event added successfully.';
                    $_SESSION['flash_type'] = 'success';
                    header('Location: manage-events.php');
                    exit();
                }
            } else {
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, event_time = ?, location = ?, event_type = ?, is_public = ? WHERE id = ?");
                if ($stmt->execute([$title, $description, $event_date, $event_time, $location, $event_type, $is_public, $id])) {
                    $_SESSION['flash_message'] = 'Event updated successfully.';
                    $_SESSION['flash_type'] = 'success';
                    header('Location: manage-events.php');
                    exit();
                }
            }
        }
    }
}

// Get event for editing
$event = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$id]);
    $event = $stmt->fetch();
    if (!$event) {
        header('Location: manage-events.php');
        exit();
    }
}

// Get all events
$stmt = $pdo->prepare("SELECT * FROM events ORDER BY event_date DESC");
$stmt->execute();
$events = $stmt->fetchAll();

$csrf_token = generateCSRFToken();
?>
<?php include '../includes/header.php'; ?>

<div class="container">
    <h1>Manage Events</h1>
    
    <?php if ($action === 'add' || $action === 'edit'): ?>
        <!-- Add/Edit Form -->
        <div class="card">
            <h2><?php echo $action === 'add' ? 'Add New' : 'Edit'; ?> Event</h2>
            
            <?php if ($error): ?>
                <div class="flash-message flash-error"><?php echo escapeOutput($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="title">Event Title *</label>
                    <input type="text" id="title" name="title" required value="<?php echo escapeOutput($event['title'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="event_date">Event Date *</label>
                    <input type="date" id="event_date" name="event_date" required value="<?php echo escapeOutput($event['event_date'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="event_time">Event Time</label>
                    <input type="time" id="event_time" name="event_time" value="<?php echo escapeOutput($event['event_time'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" value="<?php echo escapeOutput($event['location'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="event_type">Event Type</label>
                    <select id="event_type" name="event_type">
                        <option value="academic" <?php echo (isset($event['event_type']) && $event['event_type'] === 'academic') ? 'selected' : ''; ?>>Academic</option>
                        <option value="sports" <?php echo (isset($event['event_type']) && $event['event_type'] === 'sports') ? 'selected' : ''; ?>>Sports</option>
                        <option value="cultural" <?php echo (isset($event['event_type']) && $event['event_type'] === 'cultural') ? 'selected' : ''; ?>>Cultural</option>
                        <option value="meeting" <?php echo (isset($event['event_type']) && $event['event_type'] === 'meeting') ? 'selected' : ''; ?>>Meeting</option>
                        <option value="holiday" <?php echo (isset($event['event_type']) && $event['event_type'] === 'holiday') ? 'selected' : ''; ?>>Holiday</option>
                        <option value="other" <?php echo (isset($event['event_type']) && $event['event_type'] === 'other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5"><?php echo escapeOutput($event['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_public" value="1" <?php echo (!isset($event) || $event['is_public']) ? 'checked' : ''; ?>>
                        Make this event public
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Event</button>
                <a href="manage-events.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
        
    <?php else: ?>
        <!-- List View -->
        <a href="?action=add" class="btn btn-primary" style="margin-bottom: 1rem;">
            <i class="fas fa-plus"></i> Add New Event
        </a>
        
        <?php if (count($events) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $item): ?>
                        <tr>
                            <td><?php echo $item['id']; ?></td>
                            <td><?php echo escapeOutput($item['title']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($item['event_date'])); ?></td>
                            <td><?php echo ucfirst($item['event_type']); ?></td>
                            <td><?php echo escapeOutput($item['location']); ?></td>
                            <td>
                                <?php if ($item['is_public']): ?>
                                    <span style="color: var(--secondary-color);">Public</span>
                                <?php else: ?>
                                    <span style="color: var(--gray-color);">Private</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-primary" style="padding: 0.25rem 0.5rem;">Edit</a>
                                <a href="?delete=<?php echo $item['id']; ?>" class="btn btn-danger" style="padding: 0.25rem 0.5rem;" data-confirm="Are you sure you want to delete this event?">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No events yet. Click "Add New Event" to get started.</p>
        <?php endif; ?>
        
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>