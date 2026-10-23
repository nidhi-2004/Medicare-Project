
<?php
session_start();
require_once "config/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.html");
    exit;
}

$mobile = trim($_POST["mobile"] ?? "");
$password = $_POST["password"] ?? "";

if (!preg_match("/^[0-9]{10}$/", $mobile) || $password === "") {
    exit("Please enter a valid mobile number and password.");
}

$sql = "SELECT id, name, mobile, password, role
        FROM users
        WHERE mobile = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    exit("Database query failed. Check your database connection.");
}

$stmt->bind_param("s", $mobile);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

if ($user && password_verify($password, $user["password"])) {
    session_regenerate_id(true);

    $_SESSION["user_id"] = $user["id"];
    $_SESSION["user_name"] = $user["name"];
    $_SESSION["user_role"] = $user["role"];

   
header("Location: index.html");
exit;
}

echo "<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>Login Error | MediCare+</title>
<link rel='stylesheet' href='css/style.css'>
</head>
<body>
<section class='login-section'>
<div class='login-card'>
<h1>Login Failed</h1>
<p>Invalid mobile number or password.</p>
<a href='login.html'>Try Again</a>
</div>
</section>
</body>
</html>";
?>
