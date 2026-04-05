<?php
// user/dashboard.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
$activeTab = $_GET['tab'] ?? 'reports';

// Fetch current user details
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$currentUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

// Handle POST actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_report') {
        $location = trim($_POST['location']);
        $description = trim($_POST['description']);

        if (empty($location) || empty($description)) {
            $error = "Location and Description are required.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO reports (user_id, location, description, status) VALUES (?, ?, ?, 'Pending')");
            if ($stmt->execute([$user_id, $location, $description])) {
                $success = "Report added successfully.";
                $activeTab = 'reports'; // Return to reports tab on success
            } else {
                $error = "Failed to add report.";
                $activeTab = 'add_report';
            }
        }
    } 
    elseif ($action === 'edit_profile') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password']; // optional

        if (empty($name) || empty($email)) {
            $error = "Name and Email are required.";
            $activeTab = 'profile';
        } else {
            // Check if email belongs to someone else
            $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $checkEmail->execute([$email, $user_id]);
            if ($checkEmail->rowCount() > 0) {
                $error = "Email is already taken by another account.";
                $activeTab = 'profile';
            } else {
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $updStmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
                    $updStmt->execute([$name, $email, $hashed_password, $user_id]);
                } else {
                    $updStmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                    $updStmt->execute([$name, $email, $user_id]);
                }
                
                $_SESSION['name'] = $name; // update session
                $currentUser['name'] = $name;
                $currentUser['email'] = $email;
                $success = "Profile updated successfully!";
                $activeTab = 'profile';
            }
        }
    }
}

// Fetch user's reports
$stmtReports = $pdo->prepare("SELECT * FROM reports WHERE user_id = ? ORDER BY created_at DESC");
$stmtReports->execute([$user_id]);
$reports = $stmtReports->fetchAll(PDO::FETCH_ASSOC);

// For success messages from GET
if (isset($_GET['msg'])) {
    $success = $_GET['msg'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Garbage Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <a href="dashboard.php" class="logo">EcoManage</a>
    <div class="nav-links">
        <span>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
        <a href="../auth/logout.php" class="danger-btn">Logout</a>
    </div>
</nav>

<div class="dashboard-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <button class="menu-btn <?php echo $activeTab === 'reports' ? 'active' : ''; ?>" onclick="showTab('reports')">My Reports</button>
        <button class="menu-btn <?php echo $activeTab === 'add_report' ? 'active' : ''; ?>" onclick="showTab('add_report')">Add Report</button>
        <button class="menu-btn <?php echo $activeTab === 'profile' ? 'active' : ''; ?>" onclick="showTab('profile')">Edit Profile</button>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <?php if($error): ?><div class="error-msg"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if($success): ?><div class="success-msg"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

        <!-- Tab: My Reports -->
        <section id="reports" class="tab-content <?php echo $activeTab === 'reports' ? 'active' : ''; ?>">
            <div class="card">
                <h2>My Garbage Reports</h2>
                <?php if (count($reports) > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Location</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($reports as $report): ?>
                                <tr>
                                    <td>#<?php echo $report['id']; ?></td>
                                    <td><?php echo htmlspecialchars($report['location']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($report['description'], 0, 30)) . '...'; ?></td>
                                    <td>
                                        <?php 
                                            $statusClass = strtolower($report['status']);
                                            echo "<span class='status $statusClass'>{$report['status']}</span>";
                                        ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($report['created_at'])); ?></td>
                                    <td>
                                        <a href="edit_report.php?id=<?php echo $report['id']; ?>" class="action-link edit-link">Edit</a>
                                        <a href="delete_report.php?id=<?php echo $report['id']; ?>" class="action-link del-link">Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: var(--text-muted);">You have not reported any garbage yet.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Tab: Add Report -->
        <section id="add_report" class="tab-content <?php echo $activeTab === 'add_report' ? 'active' : ''; ?>">
            <div class="card" style="max-width: 600px; margin: 0 auto;">
                <h2>Report Garbage</h2>
                <form action="dashboard.php" method="POST">
                    <input type="hidden" name="action" value="add_report">
                    <div class="form-group">
                        <label>Location / Area Name</label>
                        <input type="text" name="location" class="form-control" placeholder="e.g. Near Central Park Entrance" required>
                    </div>
                    <div class="form-group">
                        <label>Description of the Issue</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Provide details..." required></textarea>
                    </div>
                    <button type="submit" class="btn-block">Submit Report</button>
                </form>
            </div>
        </section>

        <!-- Tab: Edit Profile -->
        <section id="profile" class="tab-content <?php echo $activeTab === 'profile' ? 'active' : ''; ?>">
            <div class="card" style="max-width: 600px; margin: 0 auto;">
                <h2>Edit Profile</h2>
                <form action="dashboard.php" method="POST">
                    <input type="hidden" name="action" value="edit_profile">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($currentUser['name']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($currentUser['email']); ?>">
                    </div>
                    <div class="form-group">
                        <label>New Password (leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control" placeholder="******">
                    </div>
                    <button type="submit" class="btn-block">Update Profile</button>
                </form>
            </div>
        </section>

    </main>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>
