<?php
// Set page variables for the layout
$title = 'Reports & Analytics';
$pageTitle = 'Reports & Analytics';
$activePage = 'reports';

// Map friendly names
$reportLabels = [
    'all' => 'Financial Overview',
    'dues' => 'Annual Dues Report',
    'shares' => 'Shares Report',
    'thrift' => 'Thrift Savings Report',
    'project' => 'Rice Project Report',
    'membership' => 'Membership Analysis'
];
$currentReportLabel = $reportLabels[$reportType] ?? 'Report';

ob_start();
?>

<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
        <svg class="w-8 h-8 text-primary/50 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z">
            </path>
        </svg>
        <?= htmlspecialchars($currentReportLabel) ?>
    </h1>
    <p class="mt-2 text-gray-600">Analyze financial data and membership activities.</p>
</div>

<!-- Report Filters -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <form id="reportFilters" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div class="md:col-span-1">
            <label for="reportType" class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
            <select
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                id="reportType" name="reportType" onchange="this.form.submit()">
                <option value="all" <?= $reportType === 'all' || $reportType === 'financial' ? 'selected' : '' ?>>All
                    (Financial Overview)</option>
                <option value="dues" <?= $reportType === 'dues' ? 'selected' : '' ?>>Annual Dues</option>
                <option value="shares" <?= $reportType === 'shares' ? 'selected' : '' ?>>Shares</option>
                <option value="thrift" <?= $reportType === 'thrift' ? 'selected' : '' ?>>Thrift Savings</option>
                <option value="project" <?= $reportType === 'project' ? 'selected' : '' ?>>Rice Project</option>
                <option value="membership" <?= $reportType === 'membership' ? 'selected' : '' ?>>Membership Analysis
                </option>
            </select>
        </div>
        <div>
            <label for="dateFrom" class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
            <input type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                id="dateFrom" name="dateFrom" value="<?= htmlspecialchars($dateFrom) ?>">
        </div>
        <div>
            <label for="dateTo" class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
            <input type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                id="dateTo" name="dateTo" value="<?= htmlspecialchars($dateTo) ?>">
        </div>
        <div class="flex gap-2">
            <button type="submit"
                class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-primary text-white rounded-lg font-medium hover:bg-primary-dark transition-colors">
                Apply Filters
            </button>
            <a href="<?= \App\Helpers\Url::appUrl() ?>/reports/export?<?= http_build_query($_GET) ?>" target="_blank"
                class="inline-flex justify-center items-center px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors"
                title="Export to CSV">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"></path>
                </svg>
            </a>
        </div>
    </form>
</div>

<!-- DYNAMIC CONTENT SECTIONS -->

<?php if ($reportType === 'all' || $reportType === 'financial'): ?>
    <!-- FINANCIAL OVERVIEW (CHARTS) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h5 class="font-semibold text-gray-800 mb-4">Revenue Overview</h5>
            <div class="h-64">
                <canvas id="financialChart"></canvas>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const ctx = document.getElementById('financialChart').getContext('2d');
                    const data = <?= json_encode($financialData ?? []) ?>;
                    const labels = data.map(d => d.month);
                    const totalRevenue = data.map(d => parseFloat(d.total_revenue));

                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Total Revenue (<?= $currency ?? '₦' ?>)',
                                data: totalRevenue,
                                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                                borderColor: 'rgb(59, 130, 246)',
                                borderWidth: 1
                            }]
                        },
                        options: { responsive: true, maintainAspectRatio: false }
                    });
                });
            </script>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 overflow-x-auto">
            <h5 class="font-semibold text-gray-800 mb-4">Monthly Breakdown</h5>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month
                        </th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Dues
                        </th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Shares
                        </th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($financialData)): ?>
                        <?php foreach (array_reverse($financialData) as $row): ?>

                            <tr>
                                <td class="px-3 py-2 text-sm text-gray-900"><?= htmlspecialchars($row['month']) ?></td>
                                <td class="px-3 py-2 text-sm text-gray-500 text-right"><?= number_format($row['dues_revenue'], 2) ?>
                                </td>
                                <td class="px-3 py-2 text-sm text-gray-500 text-right">
                                    <?= number_format($row['shares_revenue'], 2) ?></td>
                                <td class="px-3 py-2 text-sm text-gray-900 font-medium text-right">
                                    <?= number_format($row['total_revenue'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-3 py-4 text-center text-sm text-gray-500">No data available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($reportType === 'dues'): ?>
    <!-- ANNUAL DUES TABLE -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ref No.
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($duesData)): ?>
                        <?php foreach ($duesData as $d): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M d, Y', strtotime($d['payment_date'] ?? $d['created_at'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars(($d['firstname'] ?? '') . ' ' . ($d['surname'] ?? '')) ?></div>
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($d['email'] ?? '') ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($d['membership_number'] ?? 'N/A') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= number_format($d['amount'], 2) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $status = strtolower($d['status'] ?? '');
                                    if (in_array($status, ['paid', 'success', 'approved'])) {
                                        $statusClass = 'bg-green-100 text-green-800';
                                    } elseif ($status === 'pending') {
                                        $statusClass = 'bg-yellow-100 text-yellow-800';
                                    } else {
                                        $statusClass = 'bg-gray-100 text-gray-800';
                                    }
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                        <?= htmlspecialchars(ucfirst($d['status'] ?? 'Pending')) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">No annual dues records found for this
                                period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($reportType === 'shares'): ?>
    <!-- SHARES TABLE -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shares
                            Qty</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($sharesData)): ?>
                        <?php foreach ($sharesData as $s): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M d, Y', strtotime($s['purchase_date'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars(($s['firstname'] ?? '') . ' ' . ($s['surname'] ?? '')) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($s['number_of_shares']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= number_format($s['amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500">No shares records found for this
                                period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($reportType === 'thrift'): ?>
    <!-- THRIFT TABLE -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Membership No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount
                            Saved</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($thriftData)): ?>
                        <?php foreach ($thriftData as $t): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M d, Y', strtotime($t['payment_date'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars(($t['firstname'] ?? '') . ' ' . ($t['surname'] ?? '')) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($t['membership_number'] ?? 'N/A') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                    +<?= number_format($t['amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500">No thrift savings records found for
                                this period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($reportType === 'project'): ?>
    <!-- RICE PROJECT TABLE -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($projectData)): ?>
                        <?php foreach ($projectData as $p): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M d, Y', strtotime($p['created_at'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars(($p['firstname'] ?? '') . ' ' . ($p['surname'] ?? '')) ?></div>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($p['membership_number'] ?? '') ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= number_format($p['amount'], 2) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $status = strtolower($p['status'] ?? '');
                                    if (in_array($status, ['approved', 'paid', 'success'])) {
                                        $statusClass = 'bg-green-100 text-green-800';
                                    } elseif ($status === 'pending') {
                                        $statusClass = 'bg-yellow-100 text-yellow-800';
                                    } elseif ($status === 'rejected') {
                                        $statusClass = 'bg-red-100 text-red-800';
                                    } else {
                                        $statusClass = 'bg-gray-100 text-gray-800';
                                    }
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                        <?= htmlspecialchars(ucfirst($p['status'] ?? 'Pending')) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 truncate max-w-xs">
                                    <?= htmlspecialchars($p['notes'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">No project investments found for this
                                period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($reportType === 'membership'): ?>
    <!-- Member demography charts... keeping simple stats for now -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 rounded-lg p-4 text-center">
                <h3 class="text-blue-600 text-2xl font-bold"><?= $totalMembers ?? 0 ?></h3>
                <p class="text-blue-700 font-medium">Total Members</p>
            </div>
            <div class="bg-green-50 rounded-lg p-4 text-center">
                <h3 class="text-green-600 text-2xl font-bold"><?= $activeMembers ?? 0 ?></h3>
                <p class="text-green-700 font-medium">Active Members</p>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4 text-center">
                <h3 class="text-yellow-600 text-2xl font-bold"><?= $newMembersThisMonth ?? 0 ?></h3>
                <p class="text-yellow-700 font-medium">New This Month</p>
            </div>
            <div class="bg-pink-50 rounded-lg p-4 text-center">
                <h3 class="text-pink-600 text-2xl font-bold"><?= $renewalRate ?? 0 ?>%</h3>
                <p class="text-pink-700 font-medium">Renewal Rate</p>
            </div>
        </div>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/admin.php';
?>