<?php
require_once '../include/config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: ../index.php");
    exit;
}

$student_id = $_SESSION['student_id'];

$stmt = $conn->prepare("SELECT student_id, name FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT attendance_date, status, check_in_time, checkout_time, grade FROM attendance WHERE student_id = ? ORDER BY attendance_date ASC");
$stmt->execute([$student_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$file_label = $student['student_id'] ?? $student_id;
$filename = 'attendance_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $file_label) . '_' . date('Ymd') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
// UTF-8 BOM for Excel compatibility
echo "\xEF\xBB\xBF";

$out = fopen('php://output', 'w');
fputcsv($out, ['Date', 'Day', 'Status', 'Check-in', 'Check-out', 'Grade']);

foreach ($rows as $r) {
    fputcsv($out, [
        $r['attendance_date'],
        date('l', strtotime($r['attendance_date'])),
        $r['status'],
        $r['check_in_time'] ? date('h:i A', strtotime($r['check_in_time'])) : '',
        $r['checkout_time'] ? date('h:i A', strtotime($r['checkout_time'])) : '',
        ($r['grade'] ?? 0) . '%'
    ]);
}

fclose($out);
exit;
