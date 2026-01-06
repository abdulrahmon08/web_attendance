<?php
require_once '../include/config.php';

// Require admin session
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$today = date('Y-m-d');

$stmt = $conn->prepare("SELECT s.student_id, s.name, s.email_address, s.school_name, s.gender, s.phone_number, s.date_joined,
    a.auth_code, COALESCE(a.auth_used,0) AS auth_used
    FROM students s
    LEFT JOIN attendance a ON a.student_id = s.id AND a.attendance_date = ?
    ORDER BY s.date_joined DESC");
$stmt->execute([$today]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = 'students_' . date('Ymd') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo "\xEF\xBB\xBF"; // BOM for Excel

$out = fopen('php://output', 'w');
fputcsv($out, ['Student ID', 'Name', 'Email', 'School', 'Gender', 'Phone', 'Date Joined', 'Auth Code', 'Auth Used']);

foreach ($rows as $r) {
    fputcsv($out, [
        $r['student_id'],
        $r['name'],
        $r['email_address'],
        $r['school_name'],
        $r['gender'],
        $r['phone_number'],
        $r['date_joined'],
        $r['auth_code'] ?? '',
        $r['auth_used'] ? 'Yes' : 'No'
    ]);
}

fclose($out);
exit;
