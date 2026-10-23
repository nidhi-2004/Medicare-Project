<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/config/db.php";
$table = "pharmacy_products";
$result = $conn->query("SELECT id, name, price, stock FROM `$table` ORDER BY name ASC");
if (!$result) {
    http_response_code(500);
    echo json_encode(["error" => "Could not load medicines"]);
    exit;
}
$medicines = [];
while ($row = $result->fetch_assoc()) {
    $medicines[] = [
        "id" => (int)$row["id"],
        "name" => $row["name"],
        "price" => (float)$row["price"],
        "stock" => (int)$row["stock"]
    ];
}
echo json_encode($medicines);
$conn->close();
?>