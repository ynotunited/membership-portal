<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Maintenance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body class="d-flex">
<?php include __DIR__ . '/../partials/sidebar.php'; ?>
<div class="flex-grow-1">
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-tools"></i> System Maintenance</h2>
            <div>
                <button class="btn btn-success me-2" onclick="createBackup()">
                    <i class="fas fa-download"></i> Create Backup
                </button>
                <button class="btn btn-warning" onclick="optimizeSystem()">
                    <i class="fas fa-magic"></i> Optimize System
                </button>
            </div>
        </div>

        <!-- System Health Overview -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?= $diskUsage ?? 0 ?>%</h4>
                                <p class="mb-0">Disk Usage</p>
                            </div>
                            <div>
                                <i class="fas fa-hdd fa-2x opacity-75"></i>
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
                                <h4><?= $memoryUsage ?? 0 ?>%</h4>
                                <p class="mb-0">Memory Usage</p>
                            </div>
                            <div>
                                <i class="fas fa-memory fa-2x opacity-75"></i>
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
                                <h4><?= $cpuUsage ?? 0 ?>%</h4>
                                <p class="mb-0">CPU Usage</p>
                            </div>
                            <div>
                                <i class="fas fa-microchip fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?= $uptime ?? 0 ?> days</h4>
                                <p class="mb-0">System Uptime</p>
                            </div>
                            <div>
                                <i class="fas fa-clock fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance Tasks -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-database"></i> Database Maintenance</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Database Size</h6>
                                    <small class="text-muted"><?= $dbSize ?? '0 MB' ?></small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary" onclick="optimizeDatabase()">
                                    Optimize
                                </button>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Old Logs</h6>
                                    <small class="text-muted"><?= $oldLogs ?? 0 ?> records older than 90 days</small>
                                </div>
                                <button class="btn btn-sm btn-outline-warning" onclick="cleanupLogs()">
                                    Cleanup
                                </button>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Temporary Files</h6>
                                    <small class="text-muted"><?= $tempFiles ?? 0 ?> files</small>
                                </div>
                                <button class="btn btn-sm btn-outline-danger" onclick="cleanupTempFiles()">
                                    Cleanup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-shield-alt"></i> Security & Monitoring</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Failed Login Attempts</h6>
                                    <small class="text-muted"><?= $failedLogins ?? 0 ?> in last 24 hours</small>
                                </div>
                                <span class="badge bg-<?= $failedLogins > 10 ? 'danger' : 'success' ?>">
                                    <?= $failedLogins > 10 ? 'High' : 'Normal' ?>
                                </span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">SSL Certificate</h6>
                                    <small class="text-muted">Expires: <?= $sslExpiry ?? 'N/A' ?></small>
                                </div>
                                <span class="badge bg-<?= $sslStatus === 'valid' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($sslStatus ?? 'Unknown') ?>
                                </span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">System Updates</h6>
                                    <small class="text-muted">Last check: <?= $lastUpdateCheck ?? 'Never' ?></small>
                                </div>
                                <button class="btn btn-sm btn-outline-info" onclick="checkUpdates()">
                                    Check
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backup Management -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-cloud-download-alt"></i> Backup Management</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Backup Name</th>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Created</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($backups)): ?>
                                        <?php foreach ($backups as $backup): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($backup['name']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $backup['type'] === 'full' ? 'primary' : 'secondary' ?>">
                                                        <?= ucfirst($backup['type']) ?>
                                                    </span>
                                                </td>
                                                <td><?= $backup['size'] ?></td>
                                                <td><?= date('M d, Y H:i', strtotime($backup['created_at'])) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $backup['status'] === 'completed' ? 'success' : 'warning' ?>">
                                                        <?= ucfirst($backup['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button class="btn btn-sm btn-outline-primary" onclick="downloadBackup('<?= $backup['id'] ?>')">
                                                            <i class="fas fa-download"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-success" onclick="restoreBackup('<?= $backup['id'] ?>')">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteBackup('<?= $backup['id'] ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No backups found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Logs -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-file-alt"></i> System Logs</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>Level</th>
                                        <th>Message</th>
                                        <th>Source</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($systemLogs)): ?>
                                        <?php foreach ($systemLogs as $log): ?>
                                            <tr>
                                                <td>
                                                    <small class="text-muted">
                                                        <?= date('M d, Y H:i:s', strtotime($log['timestamp'])) ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $log['level'] === 'ERROR' ? 'danger' : ($log['level'] === 'WARNING' ? 'warning' : 'info') ?>">
                                                        <?= $log['level'] ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($log['message']) ?></td>
                                                <td><small class="text-muted"><?= htmlspecialchars($log['source']) ?></small></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No recent system logs</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function createBackup() {
    if (confirm('Are you sure you want to create a new backup? This may take a few minutes.')) {
        fetch('<?= \App\Helpers\Url::appUrl() ?>/admin/system/backup', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Backup created successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function optimizeSystem() {
    if (confirm('Are you sure you want to optimize the system? This may take a few minutes.')) {
        fetch('<?= \App\Helpers\Url::appUrl() ?>/admin/system/optimize', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('System optimization completed!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function optimizeDatabase() {
    if (confirm('Are you sure you want to optimize the database?')) {
        fetch('<?= \App\Helpers\Url::appUrl() ?>/admin/system/optimize-database', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Database optimization completed!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function cleanupLogs() {
    if (confirm('Are you sure you want to cleanup old logs?')) {
        fetch('<?= \App\Helpers\Url::appUrl() ?>/admin/system/cleanup-logs', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Log cleanup completed!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function cleanupTempFiles() {
    if (confirm('Are you sure you want to cleanup temporary files?')) {
        fetch('<?= \App\Helpers\Url::appUrl() ?>/admin/system/cleanup-temp', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Temporary files cleanup completed!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function checkUpdates() {
    fetch('<?= \App\Helpers\Url::appUrl() ?>/admin/system/check-updates', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Update check completed!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function downloadBackup(backupId) {
    window.location.href = '<?= \App\Helpers\Url::appUrl() ?>/admin/system/download-backup/' + backupId;
}

function restoreBackup(backupId) {
    if (confirm('Are you sure you want to restore this backup? This will overwrite current data.')) {
        fetch('<?= \App\Helpers\Url::appUrl() ?>/admin/system/restore-backup/' + backupId, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Backup restored successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function deleteBackup(backupId) {
    if (confirm('Are you sure you want to delete this backup?')) {
        fetch('<?= \App\Helpers\Url::appUrl() ?>/admin/system/delete-backup/' + backupId, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Backup deleted successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}
</script>
</body>
</html> 