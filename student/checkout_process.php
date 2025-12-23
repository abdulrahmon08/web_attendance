<?php
require_once '../include/config.php';
date_default_timezone_set('Africa/Lagos');

/* =========================
   SECURITY CHECK
========================= */
if (!isset($_SESSION['student_id'])) {
    header("Location: ../index.php");
    exit;
}

if (!isset($_POST['confirm_checkout'])) {
    header("Location: mark_attendance.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$today = date('Y-m-d');
$current_time = date('H:i:s');

/* =========================
   FETCH STUDENT ATTENDANCE
========================= */
$stmt = $conn->prepare("
    SELECT status, check_in_time, checkout_time
    FROM attendance 
    WHERE student_id = ? AND attendance_date = ?
");
$stmt->execute([$student_id, $today]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    $_SESSION['error'] = "Attendance record not found for today.";
    header("Location: mark_attendance.php");
    exit;
}

/* =========================
   VALIDATIONS
========================= */
if ($record['status'] !== 'Present') {
    $_SESSION['error'] = "You cannot checkout without marking attendance first.";
    header("Location: mark_attendance.php");
    exit;
}

if ($record['checkout_time']) {
    $_SESSION['error'] = "You have already checked out at " . date('h:i A', strtotime($record['checkout_time']));
    header("Location: mark_attendance.php");
    exit;
}

/* =========================
   UPDATE CHECKOUT TIME
========================= */
$update = $conn->prepare("
    UPDATE attendance
    SET checkout_time = ?
    WHERE student_id = ? AND attendance_date = ?
");

if ($update->execute([$current_time, $student_id, $today])) {
    $_SESSION['success'] = "Checkout successful! Checked out at " . date('h:i A');
} else {
    $_SESSION['error'] = "Failed to checkout. Please try again.";
}

header("Location: mark_attendance.php");
exit;
