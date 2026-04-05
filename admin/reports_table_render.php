<?php
// admin/reports_table_render.php (included by dashboard.php)

// If not set, default to all reports
if (!isset($render_reports)) {
    $render_reports = $allReports;
}
if (!isset($return_tab)) {
    $return_tab = 'all_reports';
}
?>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Location</th>
            <th>Description</th>
            <th>Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($render_reports as $report): ?>
        <tr>
            <td>#<?php echo $report['id']; ?></td>
            <td>
                <?php echo htmlspecialchars($report['user_name']); ?><br>
                <small style="color: var(--text-muted);"><?php echo htmlspecialchars($report['user_email']); ?></small>
            </td>
            <td><?php echo htmlspecialchars($report['location']); ?></td>
            <td><?php echo htmlspecialchars($report['description']); ?></td>
            <td><?php echo date('M d, Y', strtotime($report['created_at'])); ?></td>
            <td>
                <form action="dashboard.php" method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                    <input type="hidden" name="return_tab" value="<?php echo $return_tab; ?>">
                    <select name="status" class="form-control" style="padding: 0.25rem; width: auto; min-width: 100px;">
                        <option value="Pending" <?php echo $report['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Collected" <?php echo $report['status'] === 'Collected' ? 'selected' : ''; ?>>Collected</option>
                    </select>
                    <button type="submit" class="auth-btn" style="padding: 0.35rem 0.6rem; font-size: 0.8rem;">Update</button>
                </form>
            </td>
            <td>
                <a href="?delete_report=<?php echo $report['id']; ?>" class="action-link del-link" onclick="return confirm('Are you sure you want to delete this report?');">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if(count($render_reports) == 0): ?>
            <tr><td colspan="7" style="text-align:center;">No reports found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
