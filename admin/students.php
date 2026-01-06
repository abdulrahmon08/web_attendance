<?php
$page_title = "Students Management";
require_once '../include/config.php';

// ============================
// HANDLE ADD STUDENT
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {

    $student_id   = trim($_POST['student_id']);
    $name         = trim($_POST['name']);
    $email        = trim($_POST['email']);
    $password     = trim($_POST['password']); // plain as requested
    $school_name  = trim($_POST['school_name']);
    $gender       = trim($_POST['gender']);
    $phone        = trim($_POST['phone']);
    $date_joined  = trim($_POST['date_joined']);

    if (empty($student_id) || empty($name) || empty($email) || empty($password) || empty($school_name) || empty($gender) || empty($phone) || empty($date_joined)) {
        $_SESSION['error'] = "All fields are required.";
    } else {
        $check = $conn->prepare("SELECT id FROM students WHERE email_address = ? OR student_id = ?");
        $check->execute([$email, $student_id]);
        if ($check->rowCount() > 0) {
            $_SESSION['error'] = "Student with this email or ID already exists.";
        } else {
            $stmt = $conn->prepare("INSERT INTO students (student_id, name, email_address, password, school_name, gender, phone_number, date_joined) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$student_id, $name, $email, $password, $school_name, $gender, $phone, $date_joined]);
            $_SESSION['success'] = "Student added successfully.";
        }
    }

    header("Location: students.php");
    exit;
}

// ============================
// HANDLE EDIT STUDENT
// ============================
if (isset($_POST['edit_student'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $school_name = $_POST['school_name'];
    $gender = $_POST['gender'];
    $phone = $_POST['phone'];
    $date_joined = $_POST['date_joined'];

    $stmt = $conn->prepare("UPDATE students SET name=?, school_name=?, gender=?, phone_number=?, date_joined=? WHERE id=?");
    $stmt->execute([$name, $school_name, $gender, $phone, $date_joined, $id]);

    $_SESSION['success'] = "Student updated successfully.";
    header("Location: students.php");
    exit;
}

// ============================
// HANDLE RESET PASSWORD
// ============================
if (isset($_POST['reset_password'])) {
    $id = $_POST['id'];
    $new_password = $_POST['new_password'];

    $stmt = $conn->prepare("UPDATE students SET password=? WHERE id=?");
    $stmt->execute([$new_password, $id]);

    $_SESSION['success'] = "Password reset successfully.";
    header("Location: students.php");
    exit;
}

// ============================
// HANDLE DELETE STUDENT
// ============================
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // 1. Delete all attendance records for this student
    $stmt = $conn->prepare("DELETE FROM attendance WHERE student_id = ?");
    $stmt->execute([$id]);

    // 2. Delete the student
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['success'] = "Student and their attendance records deleted successfully.";
    header("Location: students.php");
    exit;
}

// ============================
// FETCH STUDENTS
// ============================
$students = $conn->query("SELECT * FROM students ORDER BY date_joined DESC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch today's auth codes (if any) to display beside students
$today = date('Y-m-d');
$codeMap = [];
try {
    $codes = $conn->prepare("SELECT student_id, auth_code, COALESCE(auth_used,0) AS auth_used FROM attendance WHERE attendance_date = ?");
    $codes->execute([$today]);
    $rows = $codes->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $codeMap[$r['student_id']] = $r;
    }
} catch (Exception $e) {
    // if columns don't exist or query fails, leave map empty
}

require_once '../layout/admin/header.php';
?>

        <!-- MAIN CONTENT -->
        <div class="col-md-9 col-lg-10 mt-4 offset-md-3 offset-lg-2 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Students</h4>
                <div>
                    <a href="students_print.php" target="_blank" class="btn btn-outline-secondary me-2">Print / PDF</a>
                    <a href="students_export.php" class="btn btn-outline-success me-2">Download Excel</a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Student
                    </button>
                </div>
            </div>

            <!-- ALERTS -->
            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <!-- STUDENTS TABLE -->
            <div class="card border-0 shadow-sm">
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Student ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>School</th>
                                <th>Gender</th>
                                <th>Phone</th>
                                <th>Date Joined</th>
                                <th>Auth Code</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($students)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No students found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($students as $s): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= htmlspecialchars($s['student_id']) ?></td>
                                        <td><?= htmlspecialchars($s['name']) ?></td>
                                        <td><?= htmlspecialchars($s['email_address']) ?></td>
                                        <td><?= htmlspecialchars($s['school_name']) ?></td>
                                        <td><?= htmlspecialchars($s['gender']) ?></td>
                                        <td><?= htmlspecialchars($s['phone_number']) ?></td>
                                        <td><?= date('M j, Y', strtotime($s['date_joined'])) ?></td>
                                        <?php $c = $codeMap[$s['id']] ?? null; ?>
                                        <td class="text-center">
                                            <?php if ($c && !empty($c['auth_code'])): ?>
                                                <?= htmlspecialchars($c['auth_code']) ?>
                                                <?php if ($c['auth_used']): ?>
                                                    <span class="badge bg-danger ms-2">Used</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success ms-2">Unused</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editModal<?= $s['id'] ?>">Edit</button>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#resetModal<?= $s['id'] ?>">Reset Password</button>
                                            <a href="students.php?delete=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this student?')">Delete</a>
                                        </td>
                                    </tr>

                                    <!-- EDIT MODAL -->
                                    <div class="modal fade" id="editModal<?= $s['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="post">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Student</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                                        <div class="mb-2">
                                                            <label class="form-label">Full Name</label>
                                                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($s['name']) ?>" required>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label">School Name</label>
                                                            <input type="text" class="form-control" name="school_name" value="<?= htmlspecialchars($s['school_name']) ?>" required>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label">Gender</label>
                                                            <select name="gender" class="form-select" required>
                                                                <option value="Male" <?= $s['gender']=='Male'?'selected':'' ?>>Male</option>
                                                                <option value="Female" <?= $s['gender']=='Female'?'selected':'' ?>>Female</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label">Phone</label>
                                                            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($s['phone_number']) ?>" required>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label">Date Joined</label>
                                                            <input type="text" class="form-control" name="date_joined" value="<?= htmlspecialchars($s['date_joined']) ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" name="edit_student" class="btn btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- RESET PASSWORD MODAL -->
                                    <div class="modal fade" id="resetModal<?= $s['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="post">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Reset Password</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                                        <div class="mb-2">
                                                            <label class="form-label">New Password</label>
                                                            <input type="text" class="form-control" name="new_password" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" name="reset_password" class="btn btn-warning">Reset Password</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ADD STUDENT MODAL -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6"><label class="form-label">Student ID</label><input type="text" class="form-control" name="student_id" required></div>
                    <div class="col-md-6"><label class="form-label">Full Name</label><input type="text" class="form-control" name="name" required></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" required></div>
                    <div class="col-md-6"><label class="form-label">Password</label><input type="text" class="form-control" name="password" required></div>
                    <div class="col-md-6"><label class="form-label">School Name</label><input type="text" class="form-control" name="school_name" required></div>
                    <div class="col-md-3"><label class="form-label">Gender</label>
                        <select class="form-select" name="gender" required><option value="">Select</option><option value="Male">Male</option><option value="Female">Female</option></select>
                    </div>
                    <div class="col-md-3"><label class="form-label">Phone</label><input type="text" class="form-control" name="phone" required></div>
                    <div class="col-md-6"><label class="form-label">Date Joined</label><input type="date" class="form-control" name="date_joined" required></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_student" class="btn btn-primary">Add Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../layout/admin/footer.php'; ?>
