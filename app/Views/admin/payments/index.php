<?php
$title = 'Payment Approvals';
$pageTitle = 'Payment Approvals';
$activePage = 'payments';
ob_start();
?>

<div class="mb-8">
    <div class="flex items-center">
        <a href="<?= \App\Helpers\Url::appUrl() ?>/dashboard" class="text-secondary hover:text-blue-800">
            <i class="ri-arrow-left-line text-xl"></i>
        </a>
        <h3 class="ml-3 text-lg font-semibold text-gray-900">Payment Approvals</h3>
    </div>

    <div class="mt-6">
        <h1 class="text-3xl font-bold text-gray-900">Pending Manual Payments</h1>
        <p class="mt-2 text-gray-600">Review and approve off-line manual/bank transfer payments.</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Pending Transactions List</h2>
    </div>

    <?php if (empty($payments)): ?>
        <div class="text-center py-12">
            <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                <i class="ri-check-double-line text-4xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No pending payments</h3>
            <p class="text-gray-500 mb-6">All manual payments have been processed.</p>
        </div>
    <?php else: ?>

        <div class="responsive-table-container">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Member</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Ref</th>
                        <th>Gateway</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td data-label="Date">
                                <?= date('M d, Y H:i', strtotime($payment['created_at'])) ?>
                            </td>
                            <td data-label="Member">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900">
                                        <?= htmlspecialchars(($payment['firstname'] ?? '') . ' ' . ($payment['surname'] ?? '')) ?>
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        <?= htmlspecialchars($payment['membership_number'] ?? 'N/A') ?>
                                    </span>
                                </div>
                            </td>
                            <td data-label="Type">
                                <?php
                                $typeFormatted = ucwords(str_replace('_', ' ', $payment['payment_type'] ?? 'general'));
                                ?>
                                <span class="text-sm font-medium text-gray-700">
                                    <?= htmlspecialchars($typeFormatted) ?>
                                </span>
                            </td>
                            <td data-label="Amount">
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    ₦
                                    <?= number_format(($payment['amount'] ?? 0) / 100, 2) ?>
                                </span>
                            </td>
                            <td data-label="Ref">
                                <code
                                    class="text-xs bg-gray-100 px-1 rounded"><?= htmlspecialchars($payment['reference'] ?? '') ?></code>
                            </td>
                            <td data-label="Gateway">
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    <?= ucfirst(htmlspecialchars($payment['gateway'] ?? 'manual')) ?>
                                </span>
                            </td>
                            <td data-label="Actions" class="actions">
                                <div class="flex flex-wrap gap-2">
                                    <form action="<?= \App\Helpers\Url::appUrl() ?>/admin/payments/approve" method="POST"
                                        onsubmit="return confirm('Are you sure you want to approve this payment?');">
                                        <input type="hidden" name="payment_id" value="<?= $payment['id'] ?>">
                                        <button type="submit"
                                            class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 rounded-md hover:bg-green-200 transition-colors text-sm">
                                            <i class="ri-check-line mr-1"></i> Approve
                                        </button>
                                    </form>
                                    <form action="<?= \App\Helpers\Url::appUrl() ?>/admin/payments/reject" method="POST"
                                        onsubmit="return confirm('Are you sure you want to REJECT this payment?');">
                                        <input type="hidden" name="payment_id" value="<?= $payment['id'] ?>">
                                        <button type="submit"
                                            class="inline-flex items-center px-3 py-1 bg-red-100 text-red-800 rounded-md hover:bg-red-200 transition-colors text-sm">
                                            <i class="ri-close-line mr-1"></i> Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
// Capture the content and include the layout
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>