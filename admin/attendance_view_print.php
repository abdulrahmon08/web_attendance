<?php
require_once '../include/config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$date = $_GET['date'] ?? null;
if (!$date) {
    die('Date parameter required');
}

$stmt = $conn->prepare("SELECT s.name, s.student_id, a.status, a.check_in_time, a.checkout_time, COALESCE(a.grade,'') AS grade, a.auth_code, COALESCE(a.auth_used,0) AS auth_used
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    WHERE a.attendance_date = ?
    ORDER BY s.name");
$stmt->execute([$date]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Attendance for <?= htmlspecialchars($date) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>@media print{.no-print{display:none!important}} body{padding:20px}</style>
</head>
<body>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Attendance for <?= htmlspecialchars($date) ?></h4>
      <small class="text-muted">Generated: <?= date('F j, Y, h:i A') ?></small>
    </div>
    <div class="no-print">
      <button class="btn btn-primary" onclick="window.print()">Print / Save as PDF</button>
      <a href="attendance_view_export.php?date=<?= urlencode($date) ?>" class="btn btn-success ms-2">Download Excel</a>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered">
      <thead class="table-light">
        <tr>
          <th>Student ID</th>
          <th>Name</th>
          <th>Status</th>
          <th>Check-in</th>
          <th>Check-out</th>
          <th>Grade</th>
          <th>Auth Code</th>
          <th>Used</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($students)): ?>
          <tr><td colspan="8" class="text-center">No records found</td></tr>
        <?php else: ?>
          <?php foreach ($students as $s): ?>
            <tr>
              <td><?= htmlspecialchars($s['student_id']) ?></td>
              <td><?= htmlspecialchars($s['name']) ?></td>
              <td><?= htmlspecialchars($s['status']) ?></td>
              <td><?= $s['check_in_time'] ? date('h:i A', strtotime($s['check_in_time'])) : '-- : --' ?></td>
              <td><?= $s['checkout_time'] ? date('h:i A', strtotime($s['checkout_time'])) : '-- : --' ?></td>
              <td><?= htmlspecialchars($s['grade']) ?></td>
              <td><?= htmlspecialchars($s['auth_code'] ?? '') ?></td>
              <td><?= $s['auth_used'] ? 'Yes' : 'No' ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
