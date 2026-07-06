<?php
// Ensure this file is included within a layout
if (!defined('LAYOUT_INCLUDED')) {
    http_response_code(404);
    exit;
}

$amount = $_POST['amount'] ?? 1000;
$notes = $_POST['notes'] ?? '';
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pay Thrift Contribution</h1>
            <p class="text-gray-600">Complete your monthly thrift savings contribution</p>
        </div>
        <a href="<?= \App\Helpers\Url::appUrl() ?>/member/thrift"
            class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
            <i class="ri-arrow-left-line mr-2"></i>Back to Thrift Savings
        </a>
    </div>

    <!-- Payment Details Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Payment Details</h2>
            <p class="text-gray-600">Review your thrift contribution information before proceeding</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Member Information -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Member Name</label>
                    <p class="text-gray-900 font-medium">
                        <?= htmlspecialchars($user['firstname'] . ' ' . $user['surname']) ?>
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Membership Number</label>
                    <p class="text-gray-900 font-medium"><?= htmlspecialchars($user['membership_number']) ?></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <p class="text-gray-900 font-medium"><?= htmlspecialchars($user['email']) ?></p>
                </div>
            </div>

            <!-- Contribution Information -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contribution Amount</label>
                    <p class="text-2xl font-bold text-green-600">₦<?= number_format($amount, 2) ?></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contribution Type</label>
                    <p class="text-gray-900 font-medium">Monthly Thrift Savings</p>
                </div>

                <?php if (!empty($notes)): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <p class="text-gray-900 font-medium"><?= htmlspecialchars($notes) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Payment Terms -->
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-start">
            <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                <i class="ri-information-line text-green-600 text-sm"></i>
            </div>
            <div>
                <h3 class="text-sm font-medium text-green-900 mb-1">Thrift Savings Information</h3>
                <ul class="text-sm text-green-700 space-y-1">
                    <li>• Monthly contributions help build your savings</li>
                    <li>• Minimum contribution: ₦100</li>
                    <li>• Payment is processed securely through your selected gateway</li>
                    <li>• Contribution will be credited to your thrift account immediately after payment</li>
                    <li>• You can make additional contributions at any time</li>
                    <li>• Thrift savings provide financial security and cooperative benefits</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Payment Action -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="POST" action="<?= \App\Helpers\Url::appUrl() ?>/member/thrift/pay">
            <input type="hidden" name="amount" value="<?= $amount ?>">
            <input type="hidden" name="notes" value="<?= htmlspecialchars($notes) ?>">

            <!-- Payment Gateway Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Payment Method</label>

                <input type="hidden" name="payment_gateway" value="manual">

                <!-- Bank Transfer Details -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-5">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                            <i class="ri-bank-line text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-md font-semibold text-blue-900">Direct Bank Transfer</h3>
                            <p class="text-sm text-blue-700">Please make payment to the account below</p>
                        </div>
                    </div>

                    <div class="space-y-3 bg-white p-4 rounded border border-blue-100">
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                            <span class="text-sm text-gray-600">Bank Name</span>
                            <span class="text-sm font-bold text-gray-800">First Bank Plc</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                            <span class="text-sm text-gray-600">Account Name</span>
                            <span class="text-sm font-bold text-gray-800">Global Apex Farmers Cooperative Nigeria
                                Limited</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Account Number</span>
                            <div class="flex items-center">
                                <span class="text-lg font-bold text-gray-800 mr-2">2045697533</span>
                                <button type="button" onclick="navigator.clipboard.writeText('2045697533')"
                                    class="text-primary hover:text-secondary text-xs">
                                    <i class="ri-file-copy-line"></i> Copy
                                </button>
                            </div>
                        </div>
                    </div>

                    <p class="text-xs text-blue-800 mt-3 flex items-start">
                        <i class="ri-information-fill mr-1 mt-0.5"></i>
                        <span>After making the transfer, click the button below to submit your payment notification. An
                            admin will verify and approve your contribution.</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Ready to Make Contribution?</h3>
                    <p class="text-gray-600">Click the button below to proceed to secure payment</p>
                </div>
                <button type="submit"
                    class="px-8 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium flex items-center">
                    <i class="ri-checkbox-circle-line mr-2"></i>
                    Submit Thrift Contribution - ₦<?= number_format($amount, 2) ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Security Notice -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
        <div class="flex items-center">
            <i class="ri-shield-check-line text-green-600 mr-3"></i>
            <div>
                <h4 class="text-sm font-medium text-gray-900">Secure Payment</h4>
                <p class="text-sm text-gray-600">Your payment information is encrypted and secure</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Handle payment gateway selection
        const gatewayInputs = document.querySelectorAll('input[name="payment_gateway"]');
        const gatewayLabels = document.querySelectorAll('label[class*="cursor-pointer"]');

        gatewayInputs.forEach((input, index) => {
            input.addEventListener('change', function () {
                // Remove active styling from all labels
                gatewayLabels.forEach(label => {
                    label.classList.remove('border-green-500', 'ring-2', 'ring-green-500');
                    label.classList.add('border-gray-300');
                });

                // Add active styling to selected label
                if (this.checked) {
                    gatewayLabels[index].classList.remove('border-gray-300');
                    gatewayLabels[index].classList.add('border-green-500', 'ring-2', 'ring-green-500');
                }
            });
        });

        // Set initial active state
        const checkedInput = document.querySelector('input[name="payment_gateway"]:checked');
        if (checkedInput) {
            const index = Array.from(gatewayInputs).indexOf(checkedInput);
            gatewayLabels[index].classList.remove('border-gray-300');
            gatewayLabels[index].classList.add('border-green-500', 'ring-2', 'ring-green-500');
        }
    });
</script>