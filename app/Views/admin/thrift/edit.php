<?php
$title = 'Edit Thrift Savings';
$pageTitle = 'Edit Thrift Savings';
$activePage = 'thrift';
ob_start();
?>

<div class="mb-8">
    <div class="flex items-center">
        <a href="<?= \App\Helpers\Url::appUrl() ?>/thrift" class="text-secondary hover:text-blue-800">
            <i class="ri-arrow-left-line text-xl"></i>
        </a>
        <h3 class="ml-3 text-lg font-semibold text-gray-900">Edit Thrift Savings</h3>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-2xl mx-auto">
    <form action="<?= \App\Helpers\Url::appUrl() ?>/thrift/edit?id=<?= $saving['id'] ?>" method="POST">
        <div class="space-y-6">
            <!-- Read-only Member Info -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Member</label>
                <input type="text" readonly
                    value="<?= htmlspecialchars(($member['firstname'] ?? 'Unknown') . ' ' . ($member['surname'] ?? 'Member') . ' (' . ($member['membership_number'] ?? 'N/A') . ')') ?>"
                    class="w-full px-3 py-2 border border-gray-200 bg-gray-50 text-gray-500 rounded-md focus:outline-none">
            </div>

            <!-- Amount -->
            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount (₦)</label>
                <input type="number" id="amount" name="amount" min="100" step="0.01" required
                    value="<?= htmlspecialchars($saving['amount']) ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
            </div>

            <!-- Payment Date -->
            <div>
                <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">Payment Date</label>
                <input type="date" id="payment_date" name="payment_date"
                    value="<?= htmlspecialchars(date('Y-m-d', strtotime($saving['payment_date']))) ?>" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-4">
                <button type="submit"
                    class="px-6 py-2 bg-secondary text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                    Update Savings
                </button>
            </div>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>