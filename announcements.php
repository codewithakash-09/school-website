<?php
$page_title = "Announcements";
require_once 'config.php';

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get total count
$stmt = $pdo->query("SELECT COUNT(*) as total FROM announcements WHERE is_published = 1");
$total = $stmt->fetch()['total'];
$total_pages = ceil($total / $per_page);

// Get announcements
$stmt = $pdo->prepare("SELECT a.*, u.full_name as author FROM announcements a 
                       LEFT JOIN users u ON a.posted_by = u.id 
                       WHERE a.is_published = 1 
                       ORDER BY a.published_at DESC 
                       LIMIT ? OFFSET ?");
$stmt->execute([$per_page, $offset]);
$announcements = $stmt->fetchAll();

// Get categories for filter
$stmt = $pdo->query("SELECT DISTINCT category FROM announcements WHERE is_published = 1");
$categories = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<div class="container">
    <h1>School Announcements</h1>
    
    <!-- Category Filter -->
    <div style="margin-bottom: 2rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="?page=1" class="btn <?php echo !isset($_GET['cat']) ? 'btn-primary' : 'btn-secondary'; ?>" style="padding: 0.5rem 1rem;">
            All
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="?cat=<?php echo urlencode($cat['category']); ?>&page=1" class="btn btn-secondary" style="padding: 0.5rem 1rem;">
                <?php echo ucfirst($cat['category']); ?>
            </a>
        <?php endforeach; ?>
    </div>
    
    <!-- Announcements List -->
    <?php if (count($announcements) > 0): ?>
        <?php foreach ($announcements as $announcement): ?>
            <div class="announcement-item">
                <div class="announcement-title">
                    <?php echo escapeOutput($announcement['title']); ?>
                    <span class="announcement-category category-<?php echo $announcement['category']; ?>" style="float: right;">
                        <?php echo ucfirst($announcement['category']); ?>
                    </span>
                </div>
                <div class="announcement-date">
                    <i class="far fa-calendar-alt"></i> 
                    <?php echo date('F j, Y', strtotime($announcement['published_at'])); ?>
                    <?php if ($announcement['author']): ?>
                        | <i class="fas fa-user"></i> Posted by: <?php echo escapeOutput($announcement['author']); ?>
                    <?php endif; ?>
                </div>
                <div class="announcement-content">
                    <?php echo nl2br(escapeOutput($announcement['content'])); ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem;">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['cat']) ? '&cat=' . urlencode($_GET['cat']) : ''; ?>" class="btn btn-secondary">Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo isset($_GET['cat']) ? '&cat=' . urlencode($_GET['cat']) : ''; ?>" class="btn <?php echo $i == $page ? 'btn-primary' : 'btn-secondary'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['cat']) ? '&cat=' . urlencode($_GET['cat']) : ''; ?>" class="btn btn-secondary">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="card" style="text-align: center; padding: 3rem;">
            <i class="fas fa-newspaper" style="font-size: 3rem; color: var(--gray-color);"></i>
            <p>No announcements found.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>