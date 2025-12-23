<?php

function redirect($url){
    header('Location: '.$url);
    exit();
}

function addNumber($num1 , $num2){

    return $num1 + $num2;

}


function fillMissingAttendance(PDO $conn, int $student_id): void
{
    // 1. Get student's join date
    $stmt = $conn->prepare("SELECT date_joined FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $join_date = $stmt->fetchColumn();

    if (!$join_date) return;

    // Handle date formats (slashes vs dashes)
    if (strpos($join_date, '/') !== false) {
        $start = DateTime::createFromFormat('d/m/Y', $join_date);
    } else {
        $start = new DateTime($join_date);
    }

    if (!$start) return;

    // Today
    $today = new DateTime('today');
    if ($start > $today) return;

    // 2. Get all attendance dates already in DB for this student
    $stmt = $conn->prepare("
        SELECT attendance_date 
        FROM attendance 
        WHERE student_id = ?
    ");
    $stmt->execute([$student_id]);
    $existing_dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $existing_dates = array_flip($existing_dates); // for faster lookup

    // 3. Loop from join date to today
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($start, $interval, (clone $today)->modify('+1 day'));

    // Prepare insert once
    $insert = $conn->prepare("
        INSERT INTO attendance (student_id, attendance_date, status, grade)
        VALUES (?, ?, 'Absent', 0)
    ");

    foreach ($period as $date) {
        $date_str = $date->format('Y-m-d');
        $day_of_week = (int)$date->format('N'); // 1 = Mon, 7 = Sun

        // Only weekdays
        if ($day_of_week >= 1 && $day_of_week <= 5) {
            // Skip if attendance already exists
            if (!isset($existing_dates[$date_str])) {
                $insert->execute([$student_id, $date_str]);
            }
        }
    }
}
