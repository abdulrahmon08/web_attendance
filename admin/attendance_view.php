<?php
require_once '../include/config.php';
date_default_timezone_set('Africa/Lagos');

$date = $_GET['date'] ?? null;

// ============================
// FETCH ALL HOSTED DATES
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

    $sync = $conn->prepare("
        UPDATE attendance_dates SET status = ? WHERE attendance_date = ?
    ");
    $sync->execute([$newStatus, $row['attendance_date']]);
}

// ============================
// FETCH SELECTED DATE INFO
// ============================
$dateInfo = null;
$summary = ['present' => 0, 'absent' => 0, 'total' => 0];

if ($date) {
    $stmtInfo = $conn->prepare("
        SELECT attendance_date, opened_at,
               TIMESTAMPADD(HOUR,1,opened_at) AS close_time
        FROM attendance_dates
        WHERE attendance_date = ?
        LIMIT 1
    ");
    $stmtInfo->execute([$date]);
    $dateInfo = $stmtInfo->fetch(PDO::FETCH_ASSOC);

    // Summary stats
    $summaryStmt = $conn->prepare("
        SELECT
            COUNT(*) AS total,
            SUM(status = 'Present') AS present,
            SUM(status = 'Absent') AS absent
        FROM attendance
        WHERE attendance_date = ?
    ");
    $summaryStmt->execute([$date]);
    $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);
}

// ============================
// FETCH ATTENDANCE RECORDS
// ============================
$records = [];
if ($date) {
    $stmt = $conn->prepare("
        SELECT s.name, a.status, a.check_in_time, a.checkout_time
        FROM attendance a
        JOIN students s ON a.student_id = s.id
        WHERE a.attendance_date = ?
        ORDER BY s.name
    ");
    $stmt->execute([$date]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================
// FETCH DATA FOR GRAPHS
// ============================
// Last 7 days attendance summary
$graphData = $conn->query("
    SELECT attendance_date,
           SUM(status = 'Present') AS present,
           SUM(status = 'Absent') AS absent
    FROM attendance
    WHERE attendance_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY attendance_date
    ORDER BY attendance_date
")->fetchAll(PDO::FETCH_ASSOC);

$graph_labels = [];
$graph_present = [];
$graph_absent = [];

foreach ($graphData as $g) {
    $graph_labels[] = $g['attendance_date'];
    $graph_present[] = (int)$g['present'];
    $graph_absent[] = (int)$g['absent'];
}

require_once '../layout/admin/header.php';
?>

<div class="col-md-9 col-lg-10 offset-md-3 offset-lg-2 p-4 mt-4">

    <h4 class="mb-4">Attendance Records</h4>

    <!-- HOSTED DATES -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">Select Attendance Date</div>
        <div class="card-body">
            <?php foreach ($dates as $d): ?>
                <?php
                    $isToday = ($d['attendance_date'] === $today);
                    $isOpen = (
                        $isToday &&
                        strtotime($d['opened_at']) <= time() &&
                        strtotime($d['close_time']) > time()
                    );
                    $displayStatus = $isOpen ? 'Open' : 'Closed';
                ?>
                <a href="?date=<?= $d['attendance_date'] ?>"
                   class="btn btn-outline-primary btn-sm me-2 mb-2">
                    <?= $d['attendance_date'] ?>
                    <span class="badge ms-2 bg-<?= $displayStatus === 'Open' ? 'success' : 'secondary' ?>">
                        <?= $displayStatus ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($date && $dateInfo): ?>
        <?php
            $isToday = ($dateInfo['attendance_date'] === $today);
            $isOpen = (
                $isToday &&
                strtotime($dateInfo['opened_at']) <= time() &&
                strtotime($dateInfo['close_time']) > time()
            );
            $status = $isOpen ? 'Open' : 'Closed';
        ?>

        <!-- SUMMARY + COUNTDOWN -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <h6>Total</h6>
                        <h4><?= $summary['total'] ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body text-success">
                        <h6>Present</h6>
                        <h4><?= $summary['present'] ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body text-danger">
                        <h6>Absent</h6>
                        <h4><?= $summary['absent'] ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <h6>Status</h6>
                        <span id="countdown"
                              data-close="<?= strtotime($dateInfo['close_time']) ?>"
                              class="badge bg-<?= $status === 'Open' ? 'success' : 'secondary' ?>">
                            <?= $status ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ATTENDANCE TABLE -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Student Name</th>
                            <th>Status</th>
                            <th>Check-in</th>
                            <th>Checkout</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $r): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?= htmlspecialchars($r['name']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $r['status'] === 'Present' ? 'success' : 'danger' ?>">
                                        <?= $r['status'] ?>
                                    </span>
                                </td>
                                <td><?= $r['check_in_time'] ? date('h:i A', strtotime($r['check_in_time'])) : '--' ?></td>
                                <td><?= $r['checkout_time'] ? date('h:i A', strtotime($r['checkout_time'])) : '--' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- GRAPHS (DAILY TRENDS) -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">📈 Daily Attendance Trends (Last 7 Days)</div>
            <div class="card-body">
                <canvas id="attendanceChart" height="120"></canvas>
            </div>
        </div>

    <?php endif; ?>
</div>

<!-- LIVE COUNTDOWN SCRIPT -->
<script>
const countdown = document.getElementById('countdown');
if (countdown && countdown.dataset.close) {
    const closeTime = parseInt(countdown.dataset.close) * 1000;

    setInterval(() => {
        const now = Date.now();
        const diff = closeTime - now;

        if (diff <= 0) {
            countdown.textContent = 'Closed';
            countdown.className = 'badge bg-secondary';
            return;
        }

        const mins = Math.floor(diff / 60000);
        const secs = Math.floor((diff % 60000) / 1000);
        countdown.textContent = `Closes in ${mins}m ${secs}s`;
    }, 1000);
}
</script>

<!-- CHART.JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('attendanceChart').getContext('2d');
const attendanceChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($graph_labels) ?>,
        datasets: [
            {
                label: 'Present',
                data: <?= json_encode($graph_present) ?>,
                backgroundColor: 'rgba(25, 135, 84, 0.7)'
            },
            {
                label: 'Absent',
                data: <?= json_encode($graph_absent) ?>,
                backgroundColor: 'rgba(220, 53, 69, 0.7)'
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            tooltip: { mode: 'index', intersect: false }
        },
        scales: {
            y: { beginAtZero: true, precision:0 },
        }
    }
});
</script>

<?php require_once '../layout/admin/footer.php'; ?>
