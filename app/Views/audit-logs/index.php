<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body class="d-flex">
<?php include __DIR__ . '/../partials/sidebar.php'; ?>
<div class="flex-grow-1">
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-shield-alt"></i> Audit Logs</h2>
            <div>
                <button class="btn btn-warning" onclick="exportLogs()">
                    <i class="fas fa-download"></i> Export Logs
                </button>
                <button class="btn btn-danger" onclick="cleanupLogs()">
                    <i class="fas fa-trash"></i> Cleanup Old Logs
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-filter"></i> Filter Logs</h5>
            </div>
            <div class="card-body">
                <form id="logFilters" class="row g-3">
                    <div class="col-md-3">
                        <label for="actionFilter" class="form-label">Action</label>
                        <select class="form-select" id="actionFilter" name="action">
                            <option value="">All Actions</option>
                            <option value="login">Login</option>
                            <option value="logout">Logout</option>
                            <option value="member_add">Add Member</option>
                            <option value="member_edit">Edit Member</option>
                            <option value="member_delete">Delete Member</option>
                            <option value="dues_add">Add Dues</option>
                            <option value="shares_add">Add Shares</option>
                            <option value="settings_update">Update Settings</option>
                            <option value="password_change">Password Change</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="userFilter" class="form-label">User</label>
                        <select class="form-select" id="userFilter" name="user">
                            <option value="">All Users</option>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['email']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="dateFrom" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="dateFrom" name="dateFrom" value="<?= date('Y-m-d', strtotime('-7 days')) ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="dateTo" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="dateTo" name="dateTo" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block w-100">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?= $totalLogs ?? 0 ?></h4>
                                <p class="mb-0">Total Logs</p>
                            </div>
                            <div>
                                <i class="fas fa-list fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?= $todayLogs ?? 0 ?></h4>
                                <p class="mb-0">Today's Logs</p>
                            </div>
                            <div>
                                <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?= $failedLogins ?? 0 ?></h4>
                                <p class="mb-0">Failed Logins</p>
                            </div>
                            <div>
                                <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?= $activeUsers ?? 0 ?></h4>
                                <p class="mb-0">Active Users</p>
                            </div>
                            <div>
                                <i class="fas fa-users fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Audit Logs Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-table"></i> System Activity Logs</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="auditLogsTable">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Details</th>
                                <th>IP Address</th>
                                <th>User Agent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($logs)): ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('M d, Y H:i:s', strtotime($log['created_at'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= htmlspecialchars($log['user_email'] ?? 'System') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= getActionColor($log['action']) ?>">
                                                <?= formatAction($log['action']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?= htmlspecialchars($log['details']) ?></small>
                                        </td>
                                        <td>
                                            <code class="small"><?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?></code>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= htmlspecialchars(substr($log['user_agent'] ?? 'N/A', 0, 50)) ?>
                                                <?= strlen($log['user_agent'] ?? '') > 50 ? '...' : '' ?>
                                            </small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                                        <p>No audit logs found</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#auditLogsTable').DataTable({
        order: [[0, 'desc']], // Sort by timestamp descending
        pageLength: 25,
        responsive: true,
        language: {
            search: "Search logs:",
            lengthMenu: "Show _MENU_ logs per page",
            info: "Showing _START_ to _END_ of _TOTAL_ logs"
        }
    });
});

function exportLogs() {
    const action = document.getElementById('actionFilter').value;
    const user = document.getElementById('userFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    const url = '<?= \App\Helpers\Url::appUrl() ?>/audit-logs/export?' + 
                'action=' + encodeURIComponent(action) + 
                '&user=' + encodeURIComponent(user) + 
                '&dateFrom=' + encodeURIComponent(dateFrom) + 
                '&dateTo=' + encodeURIComponent(dateTo);
    
    window.location.href = url;
}

function cleanupLogs() {
    if (confirm('Are you sure you want to delete logs older than 90 days? This action cannot be undone.')) {
        fetch('<?= \App\Helpers\Url::appUrl() ?>/audit-logs/cleanup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Old logs cleaned up successfully!');
                location.reload();
            } else {
                alert('Error cleaning up logs: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
}
</script>
</body>
</html>

<?php
// Helper functions for the view
function getActionColor($action) {
    $colors = [
        'login' => 'success',
        'logout' => 'secondary',
        'login_failed' => 'danger',
        'member_add' => 'primary',
        'member_edit' => 'warning',
        'member_delete' => 'danger',
        'dues_add' => 'info',
        'shares_add' => 'info',
        'settings_update' => 'warning',
        'password_change' => 'warning'
    ];
    return $colors[$action] ?? 'secondary';
}

function formatAction($action) {
    $formatted = [
        'login' => 'Login',
        'logout' => 'Logout',
        'login_failed' => 'Login Failed',
        'member_add' => 'Add Member',
        'member_edit' => 'Edit Member',
        'member_delete' => 'Delete Member',
        'dues_add' => 'Add Dues',
        'shares_add' => 'Add Shares',
        'settings_update' => 'Update Settings',
        'password_change' => 'Password Change'
    ];
    return $formatted[$action] ?? ucfirst(str_replace('_', ' ', $action));
}
?> 