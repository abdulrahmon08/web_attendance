<?php
$page_title = "Student Dashboard";
require_once '../include/config.php';
require_once '../layout/student/header.php';

$student_id = $_SESSION['student_id'];

// ============================
// STUDENT INFO
// ============================
$stmt = $conn->prepare("SELECT email_address FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// ============================
// ATTENDANCE SUMMARY
// ============================
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) AS total,
        COALESCE(SUM(status='Present'),0) AS present,
        COALESCE(SUM(status='Absent'),0) AS absent
    FROM attendance
    WHERE student_id = ?
");
$stmt->execute([$student_id]);
$summary = $stmt->fetch(PDO::FETCH_ASSOC);

$total = (int)$summary['total'];
$present = (int)$summary['present'];
$absent = (int)$summary['absent'];

$percentage = $total > 0 ? round(($present / $total) * 100) : 0;

// ============================
// THIS MONTH
// ============================
$stmt = $conn->prepare("
    SELECT 
        COALESCE(SUM(status='Present'),0) AS present,
        COUNT(*) AS total
    FROM attendance
    WHERE student_id = ?
    AND MONTH(attendance_date) = MONTH(CURDATE())
    AND YEAR(attendance_date) = YEAR(CURDATE())
");
$stmt->execute([$student_id]);
$thisMonth = $stmt->fetch(PDO::FETCH_ASSOC);

// ============================
// RECENT ATTENDANCE
// ============================
$stmt = $conn->prepare("
    SELECT attendance_date, status, check_in_time
    FROM attendance
    WHERE student_id = ?
    ORDER BY attendance_date DESC
    LIMIT 5
");
$stmt->execute([$student_id]);
$recent = $stmt;
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-md-3 col-lg-2 sidebar p-3">
            <nav class="nav flex-column">
                <a class="nav-link active" href="#">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a class="nav-link" href="attendance.php">
                    <i class="bi bi-calendar-check"></i> My Attendance
                </a>
                <a class="nav-link" href="#">
                    <i class="bi bi-clock-history"></i> Attendance History
                </a>
                <a class="nav-link" href="#">
                    <i class="bi bi-graph-up"></i> Statistics
                </a>
                <a class="nav-link" href="#">
                    <i class="bi bi-person"></i> Profile
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Dashboard</h2>
                <span class="text-muted">
                    Welcome back! <?php echo $student['name']; ?>
                </span>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Attendance</h6>
                                <h3 class="mb-0"><?= $percentage ?>%</h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="bi bi-calendar-check text-primary" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Present Days</h6>
                                <h3 class="mb-0"><?= $present ?></h3>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Absent Days</h6>
                                <h3 class="mb-0"><?= $absent ?></h3>
                            </div>
                            <div class="bg-danger bg-opacity-10 p-3 rounded">
                                <i class="bi bi-x-circle text-danger" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">This Month</h6>
                                <h3 class="mb-0">
                                    <?= $thisMonth['present'] ?>/<?= $thisMonth['total'] ?>
                                </h3>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="bi bi-calendar-month text-info" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Attendance -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Recent Attendance
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
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $recent->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?= $row['attendance_date'] ?></td>
                                        <td><?= date('l', strtotime($row['attendance_date'])) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $row['status'] === 'Present' ? 'success' : 'danger' ?>">
                                                <?= $row['status'] ?>
                                            </span>
                                        </td>
                                        <td><?= $row['check_in_time'] ?? '-' ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once '../layout/student/footer.php'; ?>
