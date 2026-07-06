<?php
// Ensure this file is included within a layout
if (!defined('LAYOUT_INCLUDED')) {
    http_response_code(404);
    exit;
}

$reference = $_GET['reference'] ?? '';
$amount = $_GET['amount'] ?? '12000';
$email = $_GET['email'] ?? '';
$callbackUrl = $_GET['callback_url'] ?? '';
$gateway = $_GET['gateway'] ?? 'paystack';
$paymentId = $_GET['payment_id'] ?? '';

$gatewayNames = [
    'paystack' => 'Paystack',
    'monify' => 'Monify',
    'opay' => 'OPay'
];

$gatewayName = $gatewayNames[$gateway] ?? 'Payment Gateway';

// Determine payment type from callback URL
$paymentType = 'dues';
if (strpos($callbackUrl, '/shares/') !== false) {
    $paymentType = 'shares';
} elseif (strpos($callbackUrl, '/thrift/') !== false) {
    $paymentType = 'thrift';
}

$paymentTypeNames = [
    'dues' => 'Annual Dues',
    'shares' => 'Share Purchase',
    'thrift' => 'Thrift Contribution'
];

$paymentTypeName = $paymentTypeNames[$paymentType] ?? 'Payment';
?>

<div class="min-h-screen bg-gray-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="ri-bank-card-line text-2xl text-green-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Demo <?= $gatewayName ?> Gateway</h2>
                <p class="text-gray-600 mt-2">This is a demo payment page for testing purposes</p>
            </div>

            <!-- Payment Details -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Payment Details</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Payment Type:</span>
                        <span class="font-semibold"><?= $paymentTypeName ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Amount:</span>
                        <span class="font-semibold">₦<?= number_format($amount/100, 2) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Reference:</span>
                        <span class="font-mono text-sm"><?= htmlspecialchars($reference) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Gateway:</span>
                        <span class="text-sm font-medium"><?= $gatewayName ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Email:</span>
                        <span class="text-sm"><?= htmlspecialchars($email) ?></span>
                    </div>
                    <?php if ($paymentId): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Payment ID:</span>
                        <span class="font-mono text-sm"><?= htmlspecialchars($paymentId) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Demo Card Form -->
            <form method="POST" action="<?= htmlspecialchars($callbackUrl) ?>" class="space-y-4">
                <input type="hidden" name="reference" value="<?= htmlspecialchars($reference) ?>">
                <input type="hidden" name="payment_id" value="<?= htmlspecialchars($paymentId) ?>">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Card Number</label>
                    <input type="text" value="4111 1111 1111 1111" readonly class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                        <input type="text" value="12/25" readonly class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CVV</label>
                        <input type="text" value="123" readonly class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cardholder Name</label>
                    <input type="text" value="Demo User" readonly class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600">
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3 pt-4">
                    <button type="submit" name="status" value="success" class="w-full bg-green-600 text-white py-3 px-4 rounded-md hover:bg-green-700 transition-colors font-medium">
                        <i class="ri-check-line mr-2"></i>Pay Successfully
                    </button>
                    
                    <button type="submit" name="status" value="failed" class="w-full bg-red-600 text-white py-3 px-4 rounded-md hover:bg-red-700 transition-colors font-medium">
                        <i class="ri-close-line mr-2"></i>Simulate Payment Failure
                    </button>
                    
                    <button type="submit" name="status" value="cancelled" class="w-full bg-yellow-600 text-white py-3 px-4 rounded-md hover:bg-yellow-700 transition-colors font-medium">
                        <i class="ri-close-line mr-2"></i>Simulate Payment Cancellation
                    </button>
                    
                    <a href="<?= \App\Helpers\Url::appUrl() ?>/member/<?= $paymentType ?>" class="w-full bg-gray-300 text-gray-700 py-3 px-4 rounded-md hover:bg-gray-400 transition-colors font-medium text-center block">
                        <i class="ri-arrow-left-line mr-2"></i>Cancel Payment
                    </a>
                </div>
            </form>

            <!-- Demo Notice -->
            <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-center">
                    <i class="ri-information-line text-yellow-600 mr-2"></i>
                    <p class="text-sm text-yellow-800">
                        This is a demo <?= $gatewayName ?> payment gateway for <?= strtolower($paymentTypeName) ?>. You can test different payment scenarios:
                        <br>• <strong>Pay Successfully</strong> - Simulates successful payment
                        <br>• <strong>Payment Failure</strong> - Simulates payment failure (insufficient funds, etc.)
                        <br>• <strong>Payment Cancellation</strong> - Simulates user cancellation
                    </p>
                </div>
            </div>
        </div>
    </div>
</div> 