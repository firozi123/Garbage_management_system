<?php
// user/delete_report.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$report_id = $_GET['id'] ?? null;

if ($report_id) {
    // Only delete if user_id matches
    $stmt = $pdo->prepare("DELETE FROM reports WHERE id = ? AND user_id = ?");
    $stmt->execute([$report_id, $_SESSION['user_id']]);
    
    header("Location: dashboard.php?msg=" . urlencode("Report deleted successfully."));
    exit();
} else {
    header("Location: dashboard.php");
    exit();
}
?>
