<?php
require_once '../include/config.php';

// Security check
if(!isset($_SESSION['student_id'])){
    header("Location: ../index.php");
    exit;
}

// Ensure correct timezone
date_default_timezone_set('Africa/Lagos'); // Change to your timezone

$student_id = $_SESSION['student_id'];
$today = date('Y-m-d');
$time = date('H:i:s');

// 1. PREVENT DOUBLE ATTENDANCE
$stmt = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND attendance_date = ?");
$stmt->execute([$student_id, $today]);

if ($stmt->fetch()) {
    $_SESSION['error'] = "Attendance already marked for today.";
} else {
    // 2. INSERT ATTENDANCE
    $stmt = $conn->prepare("INSERT INTO attendance (student_id, attendance_date, status, check_in_time) VALUES (?, ?, 'Present', ?)");
    
    if ($stmt->execute([$student_id, $today, $time])) {
        $_SESSION['success'] = "Attendance marked successfully at $time.";
    } else {
        $_SESSION['error'] = "Database error. Failed to mark attendance.";
    }
}

// Redirect back to dashboard or an attendance list page
header("Location: index.php");
exit;