<?php
require_once '../include/config.php';

// Check if a date is selected
$date = $_GET['date'] ?? null;

// Fetch all hosted attendance dates
$dates = $conn->query("
    SELECT attendance_date 
    FROM attendance_dates 
    ORDER BY attendance_date DESC
")->fetchAll(PDO::FETCH_ASSOC);

// If a date is selected, fetch attendance records
$records = [];
if ($date) {
    $stmt = $conn->prepare("
        SELECT s.name, a.status, a.check_in_time
        FROM attendance a
        JOIN students s ON a.student_id = s.id
        WHERE a.attendance_date = ?
        ORDER BY s.name
    ");
    $stmt->execute([$date]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

require_once '../layout/admin/header.php';
?>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar p-3 bg-light min-vh-100">
            <div class="mb-4 ps-3">
                <h5 class="text-primary fw-bold">SIWES Admin</h5>
            </div>

            <nav class="nav flex-column">
                <a class="nav-link mb-2" href="index.php">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a class="nav-link mb-2" href="attendance_dates.php">
                    <i class="bi bi-calendar-plus me-2"></i> Host Attendance
                </a>
                <a class="nav-link active mb-2" href="attendance_view.php">
                    <i class="bi bi-eye me-2"></i> View Attendance
                </a>
                <hr>
                <a class="nav-link text-danger" href="logout.php">
                    <i class="bi bi-box-arrow-left me-2"></i> Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-4">

            <h4 class="mb-4">Attendance Records</h4>

            <!-- LIST OF HOSTED DATES -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">
                    Select Attendance Date
                </div>
                <div class="card-body">
                    <?php if (empty($dates)): ?>
                        <p class="text-muted">No attendance dates hosted yet.</p>
                    <?php else: ?>
                        <?php foreach ($dates as $d): ?>
                            <a href="attendance_view.php?date=<?= $d['attendance_date'] ?>"
                               class="btn btn-outline-primary btn-sm me-2 mb-2">
                                <?= $d['attendance_date'] ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ATTENDANCE TABLE -->
            <?php if ($date): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">
                    Attendance for <?= htmlspecialchars($date) ?>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Student Name</th>
                                <th>Status</th>
                                <th>Check-in Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($records)): ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        No records found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($records as $r): ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?= htmlspecialchars($r['name']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $r['status'] === 'Present' ? 'success' : 'danger' ?>">
                                            <?= $r['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= $r['check_in_time'] ? date('h:i A', strtotime($r['check_in_time'])) : '-- : --' ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php require_once '../layout/admin/footer.php'; ?>
