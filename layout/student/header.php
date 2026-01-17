<?php
require_once './session.php';

if (!isset($_SESSION['student_id'])) {
    redirect('../index.php');
}

$student = $_SESSION['student_id'] ? $_SESSION['student_data'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Student Dashboard'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- CSS -->
     <link rel="stylesheet" href=".././assets/css/student.css">
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
                    <button class="btn btn-primary d-md-none me-2" id="sidebarToggle" aria-label="Toggle sidebar">
                        <i class="bi bi-list"></i>
                    </button>
                    <a class="navbar-brand" href="#">
                        <i class="bi bi-calendar-check"></i> Student Attendance System
                    </a>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item dropdown">
                        <button class="btn btn-light dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($student['name']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../index.php" onclick="return confirm('Are you sure you want to logout?')"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- SIDEBAR -->
    <div class="col-md-3 col-lg-2 sidebar bg-light min-vh-100 position-fixed" id="sidebar">
        <div class="p-3">
            <h5 class="text-primary fw-bold mb-4">SIWES Portal</h5>
            <nav class="nav flex-column">
                <a class="nav-link mb-2" href="index.php">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a class="nav-link mb-2" href="mark_attendance.php">
                    <i class="bi bi-calendar-check me-2"></i> Mark Attendance
                </a>
                <a class="nav-link mb-2" href="profile.php">
                    <i class="bi bi-person me-2"></i> Profile
                </a>
                <hr>
                <a class="nav-link text-danger" href="../logout.php"
                   onclick="return confirm('Are you sure you want to logout?')">
                    <i class="bi bi-box-arrow-left me-2"></i> Logout
                </a>
            </nav>
        </div>
    </div>

    <!-- OVERLAY FOR MOBILE -->
    <div class="overlay" id="sidebarOverlay"></div>

    <main class="main-content mt-5">


    <script>
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    });

    overlay.addEventListener('click', () => {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
    });
</script>
