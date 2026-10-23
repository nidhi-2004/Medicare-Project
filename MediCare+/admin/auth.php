
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prevent protected pages from being cached.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
?>
