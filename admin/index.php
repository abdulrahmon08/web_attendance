<?php
$page_title = "Admin Dashboard";
require_once '../include/config.php';

$stmt = $conn->query("SELECT COUNT(*) FROM students");
$total_students = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) FROM attendance_dates");
$total_days = $stmt->fetchColumn();


require_once '../layout/admin/header.php';
?>

        <!-- ================= MAIN CONTENT ================= -->
        <div class="col-md-9 col-lg-10 p-4 offset-md-3 offset-lg-2 p-4 mt-4">

            <h3 class="mb-4">Admin Dashboard</h3>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted small text-uppercase fw-bold">Total Students</h6>
                                <h3 class="mb-0"><?= $total_students ?></h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="bi bi-people text-primary fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted small text-uppercase fw-bold">
                                    Attendance Days Hosted
                                </h6>
                                <h3 class="mb-0"><?= $total_days ?></h3>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="bi bi-calendar-check text-success fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QUICK ACTION BUTTONS -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Quick Actions</h5>

                    <a href="attendance_dates.php" class="btn btn-primary me-2 mb-2">
                        <i class="bi bi-calendar-plus me-1"></i> Host Attendance
                    </a>

                    <a href="attendance_view.php" class="btn btn-outline-secondary me-2 mb-2">
                        <i class="bi bi-eye me-1"></i> View Attendance
                    </a>

                    <a href="students.php" class="btn btn-outline-info mb-2">
                        <i class="bi bi-people me-1"></i> View Students
                    </a>
                </div>
            </div>

        </div>
        <!-- =============== END MAIN CONTENT =============== -->

    </div>
</div>

<?php require_once '../layout/admin/footer.php'; ?>
