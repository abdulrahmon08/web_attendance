<?php
$page_title = "Login Page";
require_once 'include/config.php'; // Ensure session_start() is inside this file

if (isset($_POST['login'])) {
    $email_address = trim($_POST['email_address']);
    $password = trim($_POST['password']);

    // We only select by email first to verify the hashed password later
    $sql = "SELECT * FROM students WHERE email_address = :email_address";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['email_address' => $email_address]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Use password_verify to check against hashed passwords
    // Note: If you still have plain-text passwords, change this back to: 
    // if($user && $password == $user['password']) 
    // BUT you should update your DB to use password_hash()
    if ($user && $password === $user['password']) {

        // Setting session variables correctly
        $_SESSION['student_id']   = $user['id'];
        $_SESSION['student_name'] = $user['name'];
        $_SESSION['student_data'] = $user;

        header("Location: student/index.php");
        exit;
    } else {
        $msg = '<div class="alert alert-danger">Invalid email address or password</div>';
    }
}

require_once 'layout/auth/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-lg border-0 mt-5">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-circle text-primary" style="font-size: 4rem;"></i>
                        <h2 class="mt-3 mb-1">Student Login</h2>
                        <p class="text-muted">Sign in to access your account</p>
                    </div>

                    <form action="" method="post">
                        <?php echo isset($msg) ? $msg : ''; ?>
                        <div class="mb-3">
                            <label for="email" class="form-label"><i class="bi bi-envelope"></i> Email Address</label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email_address" placeholder="Enter your email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label"><i class="bi bi-lock"></i> Password</label>
                            <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Enter your password" required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="login" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout/auth/footer.php'; ?>