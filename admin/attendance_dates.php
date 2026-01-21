<?php
require_once '../include/config.php';
date_default_timezone_set('Africa/Lagos');

// ============================
// OPEN NEW ATTENDANCE DATE
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance_date'])) {

    $date = $_POST['attendance_date'];
    $now  = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("
        INSERT INTO attendance_dates (attendance_date, opened_at, status)
        VALUES (?, ?, 'Open')
        ON DUPLICATE KEY UPDATE status='Open', opened_at=VALUES(opened_at)
    ");
    $stmt->execute([$date, $now]);

    $students = $conn->query("SELECT id FROM students")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($students as $student) {
        $insert = $conn->prepare("
            INSERT IGNORE INTO attendance (student_id, attendance_date, status, grade)
            VALUES (?, ?, 'Absent', 0)
        ");
        $insert->execute([$student['id'], $date]);
    }

    try {
        $conn->exec("ALTER TABLE attendance ADD COLUMN IF NOT EXISTS auth_code VARCHAR(6) DEFAULT NULL");
        $conn->exec("ALTER TABLE attendance ADD COLUMN IF NOT EXISTS auth_used TINYINT(1) DEFAULT 0");
    } catch (Exception $e) {}

    $updateCode = $conn->prepare("
        UPDATE attendance 
        SET auth_code = ?, auth_used = 0 
        WHERE student_id = ? AND attendance_date = ?
    ");

    foreach ($students as $student) {
        do {
            $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $check = $conn->prepare("
                SELECT COUNT(*) FROM attendance
                WHERE attendance_date = ? AND auth_code = ?
            ");
            $check->execute([$date, $code]);
        } while ($check->fetchColumn() > 0);

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

    $stmt = $conn->prepare("DELETE FROM attendance WHERE attendance_date = ?");
    $stmt->execute([$date]);

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
    SELECT attendance_date, opened_at,
           TIMESTAMPADD(HOUR,1,opened_at) AS close_time
    FROM attendance_dates
    ORDER BY attendance_date DESC
")->fetchAll(PDO::FETCH_ASSOC);

// ============================
// SYNC DB STATUS (DATE + TIME AWARE)
// ============================
$today = date('Y-m-d');

foreach ($dates as $row) {
    $isToday = ($row['attendance_date'] === $today);
    $isOpen = (
        $isToday &&
        strtotime($row['opened_at']) <= time() &&
        strtotime($row['close_time']) > time()
    );
    $newStatus = $isOpen ? 'Open' : 'Closed';
    $sync = $conn->prepare("UPDATE attendance_dates SET status = ? WHERE attendance_date = ?");
    $sync->execute([$newStatus, $row['attendance_date']]);
}

// ============================
// CALCULATE SUMMARY STATS
// ============================
$summary = ['total'=>0, 'present'=>0, 'absent'=>0];
foreach ($dates as $d) {
    $stmtSummary = $conn->prepare("
        SELECT
            COUNT(*) AS total,
            SUM(status='Present') AS present,
            SUM(status='Absent') AS absent
        FROM attendance
        WHERE attendance_date = ?
    ");
    $stmtSummary->execute([$d['attendance_date']]);
    $s = $stmtSummary->fetch(PDO::FETCH_ASSOC);
    $summary[$d['attendance_date']] = $s;
}

require_once '../layout/admin/header.php';
?>

<div class="container offset-md-2 offset-lg-2 p-4 mt-4">

    <h4 class="mb-4 fw-bold">Host Attendance</h4>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

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

    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-sm align-middle mb-0 text-nowrap w-100">
                <thead class="table-light small">
                    <tr>
                        <th>Date</th>
                        <th>Time Opened</th>
                        <th>Status</th>
                        <th>Closes At</th>
                        <th>Summary (P/A/T)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php if (empty($dates)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-2 text-muted">
                                No attendance opened yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($dates as $d): ?>
                            <?php
                                $isToday = ($d['attendance_date'] === $today);
                                $isOpen = (
                                    $isToday &&
                                    strtotime($d['opened_at']) <= time() &&
                                    strtotime($d['close_time']) > time()
                                );
                                $displayStatus = $isOpen ? 'Open' : 'Closed';
                                $s = $summary[$d['attendance_date']] ?? ['total'=>0,'present'=>0,'absent'=>0];
                            ?>
                            <tr>
                                <td class="fw-bold"><?= $d['attendance_date'] ?></td>
                                <td><?= date('h:i A', strtotime($d['opened_at'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $displayStatus==='Open'?'success':'secondary' ?>">
                                        <?= $displayStatus ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted"><?= date('h:i A', strtotime($d['close_time'])) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-success">P: <?= $s['present'] ?></span>
                                    <span class="badge bg-danger">A: <?= $s['absent'] ?></span>
                                    <span class="badge bg-secondary">T: <?= $s['total'] ?></span>
                                </td>
                                <td>
                                    <?php if ($displayStatus === 'Open'): ?>
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="bi bi-lock"></i> Locked
                                        </button>
                                    <?php else: ?>
                                        <a href="?delete=<?= $d['attendance_date'] ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Remove this attendance date?')">
                                            <i class="bi bi-trash"></i> Remove
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- LIVE COUNTDOWN SCRIPT -->
<script>
document.querySelectorAll('tr').forEach(row => {
    const statusBadge = row.querySelector('span.badge');
    if (statusBadge && statusBadge.textContent === 'Open') {
        const closeTimeEl = row.querySelector('td small');
        if (!closeTimeEl) return;
        const closeTime = new Date(closeTimeEl.textContent).getTime();
        const countdownEl = document.createElement('div');
        countdownEl.className = 'text-muted small';
        row.querySelector('td:nth-child(3)').appendChild(countdownEl);

        const interval = setInterval(() => {
            const now = new Date().getTime();
            const diff = closeTime - now;
            if(diff<=0){
                countdownEl.textContent = 'Attendance Closed';
                statusBadge.textContent = 'Closed';
                statusBadge.className = 'badge bg-secondary';
                clearInterval(interval);
                return;
            }
            const mins = Math.floor(diff/60000);
            const secs = Math.floor((diff%60000)/1000);
            countdownEl.textContent = `Closes in ${mins}m ${secs}s`;
        },1000);
    }
});
</script>
