<?php
// user/edit_report.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$error = '';
$report_id = $_GET['id'] ?? null;

if (!$report_id) {
    header("Location: dashboard.php");
    exit();
}

// Fetch report
$stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ? AND user_id = ?");
$stmt->execute([$report_id, $_SESSION['user_id']]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$report) {
    echo "Report not found or unauthorized.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);

    if (empty($location) || empty($description)) {
        $error = "Location and Description are required.";
    } else {
        $updateStmt = $pdo->prepare("UPDATE reports SET location = ?, description = ? WHERE id = ? AND user_id = ?");
        if ($updateStmt->execute([$location, $description, $report_id, $_SESSION['user_id']])) {
            header("Location: dashboard.php?msg=" . urlencode("Report updated successfully."));
            exit();
        } else {
            $error = "Failed to update report.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Report - Garbage Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <a href="dashboard.php" class="logo">EcoManage</a>
    <div class="nav-links">
        <a href="dashboard.php" class="auth-btn" style="background:var(--text-main);">Back to Dashboard</a>
    </div>
</nav>

<div class="container" style="max-width: 600px;">
    <div class="card">
        <h2>Edit Report #<?php echo $report['id']; ?></h2>
        <?php if($error): ?><div class="error-msg"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        
        <form action="" method="POST">
            <div class="form-group">
                <label>Location / Area Name</label>
                <input type="text" name="location" class="form-control" required value="<?php echo htmlspecialchars($report['location']); ?>">
            </div>
            <div class="form-group">
                <label>Description of the Issue</label>
                <textarea name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($report['description']); ?></textarea>
            </div>
            <button type="submit" class="btn-block">Update Report</button>
        </form>
    </div>
</div>

</body>
</html>
