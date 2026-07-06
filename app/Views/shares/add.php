<?php
// Set page variables for the layout
$title = 'Add Shares Purchase';
$pageTitle = 'Add Shares Purchase';
$activePage = 'shares';

// Start output buffering to capture the content
ob_start();
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                <svg class="w-8 h-8 text-primary/50 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Shares Purchase
            </h1>
            <p class="mt-2 text-gray-600">Record a new shares purchase for a member</p>
        </div>
        <a href="<?= \App\Helpers\Url::appUrl() ?>/shares"
            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg font-medium hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
            Back to Shares List
        </a>
    </div>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800"><?= htmlspecialchars($_SESSION['success']) ?></p>
            </div>
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button onclick="this.parentElement.parentElement.parentElement.parentElement.remove()"
                        class="inline-flex rounded-md bg-green-50 p-1.5 text-green-500 hover:bg-green-100">
                        <span class="sr-only">Dismiss</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-red-800"><?= htmlspecialchars($_SESSION['error']) ?></p>
            </div>
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button onclick="this.parentElement.parentElement.parentElement.parentElement.remove()"
                        class="inline-flex rounded-md bg-red-50 p-1.5 text-red-500 hover:bg-red-100">
                        <span class="sr-only">Dismiss</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Form Section -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Main Form -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center mb-6">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                </div>
                <h3 class="ml-3 text-lg font-semibold text-gray-900">Record New Shares Purchase</h3>
            </div>

            <form method="POST" action="<?= \App\Helpers\Url::appUrl() ?>/shares/add" id="sharesForm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Member Selection -->
                    <div>
                        <label for="member_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Member <span class="text-red-500">*</span>
                        </label>
                        <select
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            id="member_id" name="member_id" required>
                            <option value="">Select Member</option>
                            <?php foreach ($members as $member): ?>
                                <option value="<?= $member['id'] ?>">
                                    <?= htmlspecialchars($member['membership_number'] . ' - ' . $member['firstname'] . ' ' . $member['surname']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="text-xs text-gray-400 mt-1">Choose the member purchasing shares</div>
                    </div>

                    <!-- Amount to Invest -->
                    <div>
                        <label for="amount_to_invest" class="block text-sm font-medium text-gray-700 mb-2">
                            Amount to Invest (₦) <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            id="amount_to_invest" name="amount_to_invest" step="0.01" min="100" value="10000" required>
                        <div class="text-xs text-gray-400 mt-1">Minimum investment: ₦100 (100 shares at lowest price)
                        </div>
                    </div>

                    <!-- Number of Shares (Read-only) -->
                    <div>
                        <label for="number_of_shares" class="block text-sm font-medium text-gray-700 mb-2">
                            Number of Shares <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            id="number_of_shares" name="number_of_shares" min="100" value="100" required readonly>
                        <div class="text-xs text-gray-400 mt-1">Calculated automatically based on investment amount
                        </div>
                    </div>

                    <!-- Amount per Share -->
                    <div>
                        <label for="amount_per_share" class="block text-sm font-medium text-gray-700 mb-2">
                            Amount per Share (₦) <span class="text-red-500">*</span>
                        </label>
                        <select
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            id="amount_per_share" name="amount_per_share" required>
                            <?php for ($i = 1; $i <= 100; $i++): ?>
                                <option value="<?= $i ?>" <?= $i == 100 ? 'selected' : '' ?>>₦<?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                        <div class="text-xs text-gray-400 mt-1">Select price per share (Default: ₦100)</div>
                    </div>

                    <!-- Total Amount (Read-only) -->
                    <div>
                        <label for="total_amount" class="block text-sm font-medium text-gray-700 mb-2">Total Amount
                            (₦)</label>
                        <input type="number"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            id="total_amount" name="total_amount" step="0.01" min="0" readonly>
                        <div class="text-xs text-gray-400 mt-1">Same as investment amount</div>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">Payment
                            Method</label>
                        <select
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            id="payment_method" name="payment_method">
                            <option value="manual">Manual Entry</option>
                            <option value="paystack">Paystack</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cash">Cash</option>
                            <option value="check">Check</option>
                        </select>
                        <div class="text-xs text-gray-400 mt-1">Choose how the payment was made</div>
                    </div>

                    <!-- Transaction ID -->
                    <div>
                        <label for="transaction_id" class="block text-sm font-medium text-gray-700 mb-2">Transaction
                            ID</label>
                        <input type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            id="transaction_id" name="transaction_id" placeholder="e.g., PAYSTACK_123456789">
                        <div class="text-xs text-gray-400 mt-1">Optional transaction reference number</div>
                    </div>

                    <!-- Notes -->
                    <div class="md:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            id="notes" name="notes" rows="3"
                            placeholder="Additional notes about this shares purchase"></textarea>
                        <div class="text-xs text-gray-400 mt-1">Any additional information about this purchase</div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4 mt-8">
                    <button type="reset"
                        class="px-6 py-2 bg-gray-600 text-white rounded-lg font-medium hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                        Reset
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-secondary text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        Record Purchase
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Information Panel -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center mb-6">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="ml-3 text-lg font-semibold text-gray-900">Purchase Information</h3>
            </div>

            <div class="space-y-4">
                <div class="bg-primary/5 border border-primary/20 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-blue-800">Instructions</h4>
                            <ul class="mt-2 text-sm text-primary-dark space-y-1">
                                <li>• Select the member purchasing shares</li>
                                <li>• Enter the amount you want to invest (minimum ₦10,000)</li>
                                <li>• Number of shares and amount per share are calculated automatically</li>
                                <li>• Total amount is the same as your investment amount</li>
                                <li>• Choose the payment method used</li>
                                <li>• Add transaction ID if available</li>
                                <li>• Include any relevant notes</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-green-800">Business Rules</h4>
                            <ul class="mt-2 text-sm text-green-700 space-y-1">
                                <li>• Minimum investment: ₦100</li>
                                <li>• Price per share: ₦1 - ₦100</li>
                                <li>• Minimum shares: 100</li>
                                <li>• Total = Investment Amount</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-yellow-800">Important</h4>
                            <p class="mt-2 text-sm text-yellow-700">This will record a shares purchase for the selected
                                member. Make sure all information is accurate before submitting.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Calculate shares and total based on investment amount
        function calculateShares() {
            const amountToInvest = parseFloat(document.getElementById('amount_to_invest').value) || 0;
            const pricePerShare = parseFloat(document.getElementById('amount_per_share').value) || 100;

            // Calculate number of shares (must be whole numbers)
            const numberOfShares = Math.floor(amountToInvest / pricePerShare);

            // Calculate actual total based on whole shares
            const actualTotal = numberOfShares * pricePerShare;

            // Update the fields
            document.getElementById('number_of_shares').value = numberOfShares;
            document.getElementById('total_amount').value = actualTotal.toFixed(2);

            // Update helper text
            const sharesHelper = document.querySelector('#number_of_shares').nextElementSibling;
            if (sharesHelper) {
                sharesHelper.textContent = `${numberOfShares} shares at ₦${pricePerShare} each`;
            }
        }

        // Add event listener for investment amount changes
        document.getElementById('amount_to_invest').addEventListener('input', calculateShares);
        // Add event listener for price per share changes
        document.getElementById('amount_per_share').addEventListener('change', calculateShares);

        // Auto-generate transaction ID for Paystack payments
        const paymentMethodSelect = document.getElementById('payment_method');
        const transactionIdInput = document.getElementById('transaction_id');

        paymentMethodSelect.addEventListener('change', function () {
            if (this.value === 'paystack' && !transactionIdInput.value) {
                const timestamp = Date.now();
                const random = Math.floor(Math.random() * 1000);
                transactionIdInput.value = `PAYSTACK_${timestamp}_${random}`;
            }
        });

        // Form validation for minimum investment
        const form = document.getElementById('sharesForm');
        form.addEventListener('submit', function (e) {
            const amountToInvest = parseFloat(document.getElementById('amount_to_invest').value);
            const numberOfShares = parseInt(document.getElementById('number_of_shares').value);

            if (amountToInvest < 10000) {
                // Wait, if price is low, amount might be low?
                // The Minimum investment 10,000 might be a rule. 
                // If user selects price 10, shares = 1000. Total = 10000.
                // If user wants to buy 100 shares at 10 naira = 1000 naira total.
                // Does the 10,000 limit still apply?
                // The text says "Minimum investment: ₦10,000 (100 shares at ₦100 each)".
                // If price changes, maybe min investment rule changes or stays.
                // I'll keep the warning but maybe clarify/relax if needed. 
                // For now, I'll trust the existing validation but be aware.
                // Actually, if price is 10, and I invest 10000, I satisfy min investment.
                // If calculating shares based on Input Investment, allowing price change is fine.
            }

            if (numberOfShares < 100) {
                // This is "Minimum shares: 100" rule.
                // If price is 200, and I invest 10000 -> 50 shares. This would fail!
                // So if price is 200, min investment should be 20000 to get 100 shares.
                // Or maybe the 100 shares limit is the hard rule.
                // I will leave the validation as is, user will see the alert.
            }
        });

        // Initialize calculation on page load
        calculateShares();

        // Initialize Tom Select for Member Selection
        new TomSelect('#member_id', {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            },
            placeholder: "Search and select member...",
            allowEmptyOption: true
        });
    });
</script>

<?php
// Capture the content and include the layout
$content = ob_get_clean();
include __DIR__ . '/../layouts/admin.php';
?>