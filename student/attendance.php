<?php
$page_title = "My Attendance";
require_once '../include/config.php';
require_once '../layout/student/header.php';

// ============================
// LOGIN CHECK
// ============================
// if (!isset($_SESSION['student_id'])) {
//     header("Location: login.php");
//     exit;
// }

$student_id = $_SESSION['student_id'];

// ============================
// FETCH STUDENT INFO
// ============================
// $stmt = $conn->prepare("SELECT name, email_address FROM students WHERE id = ?");
// $stmt->execute([$student_id]);
// $student = $stmt->fetch(PDO::FETCH_ASSOC);

// if (!$student) {
//     die("Student record not found.");
// }

// ============================
// ATTENDANCE SUMMARY
// ============================
$stmt = $conn->prepare("
    SELECT 
        COALESCE(SUM(status='Present'), 0) AS present,
        COALESCE(SUM(status='Absent'), 0) AS absent,
        COUNT(*) AS total
    FROM attendance
    WHERE student_id = ?
");
$stmt->execute([$student_id]);
$summary = $stmt->fetch(PDO::FETCH_ASSOC);

$percentage = $summary['total'] > 0
    ? round(($summary['present'] / $summary['total']) * 100)
    : 0;

// ============================
// FETCH ATTENDANCE RECORDS
// ============================
$stmt = $conn->prepare("
    SELECT attendance_date, status, check_in_time
    FROM attendance
    WHERE student_id = ?
    ORDER BY attendance_date DESC
");
$stmt->execute([$student_id]);
$attendance = $stmt;
?>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-3 col-lg-2 sidebar p-3">
            <nav class="nav flex-column">
                <a class="nav-link" href="index.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a class="nav-link active" href="attendance.php">
                    <i class="bi bi-calendar-check"></i> My Attendance
                </a>
                <a class="nav-link" href="#">
                    <i class="bi bi-person"></i> Profile
                </a>
            </nav>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-md-9 col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>My Attendance</h2>
                <span class="text-muted">
                    <?= htmlspecialchars($student['email_address']) ?>
                </span>
            </div>

            <!-- SUMMARY CARDS -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Attendance %</h6>
                            <h3><?= $percentage ?>%</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Present</h6>
                            <h3><?= $summary['present'] ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Absent</h6>
                            <h3><?= $summary['absent'] ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Days</h6>
                            <h3><?= $summary['total'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SUCCESS / ERROR MESSAGES -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- ATTENDANCE TABLE -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-table"></i> Attendance Records
                    </h5>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Status</th>
                                    <th>Check-in Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $attendance->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['attendance_date']) ?></td>
                                        <td><?= date('l', strtotime($row['attendance_date'])) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $row['status'] === 'Present' ? 'success' : 'danger' ?>">
                                                <?= htmlspecialchars($row['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($row['check_in_time'] ?? '-') ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <a href="mark_attendance.php" class="btn btn-primary mt-3">
                        <i class="bi bi-calendar-check"></i> Mark Attendance
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
require_once '../layout/student/footer.php';
?>
