<?php
// admin/print_logs.php
session_start();
require_once '../config/db.php';

// Verify Admin Privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch Reports for Audit Log
$stmtAllReports = $pdo->query("
    SELECT r.id, r.location, r.description, r.status, r.created_at, u.name, u.email 
    FROM reports r 
    JOIN users u ON r.user_id = u.id 
    ORDER BY r.created_at DESC
");
$logs = $stmtAllReports->fetchAll(PDO::FETCH_ASSOC);

$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$reportCount = $pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - Garbage Management</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 2rem; color: #333; }
        .header { text-align: center; margin-bottom: 2rem; }
        .header h1 { color: #10B981; margin-bottom: 0.5rem; }
        .meta { margin-bottom: 1.5rem; font-size: 0.9rem; color: #555; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 2rem; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: bold; }
        
        .status-pending { color: #D97706; font-weight: bold; }
        .status-collected { color: #059669; font-weight: bold; }

        /* Print Specific Styling */
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
        
        .btn-print {
            display: inline-block;
            background: #10B981; color: white;
            padding: 10px 20px; text-decoration: none; border-radius: 5px;
            font-weight: bold; cursor: pointer; border: none; font-size: 1rem;
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: right; margin-bottom: 1rem;">
        <button onclick="window.print()" class="btn-print">🖨️ Save as PDF / Print</button>
    </div>

    <div class="header">
        <h1>EcoManage - System Audit Logs</h1>
        <p>Generated on: <?php echo date('F d, Y - h:i A'); ?></p>
    </div>

    <div class="meta">
        <strong>Total Global Users:</strong> <?php echo $userCount; ?><br>
        <strong>Total Lifetime Reports:</strong> <?php echo $reportCount; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Log ID</th>
                <th>Submitter Info</th>
                <th>Location</th>
                <th>Status</th>
                <th>Date Logged</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($logs as $log): ?>
            <tr>
                <td>#<?php echo $log['id']; ?></td>
                <td>
                    <strong><?php echo htmlspecialchars($log['name']); ?></strong><br>
                    <span style="font-size: 0.85em; color: #666;"><?php echo htmlspecialchars($log['email']); ?></span>
                </td>
                <td><?php echo htmlspecialchars($log['location']); ?></td>
                <td class="<?php echo $log['status'] === 'Collected' ? 'status-collected' : 'status-pending'; ?>">
                    <?php echo $log['status']; ?>
                </td>
                <td><?php echo date('M d, Y', strtotime($log['created_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
            
            <?php if(count($logs) == 0): ?>
            <tr><td colspan="5" style="text-align:center;">No records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        // Automatically trigger PDF/Print dialog when the page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
