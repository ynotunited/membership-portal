<?php
// Ensure this file is included within a layout
if (!defined('LAYOUT_INCLUDED')) {
    http_response_code(404);
    exit;
}

$amountToInvest = $_POST['amount_to_invest'] ?? 10000;
$shares = floor($amountToInvest / 100); // Calculate shares based on amount
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Purchase Shares</h1>
            <p class="text-gray-600">Complete your share purchase payment</p>
        </div>
        <a href="<?= \App\Helpers\Url::appUrl() ?>/member/shares"
            class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
            <i class="ri-arrow-left-line mr-2"></i>Back to Shares
        </a>
    </div>

    <!-- Payment Details Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Payment Details</h2>
            <p class="text-gray-600">Review your share purchase information before proceeding</p>
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

            <!-- Share Purchase Information -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Investment Amount</label>
                    <p class="text-2xl font-bold text-secondary">₦<?= number_format($amountToInvest, 2) ?></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Number of Shares</label>
                    <p class="text-gray-900 font-medium"><?= number_format($shares) ?> share(s)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Share Price</label>
                    <p class="text-gray-900 font-medium">₦100.00 per share</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Terms -->
    <div class="bg-primary/5 border border-primary/20 rounded-lg p-4">
        <div class="flex items-start">
            <div class="w-5 h-5 bg-primary/10 rounded-full flex items-center justify-center mr-3 mt-0.5">
                <i class="ri-information-line text-secondary text-sm"></i>
            </div>
            <div>
                <h3 class="text-sm font-medium text-blue-900 mb-1">Share Purchase Information</h3>
                <ul class="text-sm text-primary-dark space-y-1">
                    <li>• Share price is fixed at ₦100 per share</li>
                    <li>• Minimum investment: ₦10,000 (100 shares)</li>
                    <li>• Payment is processed securely through your selected gateway</li>
                    <li>• Shares will be credited to your account immediately after payment</li>
                    <li>• You can purchase additional shares at any time</li>
                    <li>• Shares contribute to your cooperative ownership and benefits</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Payment Action -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="POST" action="<?= \App\Helpers\Url::appUrl() ?>/member/shares/pay">
            <input type="hidden" name="amount_to_invest" value="<?= $amountToInvest ?>">

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
                            admin will verify and approve your shares purchase.</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Ready to Purchase Shares?</h3>
                    <p class="text-gray-600">Click the button below to proceed to secure payment</p>
                </div>
                <button type="submit"
                    class="px-8 py-3 bg-secondary text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center">
                    <i class="ri-checkbox-circle-line mr-2"></i>
                    Submit Purchase Request - ₦<?= number_format($amountToInvest, 2) ?>
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
                    label.classList.remove('border-primary', 'ring-2', 'ring-blue-500');
                    label.classList.add('border-gray-300');
                });

                // Add active styling to selected label
                if (this.checked) {
                    gatewayLabels[index].classList.remove('border-gray-300');
                    gatewayLabels[index].classList.add('border-primary', 'ring-2', 'ring-blue-500');
                }
            });
        });

        // Set initial active state
        const checkedInput = document.querySelector('input[name="payment_gateway"]:checked');
        if (checkedInput) {
            const index = Array.from(gatewayInputs).indexOf(checkedInput);
            gatewayLabels[index].classList.remove('border-gray-300');
            gatewayLabels[index].classList.add('border-primary', 'ring-2', 'ring-blue-500');
        }
    });
</script>