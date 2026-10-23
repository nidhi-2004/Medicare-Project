<?php
require_once "config/db.php";
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit;
}
$data = json_decode(file_get_contents("php://input"), true) ?? [];
$name = trim($data["name"] ?? "");
$mobile = trim($data["mobile"] ?? "");
$items = $data["items"] ?? [];

if ($name === "" || !preg_match("/^[0-9]{10}$/", $mobile) || !is_array($items) || count($items) === 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Enter your name, 10-digit mobile number, and add a medicine."]);
    exit;
}

$conn->begin_transaction();
try {
    $total = 0.0;
    $cleanItems = [];
    $productStmt = $conn->prepare("SELECT id, price, stock FROM pharmacy_products WHERE name = ? LIMIT 1");
    foreach ($items as $item) {
        $productName = trim($item["name"] ?? "");
        $qty = (int)($item["quantity"] ?? 0);
        if ($productName === "" || $qty < 1) {
            throw new Exception("Invalid medicine quantity.");
        }
        $productStmt->bind_param("s", $productName);
        $productStmt->execute();
        $product = $productStmt->get_result()->fetch_assoc();
        if (!$product) throw new Exception("Medicine not found: " . $productName);
        if ($qty > (int)$product["stock"]) throw new Exception("Not enough stock for " . $productName);
        $price = (float)$product["price"];
        $total += $price * $qty;
        $cleanItems[] = ["id" => (int)$product["id"], "name" => $productName, "price" => $price, "quantity" => $qty];
    }
    $productStmt->close();

    $orderStmt = $conn->prepare("INSERT INTO pharmacy_orders (customer_name, mobile, total_amount) VALUES (?, ?, ?)");
    $orderStmt->bind_param("ssd", $name, $mobile, $total);
    $orderStmt->execute();
    $orderId = $conn->insert_id;
    $orderStmt->close();

    $itemStmt = $conn->prepare("INSERT INTO pharmacy_order_items (order_id, product_id, medicine_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
    $stockStmt = $conn->prepare("UPDATE pharmacy_products SET stock = stock - ? WHERE id = ? AND stock >= ?");
    foreach ($cleanItems as $item) {
        $itemStmt->bind_param("iisdi", $orderId, $item["id"], $item["name"], $item["price"], $item["quantity"]);
        $itemStmt->execute();
        $stockStmt->bind_param("iii", $item["quantity"], $item["id"], $item["quantity"]);
        $stockStmt->execute();
        if ($stockStmt->affected_rows !== 1) throw new Exception("Stock changed. Please try again.");
    }
    $itemStmt->close();
    $stockStmt->close();
    $conn->commit();
    echo json_encode(["success" => true, "message" => "Order placed successfully. Order number: " . $orderId]);
} catch (Throwable $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
