<?php
$title = 'Edit Share Purchase';
$pageTitle = 'Edit Share Purchase';
$activePage = 'shares';
// Calculate current price per share
$currentPrice = ($share['number_of_shares'] > 0) ? round($share['amount'] / $share['number_of_shares']) : 100;
ob_start();
?>

<div class="mb-8">
    <div class="flex items-center">
        <a href="<?= \App\Helpers\Url::appUrl() ?>/shares" class="text-secondary hover:text-blue-800">
            <i class="ri-arrow-left-line text-xl"></i>
        </a>
        <h3 class="ml-3 text-lg font-semibold text-gray-900">Edit Share Purchase</h3>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-2xl mx-auto">
    <form action="<?= \App\Helpers\Url::appUrl() ?>/shares/edit?id=<?= $share['id'] ?>" method="POST">
        <div class="space-y-6">
            <!-- Amount Per Share -->
            <div>
                <label for="price_per_share" class="block text-sm font-medium text-gray-700 mb-2">Price Per Share
                    (₦)</label>
                <select id="price_per_share" name="price_per_share"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    onchange="calculateTotal()">
                    <?php for ($i = 10; $i <= 200; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $currentPrice ? 'selected' : '' ?>>₦<?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- Read-only Member Info -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Member</label>
                <input type="text" readonly
                    value="<?= htmlspecialchars(($member['firstname'] ?? 'Unknown') . ' ' . ($member['surname'] ?? 'Member') . ' (' . ($member['membership_number'] ?? 'N/A') . ')') ?>"
                    class="w-full px-3 py-2 border border-gray-200 bg-gray-50 text-gray-500 rounded-md focus:outline-none">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Number of Shares -->
                <div>
                    <label for="number_of_shares" class="block text-sm font-medium text-gray-700 mb-2">Number of
                        Shares</label>
                    <input type="number" id="number_of_shares" name="number_of_shares" min="1" required
                        value="<?= htmlspecialchars($share['number_of_shares']) ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                        oninput="calculateTotal()">
                </div>

                <!-- Total Amount -->
                <div>
                    <label for="total_amount" class="block text-sm font-medium text-gray-700 mb-2">Total Amount
                        (₦)</label>
                    <input type="number" id="amount" name="amount" readonly
                        value="<?= htmlspecialchars($share['amount']) ?>"
                        class="w-full px-3 py-2 border border-gray-200 bg-gray-50 text-gray-500 rounded-md focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Payment Method -->
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">Payment
                        Method</label>
                    <select id="payment_method" name="payment_method"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="cash" <?= ($share['payment_method'] ?? '') === 'cash' ? 'selected' : '' ?>>Cash
                        </option>
                        <option value="bank_transfer" <?= ($share['payment_method'] ?? '') === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                        <option value="manual" <?= ($share['payment_method'] ?? '') === 'manual' ? 'selected' : '' ?>>
                            Manual</option>
                        <option value="online" <?= ($share['payment_method'] ?? '') === 'online' ? 'selected' : '' ?>>
                            Online</option>
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="status" name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="paid" <?= ($share['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                        <option value="pending" <?= ($share['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending
                        </option>
                        <option value="failed" <?= ($share['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed
                        </option>
                    </select>
                </div>
            </div>

            <!-- Purchase Date -->
            <div>
                <label for="purchase_date" class="block text-sm font-medium text-gray-700 mb-2">Purchase Date</label>
                <input type="date" id="purchase_date" name="purchase_date"
                    value="<?= htmlspecialchars(date('Y-m-d', strtotime($share['purchase_date']))) ?>" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea id="notes" name="notes" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"><?= htmlspecialchars($share['notes'] ?? '') ?></textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-4">
                <button type="submit"
                    class="px-6 py-2 bg-secondary text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                    Update Shares
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function calculateTotal() {
        const shares = parseFloat(document.getElementById('number_of_shares').value) || 0;
        const pricePerShare = parseFloat(document.getElementById('price_per_share').value) || 0;
        const total = shares * pricePerShare;
        document.getElementById('amount').value = total.toFixed(2);
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>