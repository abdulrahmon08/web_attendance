<?php
require_once '../include/config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: ../index.php");
    exit;
}

$student_id = $_SESSION['student_id'];

$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT attendance_date, status, check_in_time, checkout_time, grade FROM attendance WHERE student_id = ? ORDER BY attendance_date DESC");
$stmt->execute([$student_id]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Record - <?= htmlspecialchars($student['name'] ?? 'Student') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print { .no-print { display: none !important; } }
        body { padding: 20px; }
        .table th, .table td { vertical-align: middle; }
    </style>
</head>
<body>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0"><?= htmlspecialchars($student['name'] ?? 'Student') ?></h4>
            <small class="text-muted">ID: <?= htmlspecialchars($student['student_id'] ?? '') ?> | Email: <?= htmlspecialchars($student['email_address'] ?? '') ?></small>
        </div>
        <div class="no-print">
            <button class="btn btn-primary me-2" onclick="window.print()">Print / Save as PDF</button>
            <a href="export.php?format=excel" class="btn btn-success">Download Excel</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Status</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No attendance records found</td></tr>
                    <?php else: ?>
                        <?php foreach ($records as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['attendance_date']) ?></td>
                                <td><?= date('l', strtotime($r['attendance_date'])) ?></td>
                                <td><?= htmlspecialchars($r['status']) ?></td>
                                <td><?= $r['check_in_time'] ? date('h:i A', strtotime($r['check_in_time'])) : '-- : --' ?></td>
                                <td><?= $r['checkout_time'] ? date('h:i A', strtotime($r['checkout_time'])) : '-- : --' ?></td>
                                <td><?= intval($r['grade']) ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
