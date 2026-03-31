<?php
$error_code = $_GET['code'] ?? 404;
$error_messages = [
    400 => ['Bad Request', 'The request could not be understood by the server.'],
    401 => ['Unauthorized', 'You need to login to access this page.'],
    403 => ['Forbidden', 'You don\'t have permission to access this page.'],
    404 => ['Page Not Found', 'The page you are looking for does not exist.'],
    500 => ['Server Error', 'Something went wrong on our end. Please try again later.'],
];
$error = $error_messages[$error_code] ?? $error_messages[404];
$page_title = $error[0];
?>
<?php include 'includes/header.php'; ?>

<div class="container" style="text-align: center; padding: 4rem 0;">
    <h1 style="font-size: 6rem; color: var(--primary-color);"><?php echo $error_code; ?></h1>
    <h2><?php echo $error[0]; ?></h2>
    <p><?php echo $error[1]; ?></p>
    <a href="index.php" class="btn btn-primary" style="margin-top: 1rem;">
        <i class="fas fa-home"></i> Return to Home
    </a>
</div>

<?php include 'includes/footer.php'; ?>