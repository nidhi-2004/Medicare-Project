<?php
session_start();
require_once __DIR__ . "/config/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: laboratory.html");
    exit;
}

$name = trim($_POST["patient_name"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$email = trim($_POST["email"] ?? "");
$testName = trim($_POST["test_name"] ?? "");
$date = trim($_POST["booking_date"] ?? "");
$time = trim($_POST["booking_time"] ?? "");

$allowedTimes = [
    "08:00 AM", "09:00 AM", "10:00 AM",
    "11:00 AM", "12:00 PM", "02:00 PM",
    "03:00 PM", "04:00 PM", "05:00 PM"
];

if (
    $name === "" ||
    !preg_match('/^[0-9]{10}$/', $mobile) ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $testName === "" ||
    $date === "" ||
    !in_array($time, $allowedTimes, true)
) {
    exit("Invalid details. Please go back and complete the form correctly.");
}

// Validate date format and prevent past dates.
$dateObject = DateTime::createFromFormat('!Y-m-d', $date);

if (
    !$dateObject ||
    $dateObject->format('Y-m-d') !== $date ||
    $date < date('Y-m-d')
) {
    exit("Please select a valid date today or later.");
}

// Support both the current lab-booking schema and the earlier setup schema.
$bookingColumns = [];
$columnResult = $conn->query("SHOW COLUMNS FROM lab_bookings");
if ($columnResult) {
    while ($column = $columnResult->fetch_assoc()) {
        $bookingColumns[] = $column['Field'];
    }
}

if (in_array('test_id', $bookingColumns, true)) {
    $testCheck = $conn->prepare(
        "SELECT id FROM lab_tests WHERE test_name = ? AND status = 'available' LIMIT 1"
    );
    if (!$testCheck) exit("Laboratory test list is missing. Import database_backend_setup.sql.");
    $testCheck->bind_param("s", $testName);
    $testCheck->execute();
    $testCheck->bind_result($testId);
    if (!$testCheck->fetch()) {
        $testCheck->close();
        exit("The selected test is currently unavailable. Please choose another test.");
    }
    $testCheck->close();

    $stmt = $conn->prepare(
        "INSERT INTO lab_bookings (patient_name, mobile, email, test_id, booking_date, booking_time)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    if (!$stmt) exit("Unable to save booking. Check the lab_bookings table.");
    $stmt->bind_param("sssiss", $name, $mobile, $email, $testId, $date, $time);
} else {
    // Older schema stores test_name and phone directly in lab_bookings.
    $phoneColumn = in_array('phone', $bookingColumns, true) ? 'phone' : 'mobile';
    $dateColumn = in_array('preferred_date', $bookingColumns, true) ? 'preferred_date' : 'booking_date';
    $timeColumn = in_array('preferred_time', $bookingColumns, true) ? 'preferred_time' : 'booking_time';
    if (!in_array('test_name', $bookingColumns, true)) {
        exit("The lab_bookings table has an unsupported structure. Please check database_backend_setup.sql.");
    }
    $sql = "INSERT INTO lab_bookings (patient_name, `$phoneColumn`, test_name, `$dateColumn`, `$timeColumn`) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) exit("Unable to save booking. Check the lab_bookings table.");
    $stmt->bind_param("sssss", $name, $mobile, $testName, $date, $time);
}

if ($stmt->execute()) {
    $bookingId = $conn->insert_id;
    $stmt->close();
    $conn->close();

    // Escape output before displaying submitted details.
    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeTest = htmlspecialchars($testName, ENT_QUOTES, 'UTF-8');
    $safeDate = htmlspecialchars($date, ENT_QUOTES, 'UTF-8');
    $safeTime = htmlspecialchars($time, ENT_QUOTES, 'UTF-8');

    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Booking Confirmation | MediCare+</title>
        <link rel='stylesheet' href='css/style.css'>
        <link rel='stylesheet'
              href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css'>
    </head>
    <body>
        <section class='appointment-page'>
            <div class='appointment-form-container'>
                <div class='success-icon'>
                    <i class='fa-solid fa-check'></i>
                </div>
                <h2>Test Booking Submitted!</h2>
                <p>Thank you, {$safeName}. Your booking request has been saved.</p>
                <p><strong>Booking ID:</strong> " . (int)$bookingId . "</p>
                <p><strong>Test:</strong> {$safeTest}</p>
                <p><strong>Date:</strong> {$safeDate}</p>
                <p><strong>Preferred time:</strong> {$safeTime}</p>
                <p><strong>Status:</strong> Pending</p>
                <p>Our laboratory team will contact you for confirmation.</p>
                <a class='appointment-btn' href='laboratory.html'>Back to Laboratory</a>
                <a href='index.html'>Home</a>
            </div>
        </section>
    </body>
    </html>";
} else {
    $stmt->close();
    $conn->close();
    exit("Booking could not be saved. Please try again.");
}
?>