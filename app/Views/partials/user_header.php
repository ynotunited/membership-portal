<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary">Welcome back, <?= htmlspecialchars($user['firstname'] ?? 'Member') ?>!</h1>
            <p class="text-gray-600 mt-1" id="current-date-time"></p>
            <p class="text-sm text-gray-500 mt-1">
                Membership: <?= htmlspecialchars($user['membership_number'] ?? 'N/A') ?>
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <!-- Member Actions -->
            <div class="relative">
                <button id="memberActionsBtn" class="rounded-lg whitespace-nowrap px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center space-x-2">
                    <div class="w-4 h-4 flex items-center justify-center">
                        <i class="ri-user-settings-line"></i>
                    </div>
                    <span>Actions</span>
                </button>
                <div id="memberActionsDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                    <a href="<?= \App\Helpers\Url::appUrl() ?>/member/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Update Profile</a>
                    <a href="<?= \App\Helpers\Url::appUrl() ?>/member/change-password" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Change Password</a>
                    <a href="<?= \App\Helpers\Url::appUrl() ?>/member/id-card" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Download ID Card</a>
                    <div class="border-t border-gray-100"></div>
                    <a href="<?= \App\Helpers\Url::appUrl() ?>/logout" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Sign Out</a>
                </div>
            </div>
            
            <!-- Notifications -->
            <div class="relative">
                <button id="notificationBtn" class="relative rounded-lg whitespace-nowrap px-4 py-2 bg-primary text-white hover:bg-secondary flex items-center space-x-2">
                    <div class="w-4 h-4 flex items-center justify-center">
                        <i class="ri-notification-line"></i>
                    </div>
                    <span>Notifications</span>
                    <?php if (($user['annual_dues_status'] ?? 'unpaid') !== 'paid'): ?>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full px-2 py-0.5 font-bold">1</span>
                    <?php endif; ?>
                </button>
                <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg z-10 border">
                    <div class="p-3 border-b">
                        <h4 class="text-sm font-medium text-gray-900">Notifications</h4>
                    </div>
                    <div class="py-1 max-h-64 overflow-y-auto">
                        <?php if (($user['annual_dues_status'] ?? 'unpaid') !== 'paid'): ?>
                            <a href="<?= \App\Helpers\Url::appUrl() ?>/member/dues" class="flex items-start px-4 py-3 text-sm text-gray-700 hover:bg-gray-100">
                                <div class="w-8 h-8 mr-3 bg-red-100 rounded-full flex-shrink-0 flex items-center justify-center">
                                    <i class="ri-calendar-check-line text-red-600"></i>
                                </div>
                                <div class="flex-grow">
                                    <p class="font-medium">Annual dues payment due</p>
                                    <p class="text-xs text-gray-500">Please pay your annual dues to maintain active membership</p>
                                </div>
                            </a>
                        <?php endif; ?>
                        <a href="<?= \App\Helpers\Url::appUrl() ?>/member/events" class="flex items-start px-4 py-3 text-sm text-gray-700 hover:bg-gray-100">
                             <div class="w-8 h-8 mr-3 bg-primary/10 rounded-full flex-shrink-0 flex items-center justify-center">
                                <i class="ri-calendar-event-line text-secondary"></i>
                            </div>
                            <div class="flex-grow">
                                <p class="font-medium">New event: Monthly Meeting</p>
                                <p class="text-xs text-gray-500">Next meeting scheduled for this weekend</p>
                            </div>
                        </a>
                        <a href="<?= \App\Helpers\Url::appUrl() ?>/member/forum" class="flex items-start px-4 py-3 text-sm text-gray-700 hover:bg-gray-100">
                             <div class="w-8 h-8 mr-3 bg-green-100 rounded-full flex-shrink-0 flex items-center justify-center">
                                <i class="ri-chat-3-line text-green-600"></i>
                            </div>
                            <div class="flex-grow">
                                <p class="font-medium">New forum discussion</p>
                                <p class="text-xs text-gray-500">Join the latest farming techniques discussion</p>
                            </div>
                        </a>
                    </div>
                     <div class="p-2 border-t">
                        <a href="#" class="block text-center text-sm font-medium text-secondary hover:underline">View All Notifications</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>