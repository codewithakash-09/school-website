<?php
$page_title = "Admission Inquiry";
require_once 'config.php';

$success = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security token invalid.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO admissions (student_name, date_of_birth, gender, applying_for_class, father_name, mother_name, parent_email, parent_phone, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([
            sanitizeInput($_POST['student_name']), sanitizeInput($_POST['dob']), 
            sanitizeInput($_POST['gender']), sanitizeInput($_POST['class']),
            sanitizeInput($_POST['father_name']), sanitizeInput($_POST['mother_name']),
            sanitizeInput($_POST['email']), sanitizeInput($_POST['phone']), sanitizeInput($_POST['address'])
        ])) {
            $success = "Application submitted successfully! Our office will contact you.";
        } else { $error = "Submission failed."; }
    }
}
include 'includes/header.php';
?>
<div class="container">
    <h1>Admission Inquiry Form</h1>
    <?php if($success) echo "<div class='flash-message flash-success'>$success</div>"; ?>
    <form method="POST" class="card">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <div class="grid" style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group"><label>Student Name *</label><input type="text" name="student_name" required></div>
            <div class="form-group"><label>Date of Birth *</label><input type="date" name="dob" required></div>
            <div class="form-group"><label>Applying for Class *</label>
                <select name="class"><?php $s=$pdo->query("SELECT DISTINCT class_name FROM classes"); while($r=$s->fetch()) echo "<option>{$r['class_name']}</option>"; ?></select>
            </div>
            <div class="form-group"><label>Parent Email *</label><input type="email" name="email" required></div>
        </div>
        <div class="form-group"><label>Address *</label><textarea name="address" required></textarea></div>
        <button type="submit" class="btn btn-primary">Submit Application</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
