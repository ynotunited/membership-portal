<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Thrift Savings</h1>
            <p class="text-gray-600">Manage your monthly thrift savings contributions</p>
        </div>
        <div class="flex space-x-3">
            <button class="px-4 py-2 bg-secondary text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="ri-download-line mr-2"></i>Download Statement
            </button>
        </div>
    </div>

    <!-- Thrift Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="ri-bank-card-line text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Savings</p>
                    <p class="text-2xl font-bold text-gray-900">
                        ₦<?= number_format($thriftData['total_savings'] ?? 0, 2) ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i class="ri-calendar-line text-secondary text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Monthly Contribution</p>
                    <p class="text-2xl font-bold text-gray-900">
                        ₦<?= number_format($thriftData['monthly_amount'] ?? 1000, 2) ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="ri-time-line text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Last Payment</p>
                    <p class="text-2xl font-bold text-gray-900">
                        <?= $thriftData['last_payment_date'] ? date('M d', strtotime($thriftData['last_payment_date'])) : 'Never' ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Make Payment -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Make Monthly Contribution</h3>
        
        <form method="POST" action="<?= \App\Helpers\Url::appUrl() ?>/member/thrift/pay" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">₦</span>
                        <input type="number" 
                               name="amount" 
                               min="100" 
                               value="1000"
                               required
                               class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Minimum ₦100</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                    <input type="text" 
                           name="notes" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                           placeholder="Add any notes about this contribution...">
                </div>
            </div>
            
            <!-- Payment Gateway Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Choose Payment Method</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <label class="relative flex cursor-pointer rounded-lg border border-gray-300 bg-white p-4 shadow-sm focus:outline-none">
                        <input type="radio" name="payment_gateway" value="paystack" class="sr-only" checked>
                        <span class="flex flex-1">
                            <span class="flex flex-col">
                                <span class="block text-sm font-medium text-gray-900">Paystack</span>
                                <span class="mt-1 flex items-center text-sm text-gray-500">Credit/Debit Cards, Bank Transfer</span>
                            </span>
                        </span>
                        <span class="pointer-events-none absolute -inset-px rounded-lg border-2" aria-hidden="true"></span>
                    </label>
                    
                    <label class="relative flex cursor-pointer rounded-lg border border-gray-300 bg-white p-4 shadow-sm focus:outline-none">
                        <input type="radio" name="payment_gateway" value="monify" class="sr-only">
                        <span class="flex flex-1">
                            <span class="flex flex-col">
                                <span class="block text-sm font-medium text-gray-900">Monify</span>
                                <span class="mt-1 flex items-center text-sm text-gray-500">Cards, USSD, Bank Transfer</span>
                            </span>
                        </span>
                        <span class="pointer-events-none absolute -inset-px rounded-lg border-2" aria-hidden="true"></span>
                    </label>
                    
                    <label class="relative flex cursor-pointer rounded-lg border border-gray-300 bg-white p-4 shadow-sm focus:outline-none">
                        <input type="radio" name="payment_gateway" value="opay" class="sr-only">
                        <span class="flex flex-col">
                            <span class="block text-sm font-medium text-gray-900">OPay</span>
                            <span class="mt-1 flex items-center text-sm text-gray-500">OPay Wallet, Cards, USSD</span>
                        </span>
                        <span class="pointer-events-none absolute -inset-px rounded-lg border-2" aria-hidden="true"></span>
                    </label>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" 
                        class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium flex items-center">
                    <i class="ri-lock-line mr-2"></i>
                    Make Contribution
                </button>
            </div>
        </form>
    </div>

    <!-- Payment History -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Contribution History</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($thriftHistory)): ?>
                        <?php foreach ($thriftHistory as $payment): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= date('M d, Y', strtotime($payment['payment_date'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₦<?= number_format($payment['amount'], 2) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= ucfirst($payment['payment_method'] ?? 'Online') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?= ($payment['status'] === 'successful') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= ucfirst($payment['status'] ?? 'pending') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?= htmlspecialchars($payment['notes'] ?? '') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="ri-bank-card-line text-4xl mb-2 text-gray-300"></i>
                                    <p>No contribution history found</p>
                                    <p class="text-sm">Your thrift contributions will appear here</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div> 

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle payment gateway selection
    const gatewayInputs = document.querySelectorAll('input[name="payment_gateway"]');
    const gatewayLabels = document.querySelectorAll('label[class*="cursor-pointer"]');
    
    gatewayInputs.forEach((input, index) => {
        input.addEventListener('change', function() {
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