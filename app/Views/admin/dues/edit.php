<?php
$title = 'Edit Dues Payment';
$pageTitle = 'Edit Dues Payment';
$activePage = 'dues';
ob_start();
?>

<div class="mb-8">
    <div class="flex items-center">
        <a href="<?= \App\Helpers\Url::appUrl() ?>/dues" class="text-secondary hover:text-blue-800">
            <i class="ri-arrow-left-line text-xl"></i>
        </a>
        <h3 class="ml-3 text-lg font-semibold text-gray-900">Edit Dues Payment</h3>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-2xl mx-auto">
    <form action="<?= \App\Helpers\Url::appUrl() ?>/dues/edit?id=<?= $due['id'] ?>" method="POST">
        <div class="space-y-6">
            <!-- Member (Read-only) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Member</label>
                <input type="text" readonly
                    value="<?= htmlspecialchars(($member['firstname'] ?? 'Unknown') . ' ' . ($member['surname'] ?? 'Member') . ' (' . ($member['membership_number'] ?? 'N/A') . ')') ?>"
                    class="w-full px-3 py-2 border border-gray-200 bg-gray-50 text-gray-500 rounded-md focus:outline-none">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Amount -->
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount (₦)</label>
                    <input type="number" id="amount" name="amount" min="0" step="0.01" required
                        value="<?= htmlspecialchars($due['amount']) ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                </div>

                <!-- Year -->
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                    <input type="number" id="year" name="year" min="2000" max="2100" required
                        value="<?= htmlspecialchars($due['year'] ?? date('Y')) ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Payment Method -->
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">Payment
                        Method</label>
                    <select id="payment_method" name="payment_method"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="cash" <?= ($due['payment_method'] ?? '') === 'cash' ? 'selected' : '' ?>>Cash
                        </option>
                        <option value="bank_transfer" <?= ($due['payment_method'] ?? '') === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                        <option value="manual" <?= ($due['payment_method'] ?? '') === 'manual' ? 'selected' : '' ?>>Manual
                        </option>
                        <option value="online" <?= ($due['payment_method'] ?? '') === 'online' ? 'selected' : '' ?>>Online
                        </option>
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="status" name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="paid" <?= ($due['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                        <option value="pending" <?= ($due['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending
                        </option>
                        <option value="failed" <?= ($due['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed
                        </option>
                    </select>
                </div>
            </div>

            <!-- Payment Date -->
            <div>
                <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">Payment Date</label>
                <input type="date" id="payment_date" name="payment_date"
                    value="<?= htmlspecialchars(date('Y-m-d', strtotime($due['payment_date'] ?? $due['paid_at'] ?? date('Y-m-d')))) ?>"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea id="notes" name="notes" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"><?= htmlspecialchars($due['notes'] ?? '') ?></textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-4">
                <button type="submit"
                    class="px-6 py-2 bg-secondary text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                    Update Dues
                </button>
            </div>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>