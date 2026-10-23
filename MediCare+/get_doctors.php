
<?php
header("Content-Type: application/json");

require_once "config/db.php";

$departmentMap = [
    "cardiology" => "Cardiology",
    "neurology" => "Neurology",
    "orthopaedics" => "Orthopaedics",
    "paediatrics" => "Paediatrics",
    "gynaecology" => "Gynaecology"
];

$department = $_GET["department"] ?? "";

if (!isset($departmentMap[$department])) {
    echo json_encode([]);
    exit;
}

$departmentName = $departmentMap[$department];

$sql = "SELECT id, name FROM doctors WHERE department = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $departmentName);
$stmt->execute();

$result = $stmt->get_result();
$doctors = [];

while ($row = $result->fetch_assoc()) {
    $doctors[] = $row;
}

echo json_encode($doctors);

$stmt->close();
$conn->close();
?>
