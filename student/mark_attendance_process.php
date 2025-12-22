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

if (!isset($_POST['confirm_attendance'])) {
    header("Location: mark_attendance.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$today = date('Y-m-d');
$currentTime = date('H:i:s');

/* =========================
   FETCH ATTENDANCE DATE
========================= */
$stmt = $conn->prepare("
    SELECT opened_at, status 
    FROM attendance_dates 
    WHERE attendance_date = ?
");
$stmt->execute([$today]);
$attendance_date = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$attendance_date) {
    $_SESSION['error'] = "Attendance has not been opened by admin today.";
    header("Location: mark_attendance.php");
    exit;
}

if ($attendance_date['status'] === 'Closed') {
    $_SESSION['error'] = "Attendance is closed for today.";
    header("Location: mark_attendance.php");
    exit;
}

/* =========================
   FETCH STUDENT RECORD
========================= */
$stmt = $conn->prepare("
    SELECT status 
    FROM attendance 
    WHERE student_id = ? 
    AND attendance_date = ?
");
$stmt->execute([$student_id, $today]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    $_SESSION['error'] = "Attendance record not found.";
    header("Location: mark_attendance.php");
    exit;
}

if ($record['status'] === 'Present') {
    $_SESSION['error'] = "You have already marked attendance today.";
    header("Location: mark_attendance.php");
    exit;
}

/* =========================
   ATTENDANCE GRADING BASED ON OPEN TIME
========================= */
$grade = 0;

// Admin opened time
$opened_at = strtotime($attendance_date['opened_at']);
$now = strtotime($currentTime);
$diffMinutes = ($now - $opened_at) / 60;

if ($diffMinutes >= 0 && $diffMinutes <= 15) {
    $grade = 100;
} elseif ($diffMinutes > 10 && $diffMinutes <= 30) {
    $grade = 75;
} elseif ($diffMinutes > 30 && $diffMinutes <= 45) {
    $grade = 50;
} elseif ($diffMinutes > 45 && $diffMinutes <= 60) {
    $grade = 25;
} else {
    $grade = 0;
}

/* =========================
   UPDATE ATTENDANCE
========================= */
$update = $conn->prepare("
    UPDATE attendance 
    SET 
        status = 'Present',
        check_in_time = ?,
        grade = ?
    WHERE student_id = ? 
    AND attendance_date = ? 
    AND status = 'Absent'
");

if ($update->execute([$currentTime, $grade, $student_id, $today])) {
    $_SESSION['success'] = "Attendance marked successfully. Grade: {$grade}%";
} else {
    $_SESSION['error'] = "Failed to mark attendance.";
}

header("Location: mark_attendance.php");
exit;
