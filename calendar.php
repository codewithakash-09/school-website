<?php
$page_title = "Calendar";
require_once 'config.php';

// Get month and year parameters
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Adjust for previous/next
if ($month < 1) {
    $month = 12;
    $year--;
}
if ($month > 12) {
    $month = 1;
    $year++;
}

// Get first day of month and number of days
$firstDay = date('N', strtotime("$year-$month-01"));
$daysInMonth = date('t', strtotime("$year-$month-01"));

// Fetch events for this month
$stmt = $pdo->prepare("SELECT * FROM events WHERE MONTH(event_date) = ? AND YEAR(event_date) = ? ORDER BY event_date");
$stmt->execute([$month, $year]);
$events = [];
while ($row = $stmt->fetch()) {
    $day = date('j', strtotime($row['event_date']));
    $events[$day][] = $row;
}
?>
<?php include 'includes/header.php'; ?>

<div class="container">
    <h1>School Calendar</h1>
    
    <!-- Calendar Navigation -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <a href="?month=<?php echo $month - 1; ?>&year=<?php echo $year; ?>" class="btn btn-secondary">
            <i class="fas fa-chevron-left"></i> Previous
        </a>
        <h2 style="margin: 0;"><?php echo date('F Y', strtotime("$year-$month-01")); ?></h2>
        <a href="?month=<?php echo $month + 1; ?>&year=<?php echo $year; ?>" class="btn btn-secondary">
            Next <i class="fas fa-chevron-right"></i>
        </a>
    </div>
    
    <!-- Calendar Grid -->
    <div class="calendar-container">
        <div class="calendar-header">
            <strong><?php echo date('F Y', strtotime("$year-$month-01")); ?></strong>
        </div>
        
        <div class="calendar-grid">
            <div class="calendar-day-header">Mon</div>
            <div class="calendar-day-header">Tue</div>
            <div class="calendar-day-header">Wed</div>
            <div class="calendar-day-header">Thu</div>
            <div class="calendar-day-header">Fri</div>
            <div class="calendar-day-header">Sat</div>
            <div class="calendar-day-header">Sun</div>
            
            <?php
            $currentDay = 1;
            $today = date('j');
            $currentMonth = date('n');
            $currentYear = date('Y');
            
            // Empty cells for days before month starts
            for ($i = 1; $i < $firstDay; $i++) {
                echo '<div class="calendar-day"></div>';
            }
            
            // Days of the month
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $hasEvent = isset($events[$day]);
                $isToday = ($day == $today && $month == $currentMonth && $year == $currentYear);
                $class = 'calendar-day';
                if ($hasEvent) $class .= ' has-event';
                if ($isToday) $class .= ' today';
                
                echo '<div class="' . $class . '" data-day="' . $day . '">';
                echo '<strong>' . $day . '</strong>';
                
                if ($hasEvent) {
                    echo '<div class="event-indicator" style="font-size: 0.7rem; margin-top: 0.25rem;">';
                    echo count($events[$day]) . ' event(s)';
                    echo '</div>';
                }
                echo '</div>';
            }
            
            // Fill remaining cells
            $remaining = (7 - (($daysInMonth + $firstDay - 1) % 7)) % 7;
            for ($i = 0; $i < $remaining; $i++) {
                echo '<div class="calendar-day"></div>';
            }
            ?>
        </div>
    </div>
    
    <!-- Events List -->
    <h2>Events This Month</h2>
    <?php if (count($events) > 0): ?>
        <div class="cards-grid">
            <?php foreach ($events as $day => $dayEvents): ?>
                <?php foreach ($dayEvents as $event): ?>
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
                        <p><?php echo nl2br(escapeOutput($event['description'] ?? '')); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No events scheduled for this month.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>