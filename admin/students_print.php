<?php
require_once '../include/config.php';

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
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Students List</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>@media print{.no-print{display:none!important}} body{padding:20px}</style>
</head>
<body>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Students List</h4>
    <div class="no-print">
      <button class="btn btn-primary" onclick="window.print()">Print / Save as PDF</button>
      <a href="students_export.php" class="btn btn-success ms-2">Download Excel</a>
    </div>
  </div>

  <table class="table table-bordered">
    <thead class="table-light">
      <tr>
        <th>Student ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>School</th>
        <th>Gender</th>
        <th>Phone</th>
        <th>Date Joined</th>
        <th>Auth Code</th>
        <th>Used</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($students)): ?>
        <tr><td colspan="9" class="text-center">No students found</td></tr>
      <?php else: ?>
        <?php foreach ($students as $s): ?>
          <tr>
            <td><?= htmlspecialchars($s['student_id']) ?></td>
            <td><?= htmlspecialchars($s['name']) ?></td>
            <td><?= htmlspecialchars($s['email_address']) ?></td>
            <td><?= htmlspecialchars($s['school_name']) ?></td>
            <td><?= htmlspecialchars($s['gender']) ?></td>
            <td><?= htmlspecialchars($s['phone_number']) ?></td>
            <td><?= htmlspecialchars($s['date_joined']) ?></td>
            <td><?= htmlspecialchars($s['auth_code'] ?? '') ?></td>
            <td><?= $s['auth_used'] ? 'Yes' : 'No' ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
