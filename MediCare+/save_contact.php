
<?php
include 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Please submit the contact form first.");
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if (
    $name === '' ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $phone === '' ||
    $subject === '' ||
    $message === ''
) {
    exit("Error: A field is missing or the email is invalid.");
}

$sql = "INSERT INTO contacts
        (name, email, phone, subject, message)
        VALUES (?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    exit("SQL prepare error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $stmt,
    "sssss",
    $name,
    $email,
    $phone,
    $subject,
    $message
);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: contact.html?sent=1");
    exit;
}

echo "Database insert error: " . mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);
?>
