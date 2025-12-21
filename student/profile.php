<?php
$page_title = "My Profile";
require_once '../include/config.php';

// Security check
if (!isset($_SESSION['student_id'])) {
    header("Location: ../index.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// 1. FETCH FULL STUDENT DETAILS
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. FETCH ATTENDANCE SUMMARY (Optional, for the "Stats" section)
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM attendance WHERE student_id = ? AND status = 'Present'");
$stmt->execute([$student_id]);
$attendance_count = $stmt->fetchColumn();

require_once '../layout/student/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar p-3 bg-light min-vh-100">
            <nav class="nav flex-column">
                <a class="nav-link" href="index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                <a class="nav-link active" href="profile.php"><i class="bi bi-person-badge me-2"></i> Profile</a>
                <a class="nav-link text-danger mt-4" href="../logout.php"><i class="bi bi-box-arrow-left me-2"></i> Logout</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Student Profile</h2>
            </div>

            <div class="row">
                <!-- Profile Header Card -->
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm text-center p-4">
                        <div class="mb-3">
                            <!-- Placeholder Profile Picture -->
                            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center text-white" style="width: 100px; height: 100px; font-size: 3rem;">
                                <?= strtoupper(substr($student['name'], 0, 1)) ?>
                            </div>
                        </div>
                        <h4 class="mb-1"><?= htmlspecialchars($student['name']) ?></h4>
                        <p class="text-muted mb-3"><?= htmlspecialchars($student['matric_no'] ?? 'Matric Not Set') ?></p>
                        <div class="d-flex justify-content-around border-top pt-3">
                            <div>
                                <h6 class="mb-0"><?= $attendance_count ?></h6>
                                <small class="text-muted">Presences</small>
                            </div>
                            <div>
                                <h6 class="mb-0"><?= date('Y', strtotime($student['date_joined'])) ?></h6>
                                <small class="text-muted">Enrolled</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Details List Card -->
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 text-primary"><i class="bi bi-info-circle me-2"></i>Personal Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Full Name:</div>
                                <div class="col-sm-8 text-secondary"><?= htmlspecialchars($student['name']) ?></div>
                            </div>
                            <hr class="text-muted opacity-25">
                            
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Matric Number:</div>
                                <div class="col-sm-8 text-secondary"><?= htmlspecialchars($student['matric_no'] ?? 'N/A') ?></div>
                            </div>
                            <hr class="text-muted opacity-25">

                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Email Address:</div>
                                <div class="col-sm-8 text-secondary"><?= htmlspecialchars($student['email_address']) ?></div>
                            </div>
                            <hr class="text-muted opacity-25">

                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Phone Number:</div>
                                <div class="col-sm-8 text-secondary"><?= htmlspecialchars($student['phone'] ?? 'Not set') ?></div>
                            </div>
                            <hr class="text-muted opacity-25">

                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Gender:</div>
                                <div class="col-sm-8 text-secondary"><?= htmlspecialchars($student['gender'] ?? 'N/A') ?></div>
                            </div>
                            <hr class="text-muted opacity-25">

                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Department:</div>
                                <div class="col-sm-8 text-secondary"><?= htmlspecialchars($student['department'] ?? 'General') ?></div>
                            </div>
                            <hr class="text-muted opacity-25">

                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Account Created:</div>
                                <div class="col-sm-8 text-secondary"><?= date('F j, Y', strtotime($student['date_joined'])) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../layout/student/footer.php'; ?>