<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center max-w-lg mx-auto mt-12">
    <div class="w-16 h-16 bg-red-100 text-secondary rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-lock text-2xl"></i>
    </div>
    
    <h2 class="text-2xl font-bold text-gray-900 mb-2">Access Restricted</h2>
    <p class="text-gray-600 mb-6">
        You need to pay your Annual Membership Dues to access the member dashboard and features.
    </p>

    <div class="bg-gray-50 rounded-lg p-4 mb-6">
        <div class="flex justify-between items-center mb-2">
            <span class="text-gray-600">Annual Dues:</span>
            <span class="font-bold text-gray-900">₦10,000</span>
        </div>
        <div class="flex justify-between items-center text-sm">
            <span class="text-gray-500">Status:</span>
            <span class="text-secondary font-medium">Unpaid</span>
        </div>
    </div>

    <form action="/member/paystack/initiate" method="POST">
        <input type="hidden" name="csrf_token" value="<?= \App\Helpers\Csrf::generateToken() ?>">
        <button type="submit" class="w-full bg-primary hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center gap-2">
            <i class="fas fa-credit-card"></i>
            Pay with Paystack
        </button>
    </form>
</div>
