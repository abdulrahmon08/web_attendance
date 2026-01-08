<?php
require_once '../include/config.php';

// Check if a date is selected
$date = $_GET['date'] ?? null;

// Fetch all hosted attendance dates
$dates = $conn->query("
    SELECT attendance_date 
    FROM attendance_dates 
    ORDER BY attendance_date DESC
")->fetchAll(PDO::FETCH_ASSOC);

// If a date is selected, fetch attendance records
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

require_once '../layout/admin/header.php';
?>

        <div class="col-md-9 col-lg-10 offset-md-3 offset-lg-2 p-4 mt-4">

            <h4 class="mb-4">Attendance Records</h4>

            <!-- LIST OF HOSTED DATES -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">
                    Select Attendance Date
                </div>
                <div class="card-body">
                    <?php if (empty($dates)): ?>
                        <p class="text-muted">No attendance dates hosted yet.</p>
                    <?php else: ?>
                        <?php foreach ($dates as $d): ?>
                            <a href="attendance_view.php?date=<?= $d['attendance_date'] ?>"
                               class="btn btn-outline-primary btn-sm me-2 mb-2">
                                <?= $d['attendance_date'] ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ATTENDANCE TABLE -->
            <?php if ($date): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div class="fw-bold">Attendance for <?= htmlspecialchars($date) ?></div>
                    <div>
                        <a href="attendance_view_print.php?date=<?= urlencode($date) ?>" target="_blank" class="btn btn-outline-secondary btn-sm me-2">Print / PDF</a>
                        <a href="attendance_view_export.php?date=<?= urlencode($date) ?>" class="btn btn-outline-success btn-sm">Download Excel</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Student Name</th>
                                <th>Status</th>
                                <th>Check-in Time</th>
                                <th>Checkout Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($records)): ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        No records found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($records as $r): ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?= htmlspecialchars($r['name']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $r['status'] === 'Present' ? 'success' : 'danger' ?>">
                                            <?= $r['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= $r['check_in_time'] ? date('h:i A', strtotime($r['check_in_time'])) : '-- : --' ?>
                                    </td>
                                    <td>
                                        <?= $r['checkout_time'] ? date('h:i A', strtotime($r['checkout_time'])) : '-- : --' ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php require_once '../layout/admin/footer.php'; ?>
