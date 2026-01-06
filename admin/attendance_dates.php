<?php
require_once '../include/config.php';
date_default_timezone_set('Africa/Lagos');

// ============================
// OPEN NEW ATTENDANCE DATE
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance_date'])) {

    $date = $_POST['attendance_date'];
    $now = date('Y-m-d H:i:s'); // timestamp when admin opens attendance

    // Insert attendance date with open timestamp
    $stmt = $conn->prepare("
        INSERT INTO attendance_dates (attendance_date, opened_at, status)
        VALUES (?, ?, 'Open')
        ON DUPLICATE KEY UPDATE status='Open', opened_at=VALUES(opened_at)
    ");
    $stmt->execute([$date, $now]);

    // Insert default 'Absent' for all students
    $students = $conn->query("SELECT id FROM students")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($students as $student) {
        $insert = $conn->prepare("
            INSERT IGNORE INTO attendance (student_id, attendance_date, status, grade)
            VALUES (?, ?, 'Absent', 0)
        ");
        $insert->execute([$student['id'], $date]);
    }

    // Ensure auth columns exist (MySQL 8+ supports IF NOT EXISTS)
    try {
        $conn->exec("ALTER TABLE attendance ADD COLUMN IF NOT EXISTS auth_code VARCHAR(6) DEFAULT NULL");
        $conn->exec("ALTER TABLE attendance ADD COLUMN IF NOT EXISTS auth_used TINYINT(1) DEFAULT 0");
    } catch (Exception $e) {
        // ignore if ALTER TABLE not supported; attempt safe fallback
    }

    // Generate a unique 6-digit authorization code for each student for this attendance date
    $updateCode = $conn->prepare("UPDATE attendance SET auth_code = ?, auth_used = 0 WHERE student_id = ? AND attendance_date = ?");
    foreach ($students as $student) {
        // generate code and ensure it's unique for this date
        do {
            $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $check = $conn->prepare("SELECT COUNT(*) FROM attendance WHERE attendance_date = ? AND auth_code = ?");
            $check->execute([$date, $code]);
            $exists = $check->fetchColumn();
        } while ($exists > 0);

        $updateCode->execute([$code, $student['id'], $date]);
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

    // Delete attendance records first
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
    SELECT attendance_date, opened_at, status,
           TIMESTAMPADD(HOUR,1,opened_at) AS close_time
    FROM attendance_dates
    ORDER BY attendance_date DESC
")->fetchAll(PDO::FETCH_ASSOC);

require_once '../layout/admin/header.php';
?>

<div class="container offset-md-2 offset-lg-2 p-4 mt-4">

    <h4 class="mb-4 fw-bold">Host Attendance</h4>

    <!-- SUCCESS MESSAGE -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success'];
            unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <!-- OPEN ATTENDANCE FORM -->
    <form method="post" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="date" name="attendance_date" class="form-control" required>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary">
                <i class="bi bi-calendar-plus me-1"></i> Open Attendance
            </button>
        </div>
    </form>
    <small class="text-muted">
        Grading Window: 0–15m: 100% | 16–30m: 75% | 31–45m: 50% | 46–60m: 25%
    </small>
    <!-- ATTENDANCE TABLE -->
    <!-- ATTENDANCE TABLE -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-sm align-middle mb-0 text-nowrap w-100">
                <thead class="table-light small">
                    <tr>
                        <th>Date</th>
                        <th>Time Opened</th>
                        <th>Status</th>
                        <th>Closes At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php if (empty($dates)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-2 text-muted">
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
                                    <small class="text-muted"><?= date('h:i A', strtotime($d['close_time'])) ?></small>
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

