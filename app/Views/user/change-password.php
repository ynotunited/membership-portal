<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Change Password</h1>
            <p class="text-gray-600">Update your account password</p>
        </div>
    </div>

    <!-- Change Password Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="post" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                            <i class="ri-lock-line text-sm"></i>
                        </div>
                    </div>
                    <input type="password" 
                           name="current_password" 
                           required
                           class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm" 
                           placeholder="Enter your current password">
                    <button type="button" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center" 
                            onclick="togglePassword('current-password')">
                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                            <i class="ri-eye-line text-sm"></i>
                        </div>
                    </button>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                            <i class="ri-lock-line text-sm"></i>
                        </div>
                    </div>
                    <input type="password" 
                           id="new-password" 
                           name="new_password" 
                           required
                           class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm" 
                           placeholder="Enter your new password"
                           oninput="checkPasswordStrength(this.value)">
                    <button type="button" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center" 
                            onclick="togglePassword('new-password')">
                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                            <i class="ri-eye-line text-sm"></i>
                        </div>
                    </button>
                </div>
                <div id="passwordStrength" class="mt-2 text-xs"></div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                            <i class="ri-lock-line text-sm"></i>
                        </div>
                    </div>
                    <input type="password" 
                           id="confirm-password" 
                           name="confirm_password" 
                           required
                           class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm" 
                           placeholder="Confirm your new password">
                    <button type="button" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center" 
                            onclick="togglePassword('confirm-password')">
                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                            <i class="ri-eye-line text-sm"></i>
                        </div>
                    </button>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" 
                        class="px-6 py-3 bg-secondary text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    Update Password
                </button>
            </div>
        </form>
    </div>

    <!-- Password Requirements -->
    <div class="bg-primary/5 border border-primary/20 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-blue-900 mb-2">Password Requirements</h3>
        <ul class="text-sm text-primary-dark space-y-1">
            <li>• At least 6 characters long</li>
            <li>• Include uppercase and lowercase letters</li>
            <li>• Include numbers and special characters</li>
            <li>• Should be different from your current password</li>
        </ul>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'ri-eye-off-line text-sm';
    } else {
        input.type = 'password';
        icon.className = 'ri-eye-line text-sm';
    }
}

function checkPasswordStrength(password) {
    const strengthDiv = document.getElementById('passwordStrength');
    let strength = 0;
    let feedback = [];

    if (password.length >= 6) strength++;
    else feedback.push('At least 6 characters');

    if (/[a-z]/.test(password)) strength++;
    else feedback.push('One lowercase letter');

    if (/[A-Z]/.test(password)) strength++;
    else feedback.push('One uppercase letter');

    if (/[0-9]/.test(password)) strength++;
    else feedback.push('One number');

    if (/[^A-Za-z0-9]/.test(password)) strength++;
    else feedback.push('One special character');

    let strengthText = '';
    let strengthClass = '';

    if (strength < 2) {
        strengthText = 'Weak';
        strengthClass = 'text-red-600';
    } else if (strength < 4) {
        strengthText = 'Medium';
        strengthClass = 'text-yellow-600';
    } else {
        strengthText = 'Strong';
        strengthClass = 'text-green-600';
    }

    if (password.length > 0) {
        strengthDiv.innerHTML = `<div class="flex items-center justify-between"><span class="${strengthClass}">Password strength: ${strengthText}</span></div>${feedback.length > 0 ? `<div class="text-gray-500 mt-1">Missing: ${feedback.join(', ')}</div>` : ''}`;
    } else {
        strengthDiv.innerHTML = '';
    }
}
</script> 