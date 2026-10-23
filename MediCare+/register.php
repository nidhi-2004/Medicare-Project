<?php
session_start();

require_once __DIR__ . "/config/db.php";

// Only accept registration form submissions
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.html");
    exit;
}

// Read form values
$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$password = $_POST["password"] ?? "";

// Validate input
if (
    $name === "" ||
    $email === "" ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 150 ||
    !preg_match('/^[0-9]{10}$/', $mobile) ||
    strlen($password) < 8
) {
    exit(
        "Invalid details. Please enter your name, a valid email, " .
        "a 10-digit mobile number, and a password of at least 8 characters. " .
        "<a href='register.html'>Try again</a>"
    );
}

// Check whether the email or mobile number already exists
$check = $conn->prepare(
    "SELECT id FROM users WHERE email = ? OR mobile = ? LIMIT 1"
);

if (!$check) {
    exit("Unable to check registration details. Please try again.");
}

$check->bind_param("ss", $email, $mobile);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $check->close();

    exit(
        "This email or mobile number is already registered. " .
        "<a href='login.html'>Go to Login</a>"
    );
}

$check->close();

// Hash the password before storing it
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$role = "patient";

// Save the new user
$stmt = $conn->prepare(
    "INSERT INTO users (name, email, mobile, password, role)
     VALUES (?, ?, ?, ?, ?)"
);

if (!$stmt) {
    exit("Unable to prepare registration. Please try again.");
}

$stmt->bind_param(
    "sssss",
    $name,
    $email,
    $mobile,
    $hashedPassword,
    $role
);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();

    // Return to login after successful registration
    header("Location: login.html?registered=success");
    exit;
}

$stmt->close();
$conn->close();

echo "Registration failed. Please try again.";
?>