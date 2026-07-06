<?php
$title = 'Rice Project Investments';
$pageTitle = 'Rice Project Investments';
$activePage = 'rice_project';
ob_start();
?>

<div class="mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Rice Project Investments</h1>
            <p class="mt-2 text-gray-600">Review and approve investment submissions</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <?php if (empty($investments)): ?>
        <div class="text-center py-12">
            <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                <i class="ri-plant-off-line text-4xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No investments found</h3>
        </div>
    <?php else: ?>
        <div class="responsive-table-container">
            <table class="responsive-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Member</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Evidence</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($investments as $inv): ?>
                        <tr>
                            <td data-label="Date">
                                <?= date('M d, Y', strtotime($inv['created_at'])) ?>
                            </td>
                            <td data-label="Member">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900">
                                        <?= htmlspecialchars($inv['firstname'] . ' ' . $inv['surname']) ?>
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        <?= htmlspecialchars($inv['membership_number']) ?>
                                    </span>
                                </div>
                            </td>
                            <td data-label="Amount">
                                <span class="font-bold text-green-700">₦
                                    <?= number_format($inv['amount'], 2) ?>
                                </span>
                            </td>
                            <td data-label="Status">
                                <?php if ($inv['status'] === 'approved'): ?>
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                                <?php elseif ($inv['status'] === 'rejected'): ?>
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                                <?php else: ?>
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Evidence">
                                <?php if (!empty($inv['payment_proof'])): ?>
                                    <a href="<?= \App\Helpers\Url::appUrl() ?>/<?= htmlspecialchars($inv['payment_proof']) ?>"
                                        target="_blank" class="text-primary hover:underline text-sm flex items-center">
                                        <i class="ri-file-search-line mr-1"></i> View Receipt
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400 text-sm">No File</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Actions" class="actions">
                                <?php if ($inv['status'] === 'pending'): ?>
                                    <div class="flex space-x-2">
                                        <a href="<?= \App\Helpers\Url::appUrl() ?>/admin/rice-project/approve?id=<?= $inv['id'] ?>"
                                            onclick="return confirm('Are you sure you want to approve this investment?')"
                                            class="p-1 px-3 bg-green-100 text-green-700 rounded hover:bg-green-200 text-sm">Approve</a>

                                        <a href="<?= \App\Helpers\Url::appUrl() ?>/admin/rice-project/reject?id=<?= $inv['id'] ?>"
                                            onclick="return confirm('Are you sure you want to reject this investment?')"
                                            class="p-1 px-3 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm">Reject</a>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400 text-sm">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>