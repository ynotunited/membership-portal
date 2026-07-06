<?php
// Get user data if not already provided
if (!isset($user) || empty($user)) {
    if (isset($_SESSION['user_id'])) {
        try {
            $userModel = new \App\Models\UserModel();
            $user = $userModel->getUserById($_SESSION['user_id']);
        } catch (Exception $e) {
            $user = [];
        }
    }
}

// Fallback user data
if (!isset($user) || empty($user)) {
    $user = [
        'firstname' => 'Admin',
        'lastname' => 'User',
        'email' => 'admin@example.com',
        'role' => 'Admin'
    ];
}

// Safely extract initials with null checks
$firstName = $user['firstname'] ?? ($user['email'] ? explode('@', $user['email'])[0] : 'A');
$lastName = $user['lastname'] ?? $user['surname'] ?? 'U';
$initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));

// Determine active page
$activePage = $activePage ?? '';
?>
<!-- Mobile Menu Button -->
<button id="mobileMenuBtn" class="lg:hidden fixed top-4 left-4 z-50 bg-white p-2 rounded-lg shadow-lg">
    <i class="ri-menu-line text-2xl text-gray-700"></i>
</button>

<!-- Sidebar Overlay for Mobile -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>

<!-- Sidebar -->
<aside id="sidebar"
    class="fixed lg:static w-64 bg-white shadow-lg flex flex-col h-full z-40 transform -translate-x-full lg:translate-x-0 transition-all duration-300">
    <!-- Logo and Collapse Button -->
    <div class="p-4 border-b border-gray-200 flex items-center justify-between gap-2">
        <a href="<?= \App\Helpers\Url::appUrl() ?>/dashboard" class="flex items-center justify-center flex-1 min-w-0">
            <img id="sidebarLogo" src="<?= \App\Helpers\Url::appUrl() ?>/uploads/gafconl_colored.png" alt="24/7 Logo"
                class="h-8 w-auto max-w-full transition-all duration-300">
            <img id="sidebarFavicon" src="<?= \App\Helpers\Url::appUrl() ?>/uploads/gafconl-favicon.png" alt="24/7"
                class="h-10 w-10 object-contain hidden transition-all duration-300">
        </a>
        <button id="sidebarCollapseBtn"
            class="flex items-center justify-center w-8 h-8 text-gray-600 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors flex-shrink-0">
            <i class="ri-menu-fold-line text-xl"></i>
        </button>
    </div>

    <!-- User Profile Section -->
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center space-x-3">
            <div
                class="w-12 h-12 bg-gradient-to-br from-primary to-secondary rounded-full flex items-center justify-center text-white font-semibold text-lg flex-shrink-0">
                <?= htmlspecialchars($initials) ?>
            </div>
            <div class="sidebar-text overflow-hidden">
                <h3 class="font-semibold text-gray-900 truncate"><?= htmlspecialchars($firstName . ' ' . $lastName) ?>
                </h3>
                <p class="text-sm text-gray-500 truncate">
                    <?= \App\Helpers\PermissionHelper::getRoleDisplayName(\App\Helpers\PermissionHelper::getUserRoleName()) ?>
                </p>
            </div>
        </div>
    </div>
    <nav class="flex-1 py-6">
        <ul class="space-y-2 px-4">
            <li>
                <a href="<?= \App\Helpers\Url::appUrl() ?>/dashboard"
                    class="sidebar-nav-item <?= ($activePage === 'dashboard') ? 'active' : '' ?> flex items-center px-4 py-3 text-gray-700 rounded-lg">
                    <div class="w-5 h-5 flex items-center justify-center flex-shrink-0"><i class="ri-home-line"></i>
                    </div>
                    <span class="ml-3 font-medium sidebar-text">Dashboard</span>
                </a>
            </li>

            <li>
                <a href="<?= \App\Helpers\Url::appUrl() ?>/admin/payments"
                    class="sidebar-nav-item <?= ($activePage === 'payments') ? 'active' : '' ?> flex items-center px-4 py-3 text-gray-700 rounded-lg">
                    <div class="w-5 h-5 flex items-center justify-center flex-shrink-0"><i
                            class="ri-bank-card-line"></i></div>
                    <span class="ml-3 font-medium sidebar-text">Payment Approvals</span>
                </a>
            </li>

            <?php if (\App\Helpers\PermissionHelper::hasAnyPermission(['registration_types.view', 'registration_types.create', 'registration_types.edit'])): ?>
                <li class="relative">
                    <button type="button"
                        class="sidebar-nav-item flex items-center px-4 py-3 text-gray-700 rounded-lg w-full justify-between <?= (in_array($activePage, ['membership_types', 'add_membership_type'])) ? 'active' : '' ?>"
                        id="membershipTypesDropdownBtn">
                        <span class="flex items-center">
                            <div class="w-5 h-5 flex items-center justify-center flex-shrink-0"><i
                                    class="ri-award-line"></i></div>
                            <span class="ml-3 font-medium sidebar-text">Membership</span>
                        </span>
                        <i class="ri-arrow-down-s-line ml-2"></i>
                    </button>
                    <ul id="membershipTypesDropdown"
                        class="<?= (in_array($activePage, ['membership_types', 'add_membership_type'])) ? '' : 'hidden' ?> pl-8 mt-1 space-y-1">
                        <?php if (\App\Helpers\PermissionHelper::hasPermission('registration_types.create')): ?>
                            <li>
                                <a href="<?= \App\Helpers\Url::appUrl() ?>/membership-types/add"
                                    class="sidebar-nav-item flex items-center px-4 py-2 text-gray-700 rounded-lg <?= ($activePage === 'add_membership_type') ? 'active' : '' ?>">
                                    <i class="ri-add-line mr-2"></i> Add New
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\App\Helpers\PermissionHelper::hasPermission('registration_types.view')): ?>
                            <li>
                                <a href="<?= \App\Helpers\Url::appUrl() ?>/membership-types"
                                    class="sidebar-nav-item flex items-center px-4 py-2 text-gray-700 rounded-lg <?= ($activePage === 'membership_types') ? 'active' : '' ?>">
                                    <i class="ri-eye-line mr-2"></i> View
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if (\App\Helpers\PermissionHelper::hasAnyPermission(['members.view', 'members.create'])): ?>
                <li class="relative">
                    <button type="button"
                        class="sidebar-nav-item flex items-center px-4 py-3 text-gray-700 rounded-lg w-full justify-between <?= (in_array($activePage, ['members', 'add_member'])) ? 'active' : '' ?>"
                        id="membersDropdownBtn">
                        <span class="flex items-center">
                            <div class="w-5 h-5 flex items-center justify-center flex-shrink-0"><i
                                    class="ri-group-line"></i></div>
                            <span class="ml-3 font-medium sidebar-text">Members</span>
                        </span>
                        <i class="ri-arrow-down-s-line ml-2"></i>
                    </button>
                    <ul id="membersDropdown"
                        class="<?= (in_array($activePage, ['members', 'add_member'])) ? '' : 'hidden' ?> pl-8 mt-1 space-y-1">
                        <?php if (\App\Helpers\PermissionHelper::hasPermission('members.create')): ?>
                            <li>
                                <a href="<?= \App\Helpers\Url::appUrl() ?>/members/add"
                                    class="sidebar-nav-item flex items-center px-4 py-2 text-gray-700 rounded-lg <?= ($activePage === 'add_member') ? 'active' : '' ?>">
                                    <i class="ri-user-add-line mr-2"></i> Add
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\App\Helpers\PermissionHelper::hasPermission('members.view')): ?>
                            <li>
                                <a href="<?= \App\Helpers\Url::appUrl() ?>/members"
                                    class="sidebar-nav-item flex items-center px-4 py-2 text-gray-700 rounded-lg <?= ($activePage === 'members') ? 'active' : '' ?>">
                                    <i class="ri-group-line mr-2"></i> Manage
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if (\App\Helpers\PermissionHelper::hasAnyPermission(['dues.view', 'shares.view'])): ?>
                <li class="relative">
                    <button type="button"
                        class="sidebar-nav-item flex items-center px-4 py-3 text-gray-700 rounded-lg w-full justify-between <?= (in_array($activePage, ['dues', 'shares', 'thrift', 'rice_project'])) ? 'active' : '' ?>"
                        id="listsDropdownBtn">
                        <span class="flex items-center">
                            <div class="w-5 h-5 flex items-center justify-center flex-shrink-0"><i
                                    class="ri-list-check-2"></i></div>
                            <span class="ml-3 font-medium sidebar-text">Lists</span>
                        </span>
                        <i class="ri-arrow-down-s-line ml-2"></i>
                    </button>
                    <ul id="listsDropdown"
                        class="<?= (in_array($activePage, ['dues', 'shares', 'thrift'])) ? '' : 'hidden' ?> pl-8 mt-1 space-y-1">
                        <?php if (\App\Helpers\PermissionHelper::hasPermission('dues.view')): ?>
                            <li>
                                <a href="<?= \App\Helpers\Url::appUrl() ?>/dues"
                                    class="sidebar-nav-item flex items-center px-4 py-2 text-gray-700 rounded-lg <?= ($activePage === 'dues') ? 'active' : '' ?>">
                                    <i class="ri-wallet-line mr-2"></i> Dues
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\App\Helpers\PermissionHelper::hasPermission('shares.view')): ?>
                            <li>
                                <a href="<?= \App\Helpers\Url::appUrl() ?>/shares"
                                    class="sidebar-nav-item flex items-center px-4 py-2 text-gray-700 rounded-lg <?= ($activePage === 'shares') ? 'active' : '' ?>">
                                    <i class="ri-line-chart-line mr-2"></i> Shares
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\App\Helpers\PermissionHelper::hasPermission('shares.view')): // Using shares permission for thrift as well ?>
                            <li>
                                <a href="<?= \App\Helpers\Url::appUrl() ?>/thrift"
                                    class="sidebar-nav-item flex items-center px-4 py-2 text-gray-700 rounded-lg <?= ($activePage === 'thrift') ? 'active' : '' ?>">
                                    <i class="ri-safe-2-line mr-2"></i> Thrift
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\App\Helpers\PermissionHelper::hasPermission('shares.view')): // Using shares permission for rice project as well ?>
                            <li>
                                <a href="<?= \App\Helpers\Url::appUrl() ?>/admin/rice-project"
                                    class="sidebar-nav-item flex items-center px-4 py-2 text-gray-700 rounded-lg <?= ($activePage === 'rice_project') ? 'active' : '' ?>">
                                    <i class="ri-plant-line mr-2"></i> Rice Project
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if (\App\Helpers\PermissionHelper::hasAnyPermission(['events.view', 'events.create', 'events.edit'])): ?>
                <li class="relative">
                    <button type="button"
                        class="sidebar-nav-item flex items-center px-4 py-3 text-gray-700 rounded-lg w-full justify-between <?= (in_array($activePage, ['events', 'add_event'])) ? 'active' : '' ?>"
                        id="eventsDropdownBtn">
                        <span class="flex items-center">
                            <div class="w-5 h-5 flex items-center justify-center flex-shrink-0"><i
                                    class="ri-calendar-event-line"></i></div>
                            <span class="ml-3 font-medium sidebar-text">Events</span>
                        </span>
                        <i class="ri-arrow-down-s-line ml-2"></i>
                    </button>
                    <ul id="eventsDropdown"
                        class="<?= (in_array($activePage, ['events', 'add_event'])) ? '' : 'hidden' ?> pl-8 mt-1 space-y-1">
                        <?php if (\App\Helpers\PermissionHelper::hasPermission('events.create')): ?>
                            <li>
                                <a href="<?= \App\Helpers\Url::appUrl() ?>/events/add"
                                    class="sidebar-nav-item flex items-center px-4 py-2 text-gray-700 rounded-lg <?= ($activePage === 'add_event') ? 'active' : '' ?>">
                                    <i class="ri-add-line mr-2"></i> Add Event
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\App\Helpers\PermissionHelper::hasPermission('events.view')): ?>
                            <li>
                                <a href="<?= \App\Helpers\Url::appUrl() ?>/events"
                                    class="sidebar-nav-item flex items-center px-4 py-2 text-gray-700 rounded-lg <?= ($activePage === 'events') ? 'active' : '' ?>">
                                    <i class="ri-calendar-event-line mr-2"></i> Manage Events
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- Forum Menu -->
            <li>
                <a href="<?= \App\Helpers\Url::appUrl() ?>/forum"
                    class="sidebar-nav-item <?= ($activePage === 'forum') ? 'active' : '' ?> flex items-center px-4 py-3 text-gray-700 rounded-lg">
                    <div class="w-5 h-5 flex items-center justify-center flex-shrink-0"><i class="ri-chat-1-line"></i>
                    </div>
                    <span class="ml-3 font-medium sidebar-text">Community Forum</span>
                </a>
            </li>

            <?php if (\App\Helpers\PermissionHelper::hasAnyPermission(['reports.view', 'revenue.view'])): ?>
                <li class="relative">
                    <button type="button"
                        class="sidebar-nav-item flex items-center px-4 py-3 text-gray-700 rounded-lg w-full justify-between <?= (in_array($activePage, ['reports', 'revenue'])) ? 'active' : '' ?>"
                        id="reportsDropdownBtn">
                        <span class="flex items-center">
                            <div class="w-5 h-5 flex items-center justify-center flex-shrink-0"><i
                                    class="ri-bar-chart-line"></i></div>
                            <span class="ml-3 font-medium sidebar-text">Reports</span>
                        </span>
                        <i class="ri-arrow-down-s-line ml-2"></i>
                    </button>
                    <ul id="reportsDropdown"
                        class="<?= (in_array($activePage, ['reports', 'revenue'])) ? '' : 'hidden' ?> pl-8 mt-1 space-y-1">
                        <?php if (\App\Helpers\PermissionHelper::hasPermission('reports.view')): ?>
                            <li>
                                <a href="<?= \App\Helpers\Url::appUrl() ?>/reports"
                                    class="sidebar-nav-item flex items-center px-4 py-2 text-gray-700 rounded-lg <?= ($activePage === 'reports') ? 'active' : '' ?>">
                                    <i class="ri-file-chart-line mr-2"></i> Membership
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\App\Helpers\PermissionHelper::hasPermission('revenue.view')): ?>
                            <li>
                                <a href="<?= \App\Helpers\Url::appUrl() ?>/revenue"
                                    class="sidebar-nav-item flex items-center px-4 py-2 text-gray-700 rounded-lg <?= ($activePage === 'revenue') ? 'active' : '' ?>">
                                    <i class="ri-money-dollar-circle-line mr-2"></i> Revenue
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if (\App\Helpers\PermissionHelper::canManageRoles()): ?>
                <li class="relative">
                    <button type="button"
                        class="sidebar-nav-item flex items-center px-4 py-3 text-gray-700 rounded-lg w-full justify-between <?= (in_array($activePage, ['roles', 'users'])) ? 'active' : '' ?>"
                        id="rolesDropdownBtn">
                        <span class="flex items-center">
                            <div class="w-5 h-5 flex items-center justify-center flex-shrink-0"><i
                                    class="ri-shield-user-line"></i></div>
                            <span class="ml-3 font-medium sidebar-text">Roles</span>
                        </span>
                        <i class="ri-arrow-down-s-line ml-2"></i>
                    </button>
                    <ul id="rolesDropdown"
                        class="<?= (in_array($activePage, ['roles', 'users'])) ? '' : 'hidden' ?> pl-8 mt-1 space-y-1">
                        <?php if (\App\Helpers\PermissionHelper::hasPermission('roles.create')): ?>
                            <li>
                                <a href="<?= \App\Helpers\Url::appUrl() ?>/roles/add"
                                    class="sidebar-nav-item flex items-center px-4 py-2 text-gray-700 rounded-lg <?= ($activePage === 'add_role') ? 'active' : '' ?>">
                                    <i class="ri-add-line mr-2"></i> Add Role
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\App\Helpers\PermissionHelper::hasPermission('roles.view')): ?>
                            <li>
                                <a href="<?= \App\Helpers\Url::appUrl() ?>/roles"
                                    class="sidebar-nav-item flex items-center px-4 py-2 text-gray-700 rounded-lg <?= ($activePage === 'roles') ? 'active' : '' ?>">
                                    <i class="ri-eye-line mr-2"></i> Manage Roles
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\App\Helpers\PermissionHelper::hasPermission('users.create')): ?>
                            <li>
                                <a href="<?= \App\Helpers\Url::appUrl() ?>/users/add"
                                    class="sidebar-nav-item flex items-center px-4 py-2 text-gray-700 rounded-lg <?= ($activePage === 'add_user') ? 'active' : '' ?>">
                                    <i class="ri-user-add-line mr-2"></i> Add User
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\App\Helpers\PermissionHelper::hasPermission('users.view')): ?>
                            <li>
                                <a href="<?= \App\Helpers\Url::appUrl() ?>/users"
                                    class="sidebar-nav-item flex items-center px-4 py-2 text-gray-700 rounded-lg <?= ($activePage === 'users') ? 'active' : '' ?>">
                                    <i class="ri-user-settings-line mr-2"></i> Manage Users
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if (\App\Helpers\PermissionHelper::hasAnyPermission(['settings.view', 'settings.edit'])): ?>
                <li>
                    <a href="<?= \App\Helpers\Url::appUrl() ?>/settings"
                        class="sidebar-nav-item flex items-center px-4 py-3 text-gray-700 rounded-lg <?= ($activePage === 'settings') ? 'active' : '' ?>">
                        <div class="w-5 h-5 flex items-center justify-center flex-shrink-0"><i
                                class="ri-settings-3-line"></i></div>
                        <span class="ml-3 font-medium sidebar-text">Settings</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="p-4 border-t border-gray-200">
        <a href="<?= \App\Helpers\Url::appUrl() ?>/logout"
            class="flex items-center justify-between px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg group">
            <div class="flex items-center">
                <div class="w-5 h-5 flex items-center justify-center flex-shrink-0"><i class="ri-logout-box-r-line"></i>
                </div>
                <span class="ml-3 font-medium sidebar-text">Logout</span>
            </div>
            <span
                class="text-xs text-gray-400 sidebar-text group-hover:text-gray-600 transition-colors">v<?= \App\Config\Version::get() ?></span>
        </a>
    </div>
</aside>

<script>
    // Mobile menu toggle and sidebar collapse
    document.addEventListener('DOMContentLoaded', function () {
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');
        const sidebarLogo = document.getElementById('sidebarLogo');
        const sidebarFavicon = document.getElementById('sidebarFavicon');

        // Mobile menu toggle
        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            sidebarOverlay.classList.toggle('hidden');
        }

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', toggleSidebar);
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', toggleSidebar);
        }

        // Desktop sidebar collapse
        if (sidebarCollapseBtn) {
            // Check localStorage for saved state
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                collapseSidebar();
            }

            sidebarCollapseBtn.addEventListener('click', function () {
                const collapsed = sidebar.classList.contains('collapsed');
                if (collapsed) {
                    expandSidebar();
                } else {
                    collapseSidebar();
                }
            });
        }

        function collapseSidebar() {
            sidebar.classList.add('collapsed');
            sidebar.classList.remove('w-64');
            sidebar.classList.add('w-20');

            // Hide all text elements
            document.querySelectorAll('.sidebar-text').forEach(el => {
                el.classList.add('hidden');
            });

            // Hide dropdown arrows
            document.querySelectorAll('.ri-arrow-down-s-line').forEach(el => {
                el.classList.add('hidden');
            });

            // Switch logo to favicon
            sidebarLogo.classList.add('hidden');
            sidebarFavicon.classList.remove('hidden');

            // Make favicon clickable to expand
            sidebarFavicon.style.cursor = 'pointer';
            sidebarFavicon.onclick = expandSidebar;

            // Change collapse icon
            const icon = sidebarCollapseBtn.querySelector('i');
            icon.classList.remove('ri-menu-fold-line');
            icon.classList.add('ri-menu-unfold-line');

            // Center menu items
            document.querySelectorAll('.sidebar-nav-item').forEach(item => {
                item.classList.add('justify-center');
                item.classList.remove('px-4');
                item.classList.add('px-2');
            });

            // Save state
            localStorage.setItem('sidebarCollapsed', 'true');
        }

        function expandSidebar() {
            sidebar.classList.remove('collapsed');
            sidebar.classList.remove('w-20');
            sidebar.classList.add('w-64');

            // Show all text elements
            document.querySelectorAll('.sidebar-text').forEach(el => {
                el.classList.remove('hidden');
            });

            // Show dropdown arrows
            document.querySelectorAll('.ri-arrow-down-s-line').forEach(el => {
                el.classList.remove('hidden');
            });

            // Switch back to full logo
            sidebarLogo.classList.remove('hidden');
            sidebarFavicon.classList.add('hidden');

            // Remove favicon click handler
            sidebarFavicon.style.cursor = 'default';
            sidebarFavicon.onclick = null;

            // Change collapse icon
            const icon = sidebarCollapseBtn.querySelector('i');
            icon.classList.remove('ri-menu-unfold-line');
            icon.classList.add('ri-menu-fold-line');

            // Reset menu items alignment
            document.querySelectorAll('.sidebar-nav-item').forEach(item => {
                item.classList.remove('justify-center');
                item.classList.remove('px-2');
                item.classList.add('px-4');
            });

            // Save state
            localStorage.setItem('sidebarCollapsed', 'false');
        }
    });
</script>