<?php
$page_title = "Login Page";
require_once '../include/config.php'; 

if (isset($_POST['login'])) {
    $email_address = trim($_POST['email_address']);
    $password = trim($_POST['password']);

    // 1. Check the Admin table first
    $sql_admin = "SELECT * FROM admins WHERE email_address = :email_address";
    $stmt_admin = $conn->prepare($sql_admin);
    $stmt_admin->execute(['email_address' => $email_address]);
    $admin = $stmt_admin->fetch(PDO::FETCH_ASSOC);

    if ($admin && $password === $admin['password']) {
        // Successful Admin Login
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['role']       = 'admin';

        header("Location: index.php");
        exit;
    } else {
        $msg = '<div class="alert alert-danger">Invalid email address or password</div>';
    }
}

require_once '../layout/auth/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-lg border-0 mt-5">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-lock text-primary" style="font-size: 4rem;"></i>
                        <h2 class="mt-3 mb-1">Admin Login</h2>
                        <p class="text-muted">Enter your credentials to continue</p>
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
                <div class="card-footer text-center py-3">
                    <div class="small"><a href="index.php">Go back to home</a></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../layout/auth/footer.php'; ?>