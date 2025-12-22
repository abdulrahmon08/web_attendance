<?php
$page_title = "My Profile";
require_once '../include/config.php';

// 1. Security Check: Redirect to login if session is not active
if (!isset($_SESSION['student_id'])) {
    header("Location: ../index.php"); // Redirect to login, not index
    exit;
}

$student_id = $_SESSION['student_id'];

// 2. Fetch Student Data
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// 3. Fetch Total Presences
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM attendance WHERE student_id = ? AND status = 'Present'");
$stmt->execute([$student_id]);
$attendance_count = $stmt->fetchColumn();

require_once '../layout/student/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Navigation (Matching your Dashboard/Mark Attendance) -->
        <div class="col-md-3 col-lg-2 sidebar p-3 bg-light min-vh-100">
            <div class="mb-4 ps-3">
                <h5 class="text-primary fw-bold">SIWES Portal</h5>
            </div>
            <nav class="nav flex-column">
                <a class="nav-link mb-2" href="index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                <a class="nav-link mb-2" href="mark_attendance.php"><i class="bi bi-calendar-check me-2"></i> Mark Attendance</a>
                <a class="nav-link active mb-2" href="profile.php"><i class="bi bi-person-badge me-2"></i> Profile</a>
                <hr>
                <a class="nav-link text-danger" href="../logout.php" onclick="return confirm('Are you sure you want to logout?')">
                    <i class="bi bi-box-arrow-left me-2"></i> Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">My Profile</h2>
            </div>

            <div class="row">
                <!-- Profile Header Card -->
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm text-center p-4">
                        <div class="mb-3 text-center d-flex justify-content-center">
                            <!-- Safe Avatar: Takes first letter of name -->
                            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center text-white" style="width: 100px; height: 100px; font-size: 3rem;">
                                <?= strtoupper(substr($student['name'] ?? 'U', 0, 1)) ?>
                            </div>
                        </div>
                        <h4 class="mb-1"><?= htmlspecialchars($student['name']) ?></h4>
                        <!-- Using student_id as the student ID -->
                        <p class="text-muted mb-3"><?= htmlspecialchars($student['student_id'] ?? 'Student ID Not Set') ?></p>
                        
                        <div class="d-flex justify-content-around border-top pt-3">
                            <div>
                                <h6 class="mb-0"><?= $attendance_count ?></h6>
                                <small class="text-muted">Presences</small>
                            </div>
                            <div>
                                <!-- FIXED DATE ERROR HERE -->
                                <h6 class="mb-0"><?= date('Y', strtotime($student['date_joined'])) ?></h6>
                                <small class="text-muted">Enrolled</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal Details Card -->
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-info-circle me-2"></i>Personal Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Full Name:</div>
                                <div class="col-sm-8 text-secondary"><?= htmlspecialchars($student['name']) ?></div>
                            </div>
                            <hr class="text-muted opacity-25">
                            
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Student ID:</div>
                                <div class="col-sm-8 text-secondary"><?= htmlspecialchars($student['student_id'] ?? 'N/A') ?></div>
                            </div>
                            <hr class="text-muted opacity-25">

                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Email Address:</div>
                                <div class="col-sm-8 text-secondary"><?= htmlspecialchars($student['email_address']) ?></div>
                            </div>
                            <hr class="text-muted opacity-25">

                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">Phone Number:</div>
                                <div class="col-sm-8 text-secondary"><?= htmlspecialchars($student['phone_number'] ?? 'Not set') ?></div>
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
                                <div class="col-sm-8 text-secondary">
                                    <!-- FIXED DATE ERROR HERE -->
                                    <?= date('F j, Y', strtotime($student['date_joined'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../layout/student/footer.php'; ?>