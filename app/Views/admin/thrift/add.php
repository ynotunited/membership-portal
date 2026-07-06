<?php
$title = 'Add Thrift Savings';
$pageTitle = 'Add Thrift Savings';
$activePage = 'thrift';
ob_start();
?>

<div class="mb-8">
    <div class="flex items-center">
        <a href="<?= \App\Helpers\Url::appUrl() ?>/thrift" class="text-secondary hover:text-blue-800">
            <i class="ri-arrow-left-line text-xl"></i>
        </a>
        <h3 class="ml-3 text-lg font-semibold text-gray-900">Add Thrift Savings</h3>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-2xl mx-auto">
    <form action="<?= \App\Helpers\Url::appUrl() ?>/thrift/add" method="POST">
        <div class="space-y-6">
            <!-- Member Selection -->
            <div>
                <label for="member_id" class="block text-sm font-medium text-gray-700 mb-2">Member</label>
                <select id="member_id" name="member_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    <option value="">Select Member</option>
                    <?php foreach ($members as $member): ?>
                        <option value="<?= $member['id'] ?>">
                            <?= htmlspecialchars($member['firstname'] . ' ' . $member['surname'] . ' (' . ($member['membership_number'] ?? 'N/A') . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Amount -->
            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount (₦)</label>
                <input type="number" id="amount" name="amount" min="100" step="0.01" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
            </div>

            <!-- Payment Date -->
            <div>
                <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">Payment Date</label>
                <input type="date" id="payment_date" name="payment_date" value="<?= date('Y-m-d') ?>" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-4">
                <button type="submit"
                    class="px-6 py-2 bg-secondary text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                    Record Savings
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        new TomSelect('#member_id', {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            },
            placeholder: "Search for a member...",
            allowEmptyOption: true
        });
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>