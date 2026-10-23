
<?php
require_once "config/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Please submit the appointment form.");
}

$name = trim($_POST["patientName"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$department = $_POST["department"] ?? "";
$doctorId = (int)($_POST["doctor"] ?? 0);
$date = $_POST["appointmentDate"] ?? "";
$time = $_POST["appointmentTime"] ?? "";
$reason = trim($_POST["reason"] ?? "");

if (
    $name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $phone === "" || $doctorId <= 0 || $date === "" || $time === ""
) {
    exit("Please fill in all required fields correctly.");
}

$stmt = $conn->prepare(
    "SELECT name, department FROM doctors WHERE id = ?"
);
$stmt->bind_param("i", $doctorId);
$stmt->execute();
$stmt->bind_result($doctorName, $doctorDepartment);

if (!$stmt->fetch()) {
    $stmt->close();
    $conn->close();
    exit("Please select a valid doctor.");
}
$stmt->close();

$stmt = $conn->prepare(
    "INSERT INTO appointments
    (patient_name, email, phone, department, doctor, doctor_id,
     appointment_date, appointment_time, reason)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "sssssisss",
    $name, $email, $phone, $doctorDepartment, $doctorName,
    $doctorId, $date, $time, $reason
);

if ($stmt->execute()) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Appointment Confirmed | MediCare+</title>
        <link rel="stylesheet" href="css/style.css">
        <style>
            .confirmation {
                max-width: 600px;
                margin: 80px auto;
                padding: 35px 25px;
                text-align: center;
                background: white;
                border-radius: 15px;
                box-shadow: 0 5px 25px rgba(0,0,0,0.10);
            }
            .success-icon {
                width: 70px;
                height: 70px;
                margin: 0 auto 20px;
                border-radius: 50%;
                background: #e4f8ed;
                color: #159957;
                font-size: 40px;
                line-height: 70px;
            }
            .confirmation h1 {
                color: #159957;
                font-size: 28px;
            }
            .confirmation p {
                color: #555;
                margin: 15px 0;
            }
            .back-button {
                display: inline-block;
                margin-top: 15px;
                padding: 12px 24px;
                background: #087f8c;
                color: white;
                text-decoration: none;
                border-radius: 6px;
            }
            .back-button:hover {
                background: #066570;
            }
        </style>
    </head>
    <body>
        <div class="confirmation">
            <div class="success-icon">&#10003;</div>
            <h1>Appointment Confirmed!</h1>
            <p>Your appointment has been booked successfully.</p>
            
            <a class="back-button" href="appointment.html">
                Back to Appointment Page
            </a>
        </div>
    </body>
    </html>
    <?php
} else {
    http_response_code(500);
    echo "Unable to book appointment. Please try again.";
}

$stmt->close();
$conn->close();
?>

