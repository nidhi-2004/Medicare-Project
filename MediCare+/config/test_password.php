
<?php
require_once '../config/admin_db.php';

$username = 'admin';
$password = 'Admin@123';

$stmt = $conn->prepare(
    "SELECT password FROM admins WHERE username = ?"
);
$stmt->bind_param("s", $username);
$stmt->execute();

$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (!$admin) {
    echo "Admin username not found.";
} elseif (password_verify($password, $admin['password'])) {
    echo "Password is correct. Login query should work.";
} else {
    echo "Stored password does not match Admin@123.";
}

$stmt->close();
$conn->close();
?>
