<?php
// admin/edit_driver.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$error = '';
$success = '';

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: dashboard.php?tab=drivers");
    exit();
}

$driver_id = $_GET['id'];

// Handle POST Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $vehicle_type = $_POST['vehicle_type'];
    $vehicle_number = $_POST['vehicle_number'];
    $phone = $_POST['phone'];
    
    $stmt = $pdo->prepare("UPDATE drivers SET name = ?, vehicle_type = ?, vehicle_number = ?, phone = ? WHERE id = ?");
    $stmt->execute([$name, $vehicle_type, $vehicle_number, $phone, $driver_id]);
    
    header("Location: dashboard.php?msg=" . urlencode("Driver updated successfully.") . "&tab=drivers");
    exit();
}

// Fetch current driver data
$stmt = $pdo->prepare("SELECT * FROM drivers WHERE id = ?");
$stmt->execute([$driver_id]);
$driver = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$driver) {
    header("Location: dashboard.php?msg=" . urlencode("Driver not found.") . "&tab=drivers");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Driver - EcoManage Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <a href="dashboard.php" class="logo">EcoManage Admin</a>
    <div class="nav-links">
        <a href="dashboard.php?tab=drivers" class="auth-btn" style="background:#4B5563; text-decoration:none;">Back to Dashboard</a>
    </div>
</nav>

<div class="container" style="max-width: 600px; margin-top: 3rem;">
    <div class="card">
        <h2 style="margin-bottom: 1rem;">Edit Driver</h2>
        <?php if($error): ?><div class="error-msg"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if($success): ?><div class="success-msg"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
        
        <form action="" method="POST">
            <div class="form-group">
                <label>Driver Name</label>
                <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($driver['name']); ?>">
            </div>
            <div class="form-group">
                <label>Vehicle Type</label>
                <input type="text" name="vehicle_type" class="form-control" required value="<?php echo htmlspecialchars($driver['vehicle_type']); ?>">
            </div>
            <div class="form-group">
                <label>Vehicle Number</label>
                <input type="text" name="vehicle_number" class="form-control" required value="<?php echo htmlspecialchars($driver['vehicle_number']); ?>">
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" class="form-control" required value="<?php echo htmlspecialchars($driver['phone']); ?>">
            </div>
            <button type="submit" class="btn-block">Update Driver</button>
        </form>
    </div>
</div>

</body>
</html>
