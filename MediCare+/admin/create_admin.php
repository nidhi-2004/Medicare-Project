<?php
// Run once locally to create the initial administrator account.
// Delete this file immediately after setup.
require_once __DIR__ . '/../config/admin_db.php';
$username = 'admin';
$password = 'Admin@123';

$check = $conn->prepare('SELECT id FROM admins WHERE username = ? LIMIT 1');
if (!$check) { exit('The admins table is missing. Import database_backend_setup.sql first.'); }
$check->bind_param('s', $username);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo 'The admin account already exists. For security, this page will not reset its password.';
    $check->close(); $conn->close(); exit;
}
$check->close();
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO admins (username, password) VALUES (?, ?)');
$stmt->bind_param('ss', $username, $hash);
if ($stmt->execute()) {
    echo 'Admin created successfully.<br>Username: admin<br>Password: Admin@123<br><strong>Delete admin/create_admin.php now.</strong>';
} else {
    echo 'Could not create the admin account. Check the admins table.';
}
$stmt->close(); $conn->close();
?>