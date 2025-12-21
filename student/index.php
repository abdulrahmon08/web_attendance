<?php
$page_title = "Student Dashboard";
require_once '../include/config.php';



$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

// 1. Overall Summary
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) AS present,
        SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) AS absent
    FROM attendance
    WHERE student_id = ?
");
$stmt->execute([$student_id]);
$summary = $stmt->fetch(PDO::FETCH_ASSOC);

$total = (int)$summary['total'];
$present = (int)$summary['present'];
$absent = (int)$summary['absent'];
$percentage = $total > 0 ? round(($present / $total) * 100) : 0;

// 2. This Month Statistics
$stmt = $conn->prepare("
    SELECT 
        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) AS present,
        COUNT(*) AS total
    FROM attendance
    WHERE student_id = ?
    AND MONTH(attendance_date) = MONTH(CURDATE())
    AND YEAR(attendance_date) = YEAR(CURDATE())
");
$stmt->execute([$student_id]);
$thisMonth = $stmt->fetch(PDO::FETCH_ASSOC);

// 3. Recent Attendance Records
$stmt = $conn->prepare("
    SELECT attendance_date, status, check_in_time
    FROM attendance
    WHERE student_id = ?
    ORDER BY attendance_date DESC
    LIMIT 5
");
$stmt->execute([$student_id]);
$recent_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../layout/student/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar p-3 bg-light min-vh-100">
            <nav class="nav flex-column">
                <a class="nav-link active" href="index.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a class="nav-link" href="mark_attendance.php"><i class="bi bi-calendar-check"></i> Mark Attendance</a>
                <a class="nav-link" href="profile.php"><i class="bi bi-person"></i> Profile</a>
                <a class="nav-link text-danger" href="../logout.php"  onclick="return confirm('Are you sure you want to logout?')"><i class="bi bi-box-arrow-left"></i> Logout</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Dashboard</h2>
                <span class="badge bg-info text-dark p-2">
                    Welcome back, <?php echo htmlspecialchars($student_name); ?>
                </span>
            </div>

            <div class="row mb-4">
                <!-- Percentage Card -->
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><h6 class="text-muted">Attendance</h6><h3><?= $percentage ?>%</h3></div>
                            <i class="bi bi-graph-up text-primary fs-1"></i>
                        </div>
                    </div>
                </div>
                <!-- Present Card -->
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><h6 class="text-muted">Present</h6><h3><?= $present ?></h3></div>
                            <i class="bi bi-check-circle text-success fs-1"></i>
                        </div>
                    </div>
                </div>
                <!-- Absent Card -->
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><h6 class="text-muted">Absent</h6><h3><?= $absent ?></h3></div>
                            <i class="bi bi-x-circle text-danger fs-1"></i>
                        </div>
                    </div>
                </div>
                <!-- This Month Card -->
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div><h6 class="text-muted">This Month</h6><h3><?= (int)$thisMonth['present'] ?>/<?= (int)$thisMonth['total'] ?></h3></div>
                            <i class="bi bi-calendar-month text-info fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><strong>Recent Attendance</strong></div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr><th>Date</th><th>Day</th><th>Status</th><th>Time</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_records as $row): ?>
                                <tr>
                                    <td><?= $row['attendance_date'] ?></td>
                                    <td><?= date('l', strtotime($row['attendance_date'])) ?></td>
                                    <td><span class="badge bg-<?= $row['status'] == 'Present' ? 'success' : 'danger' ?>"><?= $row['status'] ?></span></td>
                                    <td><?= $row['check_in_time'] ?? '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../layout/student/footer.php'; ?>