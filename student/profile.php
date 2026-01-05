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


$message = "";

if (isset($_POST['reset'])) {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Example: user ID stored in session
    $student_id = $_SESSION['student_id'] ?? null;

    if (!$student_id) {
        $message = '<div class="alert alert-danger">Unauthorized request.</div>';
    } elseif ($new_password !== $confirm_password) {
        $message = '<div class="alert alert-danger">Passwords do not match.</div>';
    } elseif (strlen($new_password) < 4) {
        $message = '<div class="alert alert-danger">Password must be at least 4 characters.</div>';
    } elseif (strlen($new_password) > 8) {
        $message = '<div class="alert alert-danger">Password must not greater than 8 characters.</div>';
    } else {
        // Store password as plain text (NOT recommended)
        $stmt = $conn->prepare("UPDATE students SET password = ? WHERE id = ?");

        if ($stmt->execute([$new_password, $student_id])) {
            $message = '<div class="alert alert-success">Password reset successful.</div>';
        } else {
            $message = '<div class="alert alert-danger">Failed to reset password.</div>';
        }
    }
}

require_once '../layout/student/header.php';
?>

<div class="container-fluid">
    <div class="row">

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-4  offset-md-3 offset-lg-2 p-4">
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
                            <div>
                                <button class="btn btn-outline-primary btn-sm mt-4" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                                    Reset your Password
                                </button>
                                <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="resetPasswordModalLabel">Reset your Password</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <h3 class="mt-4">Create New Password</h3>
                                                <p class="mb-4">Enter a strong password and confirm it to reset your account.</p>

                                                <form method="POST">

                                                    <div class="mb-3 text-start">
                                                        <label class="form-label">New Password</label>
                                                        <input type="password" name="new_password" class="form-control" placeholder="Enter new password" required>
                                                    </div>

                                                    <div class="mb-3 text-start">
                                                        <label class="form-label">Confirm Password</label>
                                                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required>
                                                    </div>

                                                    <button type="submit" name="reset" class="btn btn-primary w-100 py-2">Reset Password</button>

                                                    <?= $message ?? "" ?>
                                                </form>

                                                <div class="mt-4">
                                                    <p><a href="index.php"><i class="bi bi-arrow-left"></i> Back to Dashboard</a></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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