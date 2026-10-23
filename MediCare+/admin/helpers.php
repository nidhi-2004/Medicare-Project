<?php
require_once __DIR__ . '/../config/db.php';

function h($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
function table_exists(mysqli $conn, string $table): bool {
    $safe = $conn->real_escape_string($table);
    $res = $conn->query("SHOW TABLES LIKE '{$safe}'");
    return $res && $res->num_rows > 0;
}
function columns_for(mysqli $conn, string $table): array {
    if (!table_exists($conn, $table)) return [];
    $cols = [];
    $res = $conn->query("SHOW COLUMNS FROM `{$table}`");
    if ($res) while ($row = $res->fetch_assoc()) $cols[] = $row['Field'];
    return $cols;
}
function admin_start(string $title): void {
    require_once __DIR__ . '/auth.php';
    $username = $_SESSION['admin_username'] ?? 'Admin';
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>'.h($title).' | MediCare+</title><link rel="stylesheet" href="../css/style.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"><style>
    *{box-sizing:border-box}body{margin:0;background:#f6fafb;color:#123b5d;font-family:Arial,sans-serif}.top{background:#123b5d;color:white;padding:17px 5%;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}.brand{font-size:22px;font-weight:bold;color:white;text-decoration:none}.top a{color:white;text-decoration:none}.logout{background:#1596a6;padding:9px 13px;border-radius:6px}.wrap{max-width:1200px;margin:28px auto;padding:0 18px}.nav{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px}.nav a,.btn{display:inline-block;background:#1596a6;color:#fff;text-decoration:none;border:0;padding:9px 12px;border-radius:6px;cursor:pointer;font-size:14px}.nav a{background:#123b5d}.panel{background:white;border:1px solid #e1ecef;border-radius:12px;padding:20px;margin-bottom:20px;box-shadow:0 4px 15px #123b5d0a}.panel h1{margin-top:0}.muted{color:#71808a}.notice{padding:12px;background:#e4f8ed;color:#176b45;border-radius:7px;margin-bottom:15px}.error{padding:12px;background:#fff0f0;color:#a32929;border-radius:7px;margin-bottom:15px}.formgrid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px}.formgrid label{display:block;font-size:13px;font-weight:bold}.formgrid input,.formgrid select,.formgrid textarea{display:block;width:100%;padding:10px;border:1px solid #cddadd;border-radius:6px;margin-top:6px;font:inherit}.formgrid textarea{min-height:80px}.tablewrap{overflow-x:auto}table{width:100%;border-collapse:collapse;background:#fff}th,td{padding:11px 10px;border-bottom:1px solid #e6eef0;text-align:left;vertical-align:top;font-size:14px}th{background:#edf6f7;color:#123b5d}td form{display:inline-flex;gap:5px;align-items:center}td select{padding:7px;border:1px solid #ccd;border-radius:5px}.danger{background:#b93838}.small{font-size:12px}.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:14px}.card{background:#fff;border:1px solid #e1ecef;border-radius:10px;padding:18px}.card strong{display:block;font-size:28px;color:#1596a6;margin-top:8px}.actions{display:flex;gap:6px;flex-wrap:wrap}@media(max-width:600px){.wrap{padding:0 10px}.panel{padding:14px}th,td{padding:8px}}
    </style></head><body><header class="top"><a class="brand" href="dashboard.php"><i class="fa-solid fa-heart-pulse"></i> MediCare+ Admin</a><div>Welcome, '.h($username).' &nbsp; <a href="../index.html"
   style="background:#1596a6;padding:9px 13px;border-radius:6px;margin-right:6px;">
   Home
</a>
<a class="logout" href="logout.php">Logout</a></div></header><main class="wrap"><nav class="nav"><a href="dashboard.php">Dashboard</a><a href="patients.php">Patients</a><a href="doctors.php">Doctors</a><a href="appointments.php">Appointments</a><a href="medicines.php">Medicines</a><a href="pharmacy_orders.php">Pharmacy Orders</a><a href="lab_bookings.php">Lab Bookings</a><a href="messages.php">Messages</a></nav>';
}
function admin_end(): void { echo '</main></body></html>'; }
function flash_message(string $text, bool $error=false): void { echo '<div class="'.($error?'error':'notice').'">'.h($text).'</div>'; }
function fetch_all_rows(mysqli $conn, string $sql): array {
    $result=$conn->query($sql); if(!$result) return [];
    $rows=[]; while($r=$result->fetch_assoc()) $rows[]=$r; return $rows;
}
?>