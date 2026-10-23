<?php
session_start();
require_once "config/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Invalid request");
}

$name = trim($_POST["customer_name"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$medicineId = (int)($_POST["medicine_id"] ?? 0);
$quantity = (int)($_POST["quantity"] ?? 0);

if ($name === "" || $mobile === "" || $medicineId < 1 || $quantity < 1) {
    exit("Please enter all details correctly.");
}

$stmt = $conn->prepare(
    "SELECT id, name, price, stock FROM medicines WHERE id = ? LIMIT 1"
);

if (!$stmt) {
    exit("Could not check medicine.");
}

$stmt->bind_param("i", $medicineId);
$stmt->execute();
$medicine = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$medicine || $quantity > (int)$medicine["stock"]) {
    exit("Medicine not found or quantity exceeds available stock.");
}

$total = (float)$medicine["price"] * $quantity;

// Check that your orders table has these columns:
// customer_name, mobile, medicine_id, quantity, total_amount
$stmt = $conn->prepare(
    "INSERT INTO orders (customer_name, mobile, medicine_id, quantity, total_amount)
     VALUES (?, ?, ?, ?, ?)"
);

if (!$stmt) {
    exit("Check your orders table columns in phpMyAdmin.");
}

$stmt->bind_param("ssiid", $name, $mobile, $medicineId, $quantity, $total);

if ($stmt->execute()) {
    $stmt->close();
    echo "Order placed successfully!";
} else {
    echo "Unable to save order.";
    $stmt->close();
}
?>