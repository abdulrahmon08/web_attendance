<?php


$page_title = "Mark Attendance";
require_once '../include/config.php';
require_once '../layout/student/header.php';


$student_id = $_SESSION['student_id'];
$today = date('Y-m-d');

// ============================
// CHECK IF ALREADY MARKED TODAY
// ============================
$stmt = $conn->prepare("
    SELECT id 
    FROM attendance 
    WHERE student_id = ? AND attendance_date = ?
");
$stmt->execute([$student_id, $today]);
$alreadyMarked = $stmt->rowCount() > 0;
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
            </nav>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-md-9 col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Mark Attendance</h2>
                <span class="text-muted">
                    <?= date('l, F j, Y') ?>
                </span>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">

                    <?php if ($alreadyMarked): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-circle"></i>
                            You have already marked attendance today.
                        </div>

                        <a href="attendance.php" class="btn btn-secondary">
                            Back to Attendance
                        </a>

                    <?php else: ?>
                        <h4 class="mb-3">Confirm Attendance</h4>
                        <p class="text-muted">
                            Click the button below to mark your attendance for today.
                        </p>

                        <form action="mark_attendance_process.php" method="post">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Confirm Attendance
                            </button>
                        </form>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>

<?php
require_once '../layout/student/footer.php';
?>
