<?php
// includes/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/app.php";
require_once __DIR__ . "/../config/db.php";

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
}

function requireRole($roles) {
    requireLogin();

    if (!is_array($roles)) {
        $roles = [$roles];
    }

    if (!in_array($_SESSION['role'], $roles)) {
        header("Location: " . BASE_URL . "index.php");
        exit();
    }
}

function redirectByRole($role) {
    if ($role === "admin") {
        header("Location: " . BASE_URL . "admin/dashboard.php");
    } elseif ($role === "owner") {
        header("Location: " . BASE_URL . "owner/dashboard.php");
    } elseif ($role === "staff") {
        header("Location: " . BASE_URL . "staff/dashboard.php");
    } else {
        header("Location: " . BASE_URL . "login.php");
    }
    exit();
}
?>