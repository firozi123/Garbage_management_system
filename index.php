<?php
// index.php
session_start();

// Include db.php to check database & table setup on very first load
require_once 'config/db.php'; 

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Garbage Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero {
            text-align: center;
            margin-top: 10vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
        }
        .hero h1 {
            font-size: 3.5rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            font-weight: 800;
        }
        .hero p {
            font-size: 1.25rem;
            color: var(--text-muted);
            max-width: 650px;
            margin: 0 auto;
            line-height: 1.6;
        }
        .action-buttons {
            display: flex;
            gap: 1.5rem;
            margin-top: 2rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        .btn-large {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-large:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .btn-user {
            background: var(--primary-color);
            color: white;
            border: 2px solid var(--primary-color);
        }
        .btn-admin {
            background: white;
            color: var(--text-main);
            border: 2px solid var(--border-color);
        }
        .btn-admin:hover {
            border-color: var(--text-main);
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">EcoManage</a>
    <div class="nav-links">
        <a href="auth/login.php?type=user" class="auth-btn">User Login</a>
        <a href="auth/login.php?type=admin" class="auth-btn" style="background:var(--text-main); margin-left:8px;">Admin Login</a>
        <a href="auth/register.php" style="margin-left:8px; font-weight:600;">Register Account</a>
    </div>
</nav>

<div class="container hero">
    <h1>Cleaner Streets, Greener Future.</h1>
    <p>
        EcoManage is the modern approach to municipal reporting. Whether you're a responsible citizen submitting a localized garbage report, or a systemic administrator sorting out collection logistics—join us in working toward a healthier environment.
    </p>

    <div class="action-buttons">
        <a href="auth/login.php?type=user" class="btn-large btn-user">Login as User</a>
        <a href="auth/login.php?type=admin" class="btn-large btn-admin">🔑 Login as Admin</a>
    </div>
</div>

<script src="assets/js/script.js"></script>
</body>
</html>
