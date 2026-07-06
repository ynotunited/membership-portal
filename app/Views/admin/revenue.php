<?php
// Set page variables for the layout
$title = 'Revenue Report';
$pageTitle = 'Revenue Report';
$activePage = 'revenue';

// Start output buffering to capture the content
ob_start();
?>

<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
        <svg class="w-8 h-8 text-primary/50 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z"></path>
        </svg>
        Revenue Report
    </h1>
    <p class="mt-2 text-gray-600">Generate comprehensive revenue reports and financial analytics</p>
</div>

<!-- Action Buttons -->
<?php if ($reportGenerated && !empty($revenueData)): ?>
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <button
            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors"
            onclick="window.location.href='<?= \App\Helpers\Url::appUrl() ?>/revenue/export?fromDate=<?= urlencode($fromDate) ?>&toDate=<?= urlencode($toDate) ?>&reportType=<?= urlencode($reportType ?? 'all') ?>'">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"></path>
            </svg>
            Export CSV
        </button>
        <button
            class="inline-flex items-center px-4 py-2 bg-secondary text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors"
            onclick="window.print()">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M6 9V2h12v7M6 18v2a2 2 0 002 2h8a2 2 0 002-2v-2M6 14h12v4H6v-4z"></path>
            </svg>
            Print Report
        </button>
    </div>
<?php endif; ?>

<!-- Report Filter Form -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Report Filters</h3>
    <form method="POST" action="<?= \App\Helpers\Url::appUrl() ?>/revenue"
        class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label for="fromDate" class="block text-sm font-medium text-gray-700 mb-2">From Date <span
                    class="text-red-500">*</span></label>
            <input type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                id="fromDate" name="fromDate" value="<?= htmlspecialchars($fromDate) ?>" required>
        </div>
        <div>
            <label for="toDate" class="block text-sm font-medium text-gray-700 mb-2">To Date <span
                    class="text-red-500">*</span></label>
            <input type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                id="toDate" name="toDate" value="<?= htmlspecialchars($toDate) ?>" required>
        </div>
        <div>
            <label for="reportType" class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
            <select
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                id="reportType" name="reportType">
                <option value="all" <?= ($reportType ?? 'all') === 'all' ? 'selected' : '' ?>>All Revenue</option>
                <option value="registration" <?= ($reportType ?? '') === 'registration' || ($reportType ?? '') === 'renewal' ? 'selected' : '' ?>>Registration</option>
                <option value="dues" <?= ($reportType ?? '') === 'dues' ? 'selected' : '' ?>>Annual Dues</option>
                <option value="shares" <?= ($reportType ?? '') === 'shares' ? 'selected' : '' ?>>Shares</option>
                <option value="thrift" <?= ($reportType ?? '') === 'thrift' ? 'selected' : '' ?>>Thrift</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit"
                class="w-full inline-flex justify-center items-center px-4 py-2 bg-secondary text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Generate Report
            </button>
        </div>
    </form>
</div>

<?php if ($reportGenerated): ?>
    <?php if (!empty($revenueData)): ?>
        <!-- Summary Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-primary/5 rounded-lg p-4 text-center">
                <h4 class="text-2xl font-bold text-secondary">₦<?= number_format($summary['total_revenue'], 2) ?></h4>
                <p class="text-primary-dark font-medium mt-1">Total Revenue</p>
            </div>
            <div class="bg-green-50 rounded-lg p-4 text-center">
                <h4 class="text-2xl font-bold text-green-600">₦<?= number_format($summary['registration_revenue'], 2) ?></h4>
                <p class="text-green-700 font-medium mt-1">Registration</p>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4 text-center">
                <h4 class="text-2xl font-bold text-yellow-600">₦<?= number_format($summary['dues_revenue'], 2) ?></h4>
                <p class="text-yellow-700 font-medium mt-1">Annual Dues</p>
            </div>
            <div class="bg-indigo-50 rounded-lg p-4 text-center">
                <h4 class="text-2xl font-bold text-indigo-600">₦<?= number_format($summary['shares_revenue'], 2) ?></h4>
                <p class="text-indigo-700 font-medium mt-1">Shares</p>
            </div>
            <div class="bg-purple-50 rounded-lg p-4 text-center">
                <h4 class="text-2xl font-bold text-purple-600">₦<?= number_format($summary['thrift_revenue'], 2) ?></h4>
                <p class="text-purple-700 font-medium mt-1">Thrift</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center">
                <h4 class="text-2xl font-bold text-gray-600"><?= number_format($summary['total_transactions']) ?></h4>
                <p class="text-gray-700 font-medium mt-1">Transactions</p>
            </div>
            <div class="bg-pink-50 rounded-lg p-4 text-center">
                <h4 class="text-2xl font-bold text-pink-600">
                    ₦<?= number_format($summary['total_revenue'] / max($summary['total_transactions'], 1), 2) ?></h4>
                <p class="text-pink-700 font-medium mt-1">Avg/Transaction</p>
            </div>
        </div>

        <!-- Revenue Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Revenue Details</h3>
                <div class="mt-4 md:mt-0">
                    <input type="text" id="searchRevenue" placeholder="Search revenue..."
                        class="w-full md:w-64 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="revenueTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Membership Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue
                                Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment
                                Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received
                                By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $counter = 1;
                        foreach ($revenueData as $revenue): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $counter++ ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?= htmlspecialchars($revenue['fullname'] ?? 'N/A') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    <?= htmlspecialchars($revenue['membership_number'] ?? 'N/A') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php
                                    if ($revenue['revenue_type'] === 'Registration' || $revenue['revenue_type'] === 'Renewal')
                                        echo 'bg-green-100 text-green-800';
                                    elseif ($revenue['revenue_type'] === 'Annual Dues')
                                        echo 'bg-yellow-100 text-yellow-800';
                                    elseif ($revenue['revenue_type'] === 'Thrift')
                                        echo 'bg-purple-100 text-purple-800';
                                    else
                                        echo 'bg-indigo-100 text-indigo-800';
                                    ?>">
                                        <?= htmlspecialchars($revenue['revenue_type']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        ₦<?= number_format($revenue['total_amount'] ?? 0, 2) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo ($revenue['payment_type'] ?? '') === 'paystack' ? 'bg-primary/10 text-blue-800' : 'bg-gray-100 text-gray-800'; ?>">
                                        <?= ucfirst(htmlspecialchars($revenue['payment_type'] ?? 'manual')) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?= htmlspecialchars($revenue['cash_received_by'] ?? 'N/A') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?= date('M d, Y H:i', strtotime($revenue['renew_date'] ?? '')) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-primary/5 border border-primary/20 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-blue-800">No revenue data found for the selected date range.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<script>
    // Search functionality
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchRevenue');
        const table = document.getElementById('revenueTable');

        if (searchInput && table) {
            searchInput.addEventListener('input', function () {
                const filter = this.value.toLowerCase();
                const rows = table.querySelectorAll('tbody tr');

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(filter) ? '' : 'none';
                });
            });
        }
    });
</script>

<?php
// Capture the content and include the layout
$content = ob_get_clean();
include __DIR__ . '/../layouts/admin.php';
?>