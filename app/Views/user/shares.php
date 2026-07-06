<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Buy Shares</h1>
            <p class="text-gray-600">Purchase cooperative shares and track your investments</p>
        </div>
    </div>
    <?php if (empty($sharesHistory)): ?>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
            <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                <i class="ri-pie-chart-line text-4xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No shares purchased yet</h3>
            <p class="text-gray-500 mb-6">You haven't purchased any shares yet.</p>
            <a href="<?= \App\Helpers\Url::appUrl() ?>/member/shares/pay"
                class="inline-flex items-center px-4 py-2 bg-secondary text-white rounded-md hover:bg-blue-700">
                <i class="ri-add-line mr-2"></i> Buy Shares
            </a>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Shares History</h3>
                    <p class="text-sm text-gray-500 mt-1">View your share purchase history</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <div class="bg-primary/5 rounded-lg px-4 py-2">
                        <p class="text-xs text-gray-500">Total Shares</p>
                        <p class="text-lg font-semibold text-gray-900"><?= number_format($totalShares) ?></p>
                    </div>
                    <div class="bg-green-50 rounded-lg px-4 py-2">
                        <p class="text-xs text-gray-500">Total Value</p>
                        <p class="text-lg font-semibold text-green-700">₦<?= number_format($totalShares * 100, 2) ?></p>
                    </div>
                </div>
            </div>

            <div class="responsive-table-container">
                <table class="responsive-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Shares</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sharesHistory as $share): ?>
                            <tr>
                                <td data-label="Date">
                                    <?= date('M d, Y', strtotime($share['purchase_date'])) ?>
                                </td>
                                <td data-label="Shares">
                                    <span class="font-medium text-gray-900">
                                        <?= number_format($share['number_of_shares']) ?>
                                    </span>
                                </td>
                                <td data-label="Amount">
                                    <span class="font-medium text-gray-900">
                                        ₦<?= number_format($share['amount'], 2) ?>
                                    </span>
                                </td>
                                <td data-label="Status">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Paid
                                    </span>
                                </td>
                                <td data-label="Notes">
                                    <span class="text-sm text-gray-500">
                                        <?= htmlspecialchars($share['notes'] ?? 'N/A') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Purchase More Shares</h3>
                <p class="text-sm text-gray-500 mt-1">Invest in additional shares to increase your stake</p>
            </div>
            <a href="<?= \App\Helpers\Url::appUrl() ?>/member/shares/pay"
                class="px-6 py-3 bg-secondary text-white rounded-lg hover:bg-blue-700 transition-colors font-medium whitespace-nowrap">
                <i class="ri-add-line mr-2"></i> Buy Shares
            </a>
        </div>
    </div>
</div>