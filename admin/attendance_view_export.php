<?php
require_once '../include/config.php';
// Require admin session
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$date = $_GET['date'] ?? null;
if (!$date) {
    die('Date parameter required');
}

$stmt = $conn->prepare("SELECT s.name, s.student_id, a.status, a.check_in_time, a.checkout_time, COALESCE(a.grade, '') AS grade, a.auth_code, COALESCE(a.auth_used,0) AS auth_used
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    WHERE a.attendance_date = ?
    ORDER BY s.name");
$stmt->execute([$date]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = 'attendance_' . $date . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
// BOM for Excel
echo "\xEF\xBB\xBF";
$out = fopen('php://output', 'w');

fputcsv($out, ['Student ID','Student Name','Status','Check-in','Check-out','Grade','Auth Code','Auth Used']);
foreach ($rows as $r) {
    fputcsv($out, [
        $r['student_id'],
        $r['name'],
        $r['status'],
        $r['check_in_time'] ? date('h:i A', strtotime($r['check_in_time'])) : '',
        $r['checkout_time'] ? date('h:i A', strtotime($r['checkout_time'])) : '',
        $r['grade'],
        $r['auth_code'] ?? '',
        $r['auth_used'] ? 'Yes' : 'No'
    ]);
}

fclose($out);
exit;
