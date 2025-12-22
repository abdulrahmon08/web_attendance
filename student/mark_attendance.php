<?php
$page_title = "Mark Attendance";
require_once '../include/config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: ../index.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$today = date('Y-m-d');
$day_num = (int)date('N');
$is_weekday = ($day_num >= 1 && $day_num <= 5);

// Check if attendance is open
$stmt = $conn->prepare("
    SELECT opened_at, status 
    FROM attendance_dates 
    WHERE attendance_date = ?
");
$stmt->execute([$today]);
$attendance_date = $stmt->fetch(PDO::FETCH_ASSOC);
$attendance_open = $attendance_date && $attendance_date['status'] === 'Open';

// Fetch student attendance
$current_status = null;
$grade = null;
$check_in_time = null;

if ($attendance_open || $attendance_date) {
    $stmt = $conn->prepare("
        SELECT status, grade, check_in_time
        FROM attendance 
        WHERE student_id = ? AND attendance_date = ?
    ");
    $stmt->execute([$student_id, $today]);
    $attendance_record = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($attendance_record) {
        $current_status = $attendance_record['status'];
        $grade = $attendance_record['grade'];
        $check_in_time = $attendance_record['check_in_time'];
    }
}

require_once '../layout/student/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar omitted for brevity -->

        <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">Mark Attendance</h2>
                    <p class="text-muted">Register your presence for today</p>
                </div>
                <div class="text-end">
                    <h5 class="mb-0 fw-bold"><?= date('l') ?></h5>
                    <span class="text-muted"><?= date('F j, Y') ?></span>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center p-5">

                            <?php if (!$is_weekday): ?>
                                <div class="alert alert-info">Weekend: Attendance not required.</div>
                                <a href="index.php" class="btn btn-outline-secondary mt-3">Back to Dashboard</a>

                            <?php elseif (!$attendance_open): ?>
                                <div class="alert alert-warning">Attendance not opened by admin.</div>
                                <a href="index.php" class="btn btn-outline-secondary mt-3">Back to Dashboard</a>

                            <?php elseif ($current_status === 'Present'): ?>
                                <i class="bi bi-check-circle-fill text-success mb-3" style="font-size:4rem;"></i>
                                <div class="alert alert-success">
                                    Attendance confirmed! Grade: <strong><?= $grade ?>%</strong><br>
                                    Checked in at: <?= $check_in_time ? date('h:i A', strtotime($check_in_time)) : '--:--' ?>
                                </div>
                                <a href="index.php" class="btn btn-primary mt-3">Go to Dashboard</a>

                            <?php else: ?>
                                <i class="bi bi-calendar2-check text-primary mb-3" style="font-size:4rem;"></i>
                                <div class="alert alert-danger">Your current status is <strong>Absent</strong>.</div>
                                <h4>Confirm Attendance</h4>
                                <form action="mark_attendance_process.php" method="post">
                                    <button type="submit" name="confirm_attendance" class="btn btn-primary btn-lg">
                                        Mark as Present
                                    </button>
                                </form>
                                <p class="small text-muted mt-3">Current Time: <?= date('h:i A') ?></p>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once '../layout/student/footer.php'; ?>
