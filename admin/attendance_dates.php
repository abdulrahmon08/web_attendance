<?php
require_once '../include/config.php';
date_default_timezone_set('Africa/Lagos');

// ============================
// OPEN NEW ATTENDANCE DATE
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance_date'])) {

    $date = $_POST['attendance_date'];
    $open_time = date('H:i:s');

    // Insert attendance date with open time
    $stmt = $conn->prepare("
        INSERT INTO attendance_dates (attendance_date, opened_at, status)
        VALUES (?, ?, 'Open')
        ON DUPLICATE KEY UPDATE status='Open'
    ");
    $stmt->execute([$date, $open_time]);

    // Insert default ABSENT for all students
    $students = $conn->query("SELECT id FROM students")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($students as $student) {
        $insert = $conn->prepare("
            INSERT IGNORE INTO attendance (student_id, attendance_date, status, grade)
            VALUES (?, ?, 'Absent', 0)
        ");
        $insert->execute([$student['id'], $date]);
    }

    $_SESSION['success'] = "Attendance opened successfully at " . date('h:i A');
    header("Location: attendance_dates.php");
    exit;
}

// ============================
// REMOVE ATTENDANCE DATE
// ============================
if (isset($_GET['delete'])) {

    $date = $_GET['delete'];

    // Delete attendance records first (FK safe)
    $stmt = $conn->prepare("DELETE FROM attendance WHERE attendance_date = ?");
    $stmt->execute([$date]);

    // Delete attendance date
    $stmt = $conn->prepare("DELETE FROM attendance_dates WHERE attendance_date = ?");
    $stmt->execute([$date]);

    $_SESSION['success'] = "Attendance date removed successfully.";
    header("Location: attendance_dates.php");
    exit;
}

// ============================
// FETCH ALL ATTENDANCE DATES
// ============================
$dates = $conn->query("
    SELECT attendance_date, opened_at, status
    FROM attendance_dates
    ORDER BY attendance_date DESC
")->fetchAll(PDO::FETCH_ASSOC);

require_once '../layout/admin/header.php';
?>

<div class="container-fluid p-4">

    <h4 class="mb-4 fw-bold">Host Attendance</h4>

    <!-- SUCCESS MESSAGE -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <!-- OPEN ATTENDANCE -->
    <form method="post" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="date" name="attendance_date" class="form-control" required>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary">
                <i class="bi bi-calendar-plus me-1"></i>
                Open Attendance
            </button>
        </div>
    </form>

    <!-- ATTENDANCE TABLE -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Time Opened</th>
                        <th>Status</th>
                        <th>Grading Window</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>

                <?php if (empty($dates)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            No attendance opened yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($dates as $d): ?>
                        <tr>
                            <td class="fw-bold"><?= $d['attendance_date'] ?></td>
                            <td><?= date('h:i A', strtotime($d['opened_at'])) ?></td>
                            <td>
                                <span class="badge bg-<?= $d['status'] === 'Open' ? 'success' : 'secondary' ?>">
                                    <?= $d['status'] ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    0–15m: 100% |
                                    16–30m: 75% |
                                    31–45m: 50% |
                                    46–60m: 25%
                                </small>
                            </td>
                            <td>
                                <a href="?delete=<?= $d['attendance_date'] ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Remove this attendance date?')">
                                    <i class="bi bi-trash"></i> Remove
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../layout/admin/footer.php'; ?>
