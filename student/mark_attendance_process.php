<?php
require_once '../include/config.php';


$student_id = $_SESSION['student_id'];
$today = date('Y-m-d');
$time = date('H:i:s');

// ============================
// PREVENT DOUBLE ATTENDANCE
// ============================
$stmt = $conn->prepare("
    SELECT id 
    FROM attendance 
    WHERE student_id = ? AND attendance_date = ?
");
$stmt->execute([$student_id, $today]);

if ($stmt->rowCount() > 0) {
    $_SESSION['error'] = "Attendance already marked for today.";
    header("Location: attendance.php");
    exit;
}

// ============================
// INSERT ATTENDANCE
// ============================
$stmt = $conn->prepare("
    INSERT INTO attendance 
    (student_id, attendance_date, status, check_in_time)
    VALUES (?, ?, 'Present', ?)
");

if ($stmt->execute([$student_id, $today, $time])) {
    $_SESSION['success'] = "Attendance marked successfully.";
} else {
    $_SESSION['error'] = "Failed to mark attendance. Please try again.";
}

header("Location: attendance.php");
exit;
