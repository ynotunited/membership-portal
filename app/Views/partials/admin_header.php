<div class="mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="ml-12 lg:ml-0">
            <?php
            // Get user data if not already provided
            if (!isset($user) || empty($user)) {
                if (isset($_SESSION['user_id'])) {
                    $userModel = new \App\Models\UserModel();
                    $user = $userModel->getUserById($_SESSION['user_id']);
                }
            }
            
            // Determine display name
            $displayName = 'Admin';
            if (isset($user) && is_array($user)) {
                if (!empty($user['firstname'])) {
                    $displayName = $user['firstname'];
                } elseif (!empty($user['email'])) {
                    $displayName = explode('@', $user['email'])[0];
                }
            }
            ?>
            <h1 class="text-2xl lg:text-3xl font-bold text-primary">Welcome back, <?= htmlspecialchars($displayName) ?>!</h1>
            <p class="text-gray-600 mt-1 text-sm lg:text-base" id="current-date-time"></p>
            <p class="text-xs lg:text-sm text-gray-500 mt-1">
                Role: <?= \App\Helpers\PermissionHelper::getRoleDisplayName(\App\Helpers\PermissionHelper::getUserRoleName()) ?>
            </p>
        </div>
        <div class="flex items-center space-x-2 lg:space-x-3 overflow-x-auto">
            <!-- Search Icon Button -->
            <button id="searchBtn" onclick="openSearchModal()" class="relative rounded-lg whitespace-nowrap px-3 lg:px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center space-x-2">
                <div class="w-4 h-4 flex items-center justify-center">
                    <i class="ri-search-line"></i>
                </div>
                <span class="hidden sm:inline">Search</span>
                <span class="text-xs text-gray-400 ml-2 hidden lg:inline">Ctrl+K</span>
            </button>
            
            <?php if (\App\Helpers\PermissionHelper::hasAnyPermission(['members.export', 'dues.export', 'shares.export'])): ?>
            <div class="relative">
                <button id="exportBtn" class="rounded-lg whitespace-nowrap px-3 lg:px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center space-x-2">
                    <div class="w-4 h-4 flex items-center justify-center">
                        <i class="ri-download-line"></i>
                    </div>
                    <span class="hidden sm:inline">Export Data</span>
                </button>
                <div id="exportDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                    <?php if (\App\Helpers\PermissionHelper::hasPermission('members.export')): ?>
                    <a href="<?= \App\Helpers\Url::appUrl() ?>/members/export" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Members</a>
                    <?php endif; ?>
                    <?php if (\App\Helpers\PermissionHelper::hasPermission('dues.export')): ?>
                    <a href="<?= \App\Helpers\Url::appUrl() ?>/dues/export" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Dues</a>
                    <?php endif; ?>
                    <?php if (\App\Helpers\PermissionHelper::hasPermission('shares.export')): ?>
                    <a href="<?= \App\Helpers\Url::appUrl() ?>/shares/export" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Shares</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="relative">
                <button id="notificationBtn" onclick="document.getElementById('notificationDropdown').classList.toggle('hidden')" class="relative rounded-lg whitespace-nowrap px-3 lg:px-4 py-2 bg-primary text-white hover:bg-secondary flex items-center space-x-2">
                    <div class="w-4 h-4 flex items-center justify-center">
                        <i class="ri-notification-line"></i>
                    </div>
                    <span class="hidden sm:inline">Notifications</span>
                    <?php 
                    // Get notification count if not provided
                    if (!isset($notificationCount)) {
                        $notificationCount = 0;
                        if (isset($_SESSION['user_id'])) {
                            try {
                                $notificationModel = new \App\Models\NotificationModel();
                                $notificationCount = $notificationModel->getUnreadCount($_SESSION['user_id']);
                            } catch (Exception $e) {
                                // If notification model fails, just use 0
                                $notificationCount = 0;
                            }
                        }
                    }
                    if (!empty($notificationCount)): ?>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full px-2 py-0.5 font-bold"><?= $notificationCount ?></span>
                    <?php endif; ?>
                </button>
                <div id="notificationDropdown" class="hidden fixed top-24 right-6 w-80 bg-white rounded-md shadow-xl border border-gray-200" style="z-index: 9999;">
                    <div class="p-3 border-b">
                        <h4 class="text-sm font-medium text-gray-900">Notifications</h4>
                    </div>
                    <div class="py-1 max-h-64 overflow-y-auto">
                        <a href="#" class="flex items-start px-4 py-3 text-sm text-gray-700 hover:bg-gray-100">
                            <div class="w-8 h-8 mr-3 bg-green-100 rounded-full flex-shrink-0 flex items-center justify-center">
                                <i class="ri-money-dollar-circle-line text-green-600"></i>
                            </div>
                            <div class="flex-grow">
                                <p class="font-medium">New dues payment from John D.</p>
                                <p class="text-xs text-gray-500">5 minutes ago</p>
                            </div>
                        </a>
                        <a href="#" class="flex items-start px-4 py-3 text-sm text-gray-700 hover:bg-gray-100">
                             <div class="w-8 h-8 mr-3 bg-primary/10 rounded-full flex-shrink-0 flex items-center justify-center">
                                <i class="ri-user-add-line text-primary"></i>
                            </div>
                            <div class="flex-grow">
                                <p class="font-medium">New member registered: Jane S.</p>
                                <p class="text-xs text-gray-500">1 hour ago</p>
                            </div>
                        </a>
                        <a href="#" class="flex items-start px-4 py-3 text-sm text-gray-700 hover:bg-gray-100">
                             <div class="w-8 h-8 mr-3 bg-orange-100 rounded-full flex-shrink-0 flex items-center justify-center">
                                <i class="ri-error-warning-line text-orange-600"></i>
                            </div>
                            <div class="flex-grow">
                                <p class="font-medium">Membership renewal due for Mike R.</p>
                                <p class="text-xs text-gray-500">Yesterday</p>
                            </div>
                        </a>
                    </div>
                     <div class="p-2 border-t">
                        <a href="#" class="block text-center text-sm font-medium text-primary hover:underline">View All Notifications</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search Modal -->
<div id="searchModal" onclick="if(event.target===this)closeSearchModal()" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden" style="display: none;">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[80vh] overflow-hidden">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Search</h3>
                <button id="closeSearchModal" onclick="closeSearchModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            
            <!-- Search Input -->
            <div class="p-6 border-b border-gray-200">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ri-search-line text-gray-400"></i>
                    </div>
                    <input type="text" 
                           id="modalSearchInput" 
                           placeholder="Search members, events, or any content..." 
                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
            </div>
            
            <!-- Search Results -->
            <div id="modalSearchResults" class="flex-1 overflow-y-auto max-h-96">
                <!-- Results will be populated here -->
            </div>
        </div>
    </div>
</div> 