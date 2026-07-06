<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Annual Dues</h1>
            <p class="text-gray-600">Manage your annual membership dues payments</p>
        </div>
    </div>
    <?php if (empty($duesHistory)): ?>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
            <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                <i class="ri-wallet-3-line text-4xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No dues payments yet</h3>
            <p class="text-gray-500 mb-6">You haven't made any annual dues payments yet.</p>
            <?php if (($user['annual_dues_status'] ?? 'unpaid') === 'unpaid'): ?>
                <a href="<?= \App\Helpers\Url::appUrl() ?>/member/dues/pay"
                    class="inline-flex items-center px-4 py-2 bg-secondary text-white rounded-md hover:bg-blue-700">
                    <i class="ri-wallet-3-line mr-2"></i> Pay Annual Dues
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Payment History</h3>
                    <p class="text-sm text-gray-500 mt-1">View your annual dues payment history</p>
                </div>
                <a href="<?= \App\Helpers\Url::appUrl() ?>/member/dues/download-statement"
                    class="inline-flex items-center px-4 py-2 bg-secondary text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                    <i class="ri-download-line mr-2"></i>Download Statement
                </a>
            </div>

            <div class="responsive-table-container">
                <table class="responsive-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Reference</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($duesHistory as $payment): ?>
                            <tr>
                                <td data-label="Date">
                                    <?= date('M d, Y', strtotime($payment['payment_date'])) ?>
                                </td>
                                <td data-label="Amount">
                                    <span class="font-medium text-gray-900">
                                        ₦<?= number_format($payment['amount'], 2) ?>
                                    </span>
                                </td>
                                <td data-label="Status">
                                    <?php
                                    $statusClass = '';
                                    $statusText = ucfirst($payment['status'] ?? 'pending');

                                    switch ($payment['status']) {
                                        case 'paid':
                                            $statusClass = 'bg-green-100 text-green-800';
                                            break;
                                        case 'failed':
                                            $statusClass = 'bg-red-100 text-red-800';
                                            break;
                                        case 'pending':
                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                            break;
                                        default:
                                            $statusClass = 'bg-gray-100 text-gray-800';
                                    }
                                    ?>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $statusClass ?>">
                                        <?= $statusText ?>
                                    </span>
                                </td>
                                <td data-label="Reference">
                                    <span class="font-mono text-sm text-gray-500">
                                        <?= htmlspecialchars($payment['reference'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td data-label="Notes">
                                    <?= htmlspecialchars($payment['notes'] ?? 'N/A') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Payment Action -->
    <?php if ($isOverdue): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-red-900">Dues Overdue</h3>
                    <p class="text-red-700 mt-1">Your annual dues are overdue. Please pay immediately to maintain active
                        membership status.</p>
                </div>
                <a href="<?= \App\Helpers\Url::appUrl() ?>/member/dues/pay"
                    class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium whitespace-nowrap">
                    Pay Overdue Dues - ₦12,000
                </a>
            </div>
        </div>
    <?php elseif (($user['annual_dues_status'] ?? 'unpaid') === 'unpaid'): ?>
        <div class="bg-primary/5 border border-primary/20 rounded-lg p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-blue-900">Pay Annual Dues</h3>
                    <p class="text-primary-dark mt-1">Complete your annual membership dues payment to maintain active status
                    </p>
                </div>
                <a href="<?= \App\Helpers\Url::appUrl() ?>/member/dues/pay"
                    class="px-6 py-3 bg-secondary text-white rounded-lg hover:bg-blue-700 transition-colors font-medium whitespace-nowrap">
                    Pay Now - ₦12,000
                </a>
            </div>
        </div>
    <?php elseif (($user['annual_dues_status'] ?? 'unpaid') === 'paid'): ?>
        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-green-900">Dues Paid</h3>
                    <p class="text-green-700 mt-1">Your annual dues are up to date. You can renew for next year when due.
                    </p>
                </div>
                <a href="<?= \App\Helpers\Url::appUrl() ?>/member/dues/pay"
                    class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium whitespace-nowrap">
                    Renew Early - ₦12,000
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>