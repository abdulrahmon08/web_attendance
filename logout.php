<?php
/**
 * logout.php
 * This file clears all user data and returns them to the login screen.
 */

// 1. Load the configuration (which contains session_start())
require_once 'include/config.php';

// 2. Clear all session variables
$_SESSION = array();

// 3. Destroy the session cookie in the browser
if (ini_get("session_use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Destroy the session on the server
session_destroy();

// 5. Redirect back to the login page
header("Location: index.php");
exit;