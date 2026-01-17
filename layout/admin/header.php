<?php
require_once './session.php';

if (!isset($_SESSION['admin_id'])) {
    redirect('../admin/login.php');
}

$admin = $_SESSION['admin_id'] ? $_SESSION['role'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($page_title) ? $page_title : 'Admin Dashboard'; ?></title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<!-- CSS -->
 <link rel="stylesheet" href=".././assets/css/admin.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container-fluid">
        <button class="btn btn-primary d-md-none" id="sidebarToggle">
            <i class="bi bi-list"></i> 
        </button>
        <a class="navbar-brand w-75" href="#"><i class="bi bi-calendar-check"></i> Admin Attendance System</a>
    </div>
</nav>

<!-- Overlay for small screen -->
<div class="overlay" id="sidebarOverlay"></div>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-3 col-lg-2 sidebar p-3 bg-light min-vh-100 position-fixed" id="sidebar">
            <div class="mb-4 ps-3">
                <h5 class="text-primary fw-bold">SIWES Admin</h5>
            </div>

            <nav class="nav flex-column">
                <a class="nav-link mb-2" href="index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                <a class="nav-link mb-2" href="attendance_dates.php"><i class="bi bi-calendar-plus me-2"></i> Host Attendance</a>
                <a class="nav-link mb-2" href="attendance_view.php"><i class="bi bi-eye me-2"></i> View Attendance</a>
                <a class="nav-link mb-2" href="students.php"><i class="bi bi-people me-2"></i> Students</a>
                <hr>
                <a class="nav-link text-danger" href="logout.php" onclick="return confirm('Are you sure you want to logout?')">
                    <i class="bi bi-box-arrow-left me-2"></i> Logout
                </a>
            </nav>
        </div>

        <!-- MAIN CONTENT -->
        <main class="main-content col-md-9 offset-md-3 col-lg-10 offset-lg-2" id="mainContent">





<script>
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mainContent = document.getElementById('mainContent');

    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('show');
        sidebarOverlay.classList.toggle('show');
    });

    sidebarOverlay.addEventListener('click', () => {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
    });
</script>
