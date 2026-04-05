<?php
// admin/dashboard.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$error = '';
$success = '';
$activeTab = $_GET['tab'] ?? 'overview';

// Handle POST actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    // Manage Report Status
    if ($action === 'update_status') {
        $report_id = $_POST['report_id'];
        $new_status = $_POST['status'];
        $stmt = $pdo->prepare("UPDATE reports SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $report_id]);
        $success = "Report status updated.";
        $activeTab = $_POST['return_tab'] ?? 'all_reports';
    }
    
    // Manage Users (Block/Unblock)
    elseif ($action === 'toggle_block') {
        $target_user = $_POST['user_id'];
        $block_value = $_POST['is_blocked'] == '1' ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE users SET is_blocked = ? WHERE id = ?");
        $stmt->execute([$block_value, $target_user]);
        $success = $block_value == 1 ? "User blocked successfully." : "User unblocked successfully.";
        $activeTab = 'users';
    }

    // Settings (Change Password)
    elseif ($action === 'change_password') {
        $new_password = $_POST['new_password'];
        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $_SESSION['user_id']]);
            $success = "Admin password updated successfully!";
        } else {
            $error = "Password cannot be empty.";
        }
        $activeTab = 'settings';
    }
}

// Handle GET Deletions
if (isset($_GET['delete_report'])) {
    $report_id = $_GET['delete_report'];
    $stmt = $pdo->prepare("DELETE FROM reports WHERE id = ?");
    $stmt->execute([$report_id]);
    header("Location: dashboard.php?msg=" . urlencode("Report deleted.") . "&tab=all_reports");
    exit();
}

if (isset($_GET['delete_user'])) {
    $target_user = $_GET['delete_user'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$target_user]);
    header("Location: dashboard.php?msg=" . urlencode("User and their reports deleted.") . "&tab=users");
    exit();
}

if (isset($_GET['msg'])) {
    $success = $_GET['msg'];
}

// Data Fetching for Tabs
$usersCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$reportsCount = $pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn();
$pendingCount = $pdo->query("SELECT COUNT(*) FROM reports WHERE status='Pending'")->fetchColumn();

// Fetch Users List
$stmtUsers = $pdo->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC");
$users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

// Fetch All Reports
$stmtAllReports = $pdo->query("
    SELECT r.*, u.name as user_name, u.email as user_email 
    FROM reports r JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC
");
$allReports = $stmtAllReports->fetchAll(PDO::FETCH_ASSOC);

// Fetch Pending Reports
$stmtPending = $pdo->query("
    SELECT r.*, u.name as user_name, u.email as user_email 
    FROM reports r JOIN users u ON r.user_id = u.id WHERE r.status='Pending' ORDER BY r.created_at DESC
");
$pendingReports = $stmtPending->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Garbage Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .stats-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: var(--bg-color); padding: 2rem; border-radius: 8px; text-align: center; border: 1px solid var(--border-color); }
        .stat-card h3 { color: var(--text-muted); font-size: 1rem; margin-bottom: 0.5rem; }
        .stat-card .num { font-size: 2.5rem; font-weight: 700; color: var(--primary-color); }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="dashboard.php" class="logo">EcoManage Admin</a>
    <div class="nav-links">
        <span style="margin-left: 1rem; margin-right: 1rem;">| Admin: <?php echo htmlspecialchars($_SESSION['name']); ?></span>
        <a href="../auth/logout.php" class="danger-btn">Logout</a>
    </div>
</nav>

<div class="dashboard-layout">
    <!-- Sidebar Menu -->
    <aside class="sidebar">
        <button class="menu-btn <?php echo $activeTab === 'overview' ? 'active' : ''; ?>" onclick="showTab('overview')">Overview</button>
        <button class="menu-btn <?php echo $activeTab === 'users' ? 'active' : ''; ?>" onclick="showTab('users')">Manage Users</button>
        <button class="menu-btn <?php echo $activeTab === 'all_reports' ? 'active' : ''; ?>" onclick="showTab('all_reports')">All Reports</button>
        <button class="menu-btn <?php echo $activeTab === 'pending_reports' ? 'active' : ''; ?>" onclick="showTab('pending_reports')">Pending Reports</button>
        <button class="menu-btn <?php echo $activeTab === 'settings' ? 'active' : ''; ?>" onclick="showTab('settings')">Settings</button>
        <a href="print_logs.php" target="_blank" class="menu-btn" style="text-decoration:none; display:block; color:#D97706; margin-top:2rem; border:1px solid #D97706; text-align:center;">
             Print / Save Audit Logs (PDF)
        </a>
    </aside>

    <main class="main-content">
        <?php if($error): ?><div class="error-msg"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if($success): ?><div class="success-msg"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

        <!-- Tab: Overview -->
        <section id="overview" class="tab-content <?php echo $activeTab === 'overview' ? 'active' : ''; ?>">
            <div class="card">
                <h2>Admin Overview</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Users</h3>
                        <div class="num"><?php echo $usersCount; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Total Reports</h3>
                        <div class="num"><?php echo $reportsCount; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Pending Reports</h3>
                        <div class="num" style="color: #D97706;"><?php echo $pendingCount; ?></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tab: Users -->
        <section id="users" class="tab-content <?php echo $activeTab === 'users' ? 'active' : ''; ?>">
            <div class="card">
                <h2>Registered Users</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $user): ?>
                            <tr>
                                <td>#<?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if(isset($user['is_blocked']) && $user['is_blocked'] == 1): ?>
                                        <span class="status" style="background:#FEE2E2; color:#B91C1C;">Blocked</span>
                                    <?php else: ?>
                                        <span class="status collected">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!-- Block/Unblock Form -->
                                    <form action="dashboard.php" method="POST" style="display:inline-block;">
                                        <input type="hidden" name="action" value="toggle_block">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="is_blocked" value="<?php echo $user['is_blocked']; ?>">
                                        <button type="submit" class="auth-btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; background: #6B7280;">
                                            <?php echo ($user['is_blocked'] == 1) ? 'Unblock' : 'Block'; ?>
                                        </button>
                                    </form>
                                    <a href="?delete_user=<?php echo $user['id']; ?>" class="action-link del-link" style="margin-left:8px;" onclick="return confirm('Delete this user and all their reports entirely?');">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Tab: All Reports -->
        <section id="all_reports" class="tab-content <?php echo $activeTab === 'all_reports' ? 'active' : ''; ?>">
            <div class="card">
                <h2>All Garbage Reports</h2>
                <div class="table-container">
                    <?php include 'reports_table_render.php'; ?>
                </div>
            </div>
        </section>

         <!-- Tab: Pending Reports -->
         <section id="pending_reports" class="tab-content <?php echo $activeTab === 'pending_reports' ? 'active' : ''; ?>">
            <div class="card">
                <h2>User Requests / Pending Reports</h2>
                <div class="table-container">
                    <?php 
                        $render_reports = $pendingReports;
                        $return_tab = 'pending_reports';
                        include 'reports_table_render.php'; 
                    ?>
                </div>
            </div>
        </section>

        <!-- Tab: Settings -->
        <section id="settings" class="tab-content <?php echo $activeTab === 'settings' ? 'active' : ''; ?>">
            <div class="card" style="max-width: 600px;">
                <h2>Basic Settings</h2>
                <form action="dashboard.php" method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <div class="form-group">
                        <label>New Admin Password</label>
                        <input type="password" name="new_password" class="form-control" required placeholder="Enter new secured password">
                    </div>
                    <button type="submit" class="btn-block">Change Password</button>
                </form>
            </div>
        </section>

    </main>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>
