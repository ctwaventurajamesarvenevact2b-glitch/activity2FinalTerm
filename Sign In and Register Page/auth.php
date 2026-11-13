
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function requireLogin() {

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

}

// Check if user is admin
function requireAdmin() {

    requireLogin();

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: dashboard.php");
        exit();
    }

}

// Check if user is regular user
function requireUser() {

    requireLogin();

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
        header("Location: dashboard.php");
        exit();
    }

}
?>
