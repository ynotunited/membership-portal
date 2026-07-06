<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body class="d-flex">
<?php include __DIR__ . '/../partials/sidebar.php'; ?>
<div class="flex-grow-1">
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-bell"></i> Notification Center</h2>
            <div>
                <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#sendNotificationModal">
                    <i class="fas fa-paper-plane"></i> Send Notification
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#emailCampaignModal">
                    <i class="fas fa-envelope"></i> Email Campaign
                </button>
            </div>
        </div>

        <!-- Notification Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?= $totalNotifications ?? 0 ?></h4>
                                <p class="mb-0">Total Sent</p>
                            </div>
                            <div>
                                <i class="fas fa-bell fa-2x opacity-75"></i>
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
                                <h4><?= $readNotifications ?? 0 ?></h4>
                                <p class="mb-0">Read</p>
                            </div>
                            <div>
                                <i class="fas fa-eye fa-2x opacity-75"></i>
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
                                <h4><?= $pendingNotifications ?? 0 ?></h4>
                                <p class="mb-0">Pending</p>
                            </div>
                            <div>
                                <i class="fas fa-clock fa-2x opacity-75"></i>
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
                                <h4><?= $emailCampaigns ?? 0 ?></h4>
                                <p class="mb-0">Email Campaigns</p>
                            </div>
                            <div>
                                <i class="fas fa-envelope fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <button class="btn btn-outline-primary w-100" onclick="sendRenewalReminder()">
                                    <i class="fas fa-calendar-check"></i><br>
                                    Renewal Reminders
                                </button>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button class="btn btn-outline-success w-100" onclick="sendEventNotification()">
                                    <i class="fas fa-calendar-alt"></i><br>
                                    Event Notifications
                                </button>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button class="btn btn-outline-warning w-100" onclick="sendPaymentReminder()">
                                    <i class="fas fa-money-bill"></i><br>
                                    Payment Reminders
                                </button>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button class="btn btn-outline-info w-100" onclick="sendWelcomeMessage()">
                                    <i class="fas fa-user-plus"></i><br>
                                    Welcome Messages
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-table"></i> Recent Notifications</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="notificationsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Subject</th>
                                <th>Recipients</th>
                                <th>Status</th>
                                <th>Sent Date</th>
                                <th>Read Rate</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($notifications)): ?>
                                <?php foreach ($notifications as $notification): ?>
                                    <tr>
                                        <td><?= $notification['id'] ?></td>
                                        <td>
                                            <span class="badge bg-<?= $notification['type'] === 'email' ? 'primary' : ($notification['type'] === 'sms' ? 'success' : 'warning') ?>">
                                                <?= ucfirst($notification['type']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($notification['subject']) ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars(substr($notification['message'], 0, 50)) ?>...</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= $notification['recipient_count'] ?> recipients</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $notification['status'] === 'sent' ? 'success' : ($notification['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                                <?= ucfirst($notification['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('M d, Y H:i', strtotime($notification['sent_at'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: <?= $notification['read_rate'] ?>%">
                                                    <?= $notification['read_rate'] ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-primary" onclick="viewNotification(<?= $notification['id'] ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success" onclick="resendNotification(<?= $notification['id'] ?>)">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteNotification(<?= $notification['id'] ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Send Notification Modal -->
<div class="modal fade" id="sendNotificationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="sendNotificationForm" method="POST" action="<?= \App\Helpers\Url::appUrl() ?>/admin/notifications/send">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="notificationType" class="form-label">Notification Type</label>
                                <select class="form-select" id="notificationType" name="type" required>
                                    <option value="email">Email</option>
                                    <option value="sms">SMS</option>
                                    <option value="in_app">In-App</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select class="form-select" id="priority" name="priority">
                                    <option value="low">Low</option>
                                    <option value="normal" selected>Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="recipients" class="form-label">Recipients</label>
                        <select class="form-select" id="recipients" name="recipients" required>
                            <option value="all">All Members</option>
                            <option value="active">Active Members Only</option>
                            <option value="inactive">Inactive Members</option>
                            <option value="custom">Custom Selection</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="scheduledAt" class="form-label">Schedule (Optional)</label>
                        <input type="datetime-local" class="form-control" id="scheduledAt" name="scheduled_at">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Notification</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#notificationsTable').DataTable({
        order: [[5, 'desc']],
        pageLength: 25,
        responsive: true
    });
});

function sendRenewalReminder() {
    // Auto-fill renewal reminder form
    $('#subject').val('Membership Renewal Reminder');
    $('#message').val('Dear member, your membership is due for renewal. Please log in to your account to renew your membership.');
    $('#sendNotificationModal').modal('show');
}

function sendEventNotification() {
    $('#subject').val('Upcoming Event Notification');
    $('#message').val('Dear member, we have an upcoming event that you might be interested in. Please check our events calendar for details.');
    $('#sendNotificationModal').modal('show');
}

function sendPaymentReminder() {
    $('#subject').val('Payment Reminder');
    $('#message').val('Dear member, please note that you have outstanding payments. Please log in to your account to make the payment.');
    $('#sendNotificationModal').modal('show');
}

function sendWelcomeMessage() {
    $('#subject').val('Welcome to Our Organization');
    $('#message').val('Welcome! Thank you for joining our organization. We are excited to have you as a member.');
    $('#sendNotificationModal').modal('show');
}

function viewNotification(id) {
    window.location.href = '<?= \App\Helpers\Url::appUrl() ?>/admin/notifications/view/' + id;
}

function resendNotification(id) {
    if (confirm('Are you sure you want to resend this notification?')) {
        fetch('<?= \App\Helpers\Url::appUrl() ?>/admin/notifications/resend/' + id, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function deleteNotification(id) {
    if (confirm('Are you sure you want to delete this notification?')) {
        fetch('<?= \App\Helpers\Url::appUrl() ?>/admin/notifications/delete/' + id, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
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