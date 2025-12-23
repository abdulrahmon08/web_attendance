<?php
$page_title = "Student Dashboard";
require_once '../include/config.php';
require_once '../include/func.php'; // Ensure this file contains fillMissingAttendance()

// =========================
// 1. SECURITY CHECK
// =========================
if (!isset($_SESSION['student_id'])) {
    header("Location: ../index.php");
    exit;
}

$student_id   = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

// =========================
// 2. AUTO-FILL ABSENT DAYS
// =========================
// fillMissingAttendance($conn, $student_id);

// =========================
// 3. OVERALL STATISTICS (GRADE AWARE)
// =========================
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) AS present,
        SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) AS absent,
        SUM(grade) AS total_grade
    FROM attendance
    WHERE student_id = ?
");
$stmt->execute([$student_id]);
$summary = $stmt->fetch(PDO::FETCH_ASSOC);

$total        = (int)$summary['total'];
$present      = (int)$summary['present'];
$absent       = (int)$summary['absent'];
$total_grade  = (int)$summary['total_grade'];

$max_grade        = $total * 100;
$grade_percentage = $max_grade > 0 ? round(($total_grade / $max_grade) * 100) : 0;

// =========================
// 4. THIS MONTH STATISTICS
// =========================
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) AS total,
        SUM(grade) AS total_grade
    FROM attendance
    WHERE student_id = ?
    AND MONTH(attendance_date) = MONTH(CURDATE())
    AND YEAR(attendance_date) = YEAR(CURDATE())
");
$stmt->execute([$student_id]);
$thisMonth = $stmt->fetch(PDO::FETCH_ASSOC);

$this_month_total = (int)$thisMonth['total'];
$this_month_grade = (int)$thisMonth['total_grade'];
$this_month_percentage = $this_month_total > 0 ? round(($this_month_grade / ($this_month_total*100)) * 100) : 0;

// =========================
// 5. RECENT ATTENDANCE
// =========================
$stmt = $conn->prepare("
    SELECT attendance_date, status, check_in_time, checkout_time, grade
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

        <!-- SIDEBAR -->
        <div class="col-md-3 col-lg-2 sidebar p-3 bg-light min-vh-100">
            <div class="mb-4 ps-3">
                <h5 class="text-primary fw-bold">SIWES Portal</h5>
            </div>

            <nav class="nav flex-column">
                <a class="nav-link active mb-2" href="index.php">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a class="nav-link mb-2" href="mark_attendance.php">
                    <i class="bi bi-calendar-check me-2"></i> Mark Attendance
                </a>
                <a class="nav-link mb-2" href="profile.php">
                    <i class="bi bi-person me-2"></i> Profile
                </a>
                <hr>
                <a class="nav-link text-danger" href="../logout.php"
                   onclick="return confirm('Are you sure you want to logout?')">
                    <i class="bi bi-box-arrow-left me-2"></i> Logout
                </a>
            </nav>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-md-9 col-lg-10 p-4">

            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">Dashboard</h2>
                    <p class="text-muted">Attendance & punctuality overview</p>
                </div>
                <div>
                    <span class="badge bg-info text-dark p-2 px-3 rounded-pill">
                        <i class="bi bi-person-circle"></i>
                        <?= htmlspecialchars($student_name) ?>
                    </span>
                </div>
            </div>

            <!-- STAT CARDS -->
            <div class="row mb-4">

                <!-- Attendance Grade -->
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted small fw-bold">ATTENDANCE SCORE</h6>
                                <h3><?= $grade_percentage ?>%</h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="bi bi-graph-up text-primary fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Present -->
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted small fw-bold">TOTAL PRESENT</h6>
                                <h3 class="text-success"><?= $present ?></h3>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="bi bi-check-circle fs-3 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Absent -->
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted small fw-bold">TOTAL ABSENT</h6>
                                <h3 class="text-danger"><?= $absent ?></h3>
                            </div>
                            <div class="bg-danger bg-opacity-10 p-3 rounded">
                                <i class="bi bi-x-circle fs-3 text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- This Month Grade -->
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted small fw-bold">MONTHLY SCORE</h6>
                                <h3><?= $this_month_percentage ?>%</h3>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="bi bi-calendar-check fs-3 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RECENT ATTENDANCE -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-clock-history me-2"></i> Recent Attendance
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Date</th>
                                <th>Day</th>
                                <th>Status</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$recent_records): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No attendance records found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_records as $row): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= $row['attendance_date'] ?></td>
                                        <td><?= date('l', strtotime($row['attendance_date'])) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $row['status'] === 'Present' ? 'success' : 'danger' ?>">
                                                <?= $row['status'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= $row['check_in_time']
                                                ? date('h:i A', strtotime($row['check_in_time']))
                                                : '-- : --' ?>
                                        </td>
                                        <td>
                                            <?= $row['checkout_time']
                                                ? date('h:i A', strtotime($row['checkout_time']))
                                                : '-- : --' ?>
                                        </td>
                                        <td>
                                            <?php
                                                $grade = (int)$row['grade'];
                                                if ($grade == 100) {
                                                    echo '<span class="badge bg-success">100%</span>';
                                                } elseif ($grade == 75) {
                                                    echo '<span class="badge bg-info text-dark">75%</span>';
                                                } elseif ($grade == 50) {
                                                    echo '<span class="badge bg-warning text-dark">50%</span>';
                                                } elseif ($grade == 25) {
                                                    echo '<span class="badge bg-primary text-white">25%</span>';
                                                } else {
                                                    echo '<span class="badge bg-danger">0%</span>';
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- POLICY -->
            <div class="alert alert-info mt-3 small">
                <strong>Grade Illustration:</strong><br>
                09:00 – 09:15 AM → 100%<br>
                09:16 – 09:30 AM → 75%<br>
                09:31 – 09:45 AM → 50%<br>
                09:46 – 10:00 AM → 25%<br>
                After 10:00 AM → 0%
            </div>

        </div>
    </div>
</div>

<?php require_once '../layout/student/footer.php'; ?>
