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

// Fetch attendance date
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
$checkout_time = null;
$remaining_minutes = null;
$can_checkout = false;

if ($attendance_date) {
    $stmt = $conn->prepare("
        SELECT status, grade, check_in_time, checkout_time
        FROM attendance 
        WHERE student_id = ? AND attendance_date = ?
    ");
    $stmt->execute([$student_id, $today]);
    $attendance_record = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($attendance_record) {
        $current_status = $attendance_record['status'];
        $grade = $attendance_record['grade'];
        $check_in_time = $attendance_record['check_in_time'];
        $checkout_time = $attendance_record['checkout_time'];
    }

    if ($attendance_open) {
        $opened_at = strtotime($attendance_date['opened_at']);
        $now = time();
        $diff = $now - $opened_at;
        $remaining_minutes = max(0, 60 - floor($diff / 60));

        if ($diff >= 3600) {
            $attendance_open = false; // auto-close after 1 hour
        }
    }

    // Check if student can checkout (already present and not checked out)
    if ($current_status === 'Present' && !$checkout_time) {
        $can_checkout = true;
    }
}

require_once '../layout/student/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar omitted for brevity -->

        <div class="col-md-9 col-lg-10 p-4  offset-md-3 offset-lg-2 p-4">
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

                            <?php if (!empty($_SESSION['error'])): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($_SESSION['success'])): ?>
                                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
                            <?php endif; ?>

                            <?php if (!$is_weekday): ?>
                                <div class="alert alert-info">Weekend: Attendance not required.</div>
                                <a href="index.php" class="btn btn-outline-secondary mt-3">Back to Dashboard</a>

                            <?php elseif (!$attendance_open && !$current_status): ?>
                                <div class="alert alert-warning">Attendance not opened or closed by admin.</div>
                                <a href="index.php" class="btn btn-outline-secondary mt-3">Back to Dashboard</a>

                            <?php elseif ($current_status === 'Present'): ?>
                                <i class="bi bi-check-circle-fill text-success mb-3" style="font-size:4rem;"></i>
                                <div class="alert alert-success">
                                    Attendance confirmed! Grade: <strong><?= $grade ?>%</strong><br>
                                    Checked in at: <?= $check_in_time ? date('h:i A', strtotime($check_in_time)) : '--:--' ?>
                                </div>

                                <?php if ($can_checkout): ?>
                                    <h4>Checkout</h4>
                                    <form action="checkout_process.php" method="post">
                                        <button type="submit" name="confirm_checkout" class="btn btn-warning btn-lg">
                                            Check Out
                                        </button>
                                    </form>
                                <?php elseif ($checkout_time): ?>
                                    <div class="alert alert-info mt-2">
                                        You have checked out at <?= date('h:i A', strtotime($checkout_time)) ?>
                                    </div>
                                <?php endif; ?>

                                <a href="index.php" class="btn btn-primary mt-3">Go to Dashboard</a>

                            <?php else: ?>
                                <i class="bi bi-calendar2-check text-primary mb-3" style="font-size:4rem;"></i>
                                <div class="alert alert-danger mb-3">Your current status is <strong>Absent</strong>.</div>

                                <?php if ($remaining_minutes <= 0): ?>
                                    <div class="alert alert-danger">Attendance window has closed. Grade: 0%</div>
                                <?php else: ?>
                                    <h4>Confirm Attendance</h4>
                                    <p class="small text-muted mb-3">
                                        Time remaining to mark attendance: <?= $remaining_minutes ?> min
                                    </p>
                                    <form action="mark_attendance_process.php" method="post" class="text-start">
                                        <div class="mb-3">
                                            <label class="form-label">Authorization Code</label>
                                            <input type="text" name="auth_code" class="form-control form-control-lg" placeholder="Enter 6-digit code" maxlength="6" pattern="\d{6}" required>
                                            <div class="form-text small">Enter the 6-digit code provided for you today.</div>
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" name="confirm_attendance" class="btn btn-primary btn-lg">
                                                Mark as Present
                                            </button>
                                        </div>
                                    </form>
                                <?php endif; ?>

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
