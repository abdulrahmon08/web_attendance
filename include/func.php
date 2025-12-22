<?php

function redirect($url){
    header('Location: '.$url);
    exit();
}

function addNumber($num1 , $num2){

    return $num1 + $num2;

}


function fillMissingAttendance($conn, $student_id) {
    // 1. Get student's join date
    $stmt = $conn->prepare("SELECT date_joined FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $join_date = $stmt->fetchColumn();
    
    if (!$join_date) return;

    // Handle date format (slashes vs dashes)
    if (strpos($join_date, '/') !== false) {
        $start = DateTime::createFromFormat('d/m/Y', $join_date);
    } else {
        $start = new DateTime($join_date);
    }

    if (!$start) return;

    // We check up to "Today" (to set the default as Absent for today)
    $today = new DateTime('today');
    
    if ($start > $today) return;

    // Loop through every day from join date until END of today
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($start, $interval, (clone $today)->modify('+1 day'));

    foreach ($period as $date) {
        $date_str = $date->format('Y-m-d');
        $day_of_week = (int)$date->format('N'); // 1 (Mon) to 7 (Sun)

        // Only for Monday to Friday
        if ($day_of_week >= 1 && $day_of_week <= 5) {
            
            // Check if ANY record exists for this date
            $check = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND attendance_date = ?");
            $check->execute([$student_id, $date_str]);
            
            if ($check->rowCount() == 0) {
                // DEFAULT OPTION: Mark as Absent
                $insert = $conn->prepare("
                    INSERT INTO attendance (student_id, attendance_date, status) 
                    VALUES (?, ?, 'Absent')
                ");
                $insert->execute([$student_id, $date_str]);
            }
        }
    }
}
?>