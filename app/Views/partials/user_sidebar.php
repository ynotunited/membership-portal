<!-- User Sidebar -->
<aside id="sidebar"
    class="w-64 bg-white border-r border-gray-200 flex flex-col fixed lg:relative inset-y-0 left-0 z-50 -translate-x-full lg:translate-x-0 transition-all duration-300 ease-in-out">

    <!-- Logo Section -->
    <div class="p-6 border-b border-gray-200 flex items-center justify-between gap-2">
        <div class="flex-1 flex items-center justify-center min-w-0">
            <img id="sidebarLogo" src="<?php echo \App\Helpers\Url::appUrl(); ?>/uploads/gafconl_colored.png"
                alt="24/7 Logo" class="max-w-full h-8 w-auto transition-all duration-300">
            <img id="sidebarFavicon" src="<?php echo \App\Helpers\Url::appUrl(); ?>/uploads/gafconl-favicon.png"
                alt="24/7" class="h-10 w-10 object-contain hidden transition-all duration-300">
        </div>
        <button id="sidebarCollapseBtn"
            class="flex items-center justify-center w-8 h-8 text-gray-600 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors flex-shrink-0">
            <i class="ri-menu-fold-line text-xl"></i>
        </button>
    </div>

    <!-- User Profile Section -->
    <div class="p-4 border-b border-gray-200">
        <div class="flex items-center space-x-3">
            <div class="w-12 h-12 rounded-full overflow-hidden bg-gray-200 flex-shrink-0">
                <?php if (!empty($user['photo']) && $user['photo'] !== 'default.jpg'): ?>
                    <img src="<?php echo \App\Helpers\Url::appUrl(); ?>/uploads/member_photos/<?php echo htmlspecialchars($user['photo']); ?>"
                        alt="Profile Photo" class="w-full h-full object-cover"
                        onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-primary/10\'><i class=\'ri-user-line text-primary text-xl\'></i></div>'">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center bg-primary/10">
                        <i class="ri-user-line text-primary text-xl"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="flex-1 min-w-0 sidebar-text">
                <p class="text-sm font-medium text-gray-800 truncate">
                    <?php echo htmlspecialchars(($user['firstname'] ?? 'Member') . ' ' . ($user['surname'] ?? 'User')); ?>
                </p>
                <p class="text-xs text-gray-500 truncate">
                    <?php echo htmlspecialchars($user['membership_number'] ?? 'Member'); ?>
                </p>
            </div>
        </div>

        <!-- Dues Status Badge -->
        <?php
        $annualDuesPaid = ($user['annual_dues_status'] ?? 'unpaid') === 'paid';
        ?>
        <div class="mt-3">
            <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $annualDuesPaid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800 animate-pulse'; ?>">
                <div
                    class="w-1.5 h-1.5 rounded-full <?php echo $annualDuesPaid ? 'bg-green-400' : 'bg-red-400'; ?> mr-1.5">
                </div>
                <?php echo $annualDuesPaid ? 'Dues Paid' : 'Dues Pending'; ?>
            </span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-4 overflow-y-auto">
        <div class="space-y-1">

            <!-- Dashboard -->
            <a href="<?php echo \App\Helpers\Url::appUrl(); ?>/member/dashboard"
                class="sidebar-nav-item flex items-center px-4 py-3 text-sm font-medium text-gray-600 rounded-lg transition-colors duration-200">
                <div class="w-5 h-5 flex items-center justify-center mr-3 flex-shrink-0">
                    <i class="ri-dashboard-line"></i>
                </div>
                <span class="sidebar-text">Dashboard</span>
            </a>

            <!-- Annual Dues -->
            <a href="<?php echo \App\Helpers\Url::appUrl(); ?>/member/dues"
                class="sidebar-nav-item flex items-center px-4 py-3 text-sm font-medium text-gray-600 rounded-lg transition-colors duration-200 <?php echo !$annualDuesPaid ? 'bg-red-50 border-l-4 border-red-500' : ''; ?>">
                <div class="w-5 h-5 flex items-center justify-center mr-3 flex-shrink-0">
                    <i class="ri-calendar-check-line <?php echo !$annualDuesPaid ? 'text-red-600' : ''; ?>"></i>
                </div>
                <span class="sidebar-text <?php echo !$annualDuesPaid ? 'text-red-700 font-semibold' : ''; ?>">Annual
                    Dues</span>
                <?php if (!$annualDuesPaid): ?>
                    <span
                        class="ml-auto bg-red-500 sidebar-text text-white text-xs px-2 py-1 rounded-full animate-pulse">Due</span>
                <?php endif; ?>
            </a>

            <!-- Buy Shares -->
            <a href="<?php echo \App\Helpers\Url::appUrl(); ?>/member/shares"
                class="sidebar-nav-item flex items-center px-4 py-3 text-sm font-medium text-gray-600 rounded-lg transition-colors duration-200">
                <div class="w-5 h-5 flex items-center justify-center mr-3 flex-shrink-0">
                    <i class="ri-pie-chart-line"></i>
                </div>
                <span class="sidebar-text">Buy Shares</span>
            </a>

            <!-- Thrift Savings -->
            <a href="<?php echo \App\Helpers\Url::appUrl(); ?>/member/thrift"
                class="sidebar-nav-item flex items-center px-4 py-3 text-sm font-medium text-gray-600 rounded-lg transition-colors duration-200">
                <div class="w-5 h-5 flex items-center justify-center mr-3 flex-shrink-0">
                    <i class="ri-wallet-3-line"></i>
                </div>
                <span class="sidebar-text">Thrift Savings</span>
            </a>

            <!-- Forum -->
            <a href="<?php echo \App\Helpers\Url::appUrl(); ?>/member/forum"
                class="sidebar-nav-item flex items-center px-4 py-3 text-sm font-medium text-gray-600 rounded-lg transition-colors duration-200">
                <div class="w-5 h-5 flex items-center justify-center mr-3 flex-shrink-0">
                    <i class="ri-chat-3-line"></i>
                </div>
                <span class="sidebar-text">Forum</span>
            </a>

            <!-- Events -->
            <a href="<?php echo \App\Helpers\Url::appUrl(); ?>/member/events"
                class="sidebar-nav-item flex items-center px-4 py-3 text-sm font-medium text-gray-600 rounded-lg transition-colors duration-200">
                <div class="w-5 h-5 flex items-center justify-center mr-3 flex-shrink-0">
                    <i class="ri-calendar-event-line"></i>
                </div>
                <span class="sidebar-text">Events</span>
            </a>
        </div>
    </nav>

    <!-- Logout Section -->
    <div class="p-4 border-t border-gray-200">
        <a href="<?php echo \App\Helpers\Url::appUrl(); ?>/logout"
            class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200 group">
            <div class="flex items-center">
                <div class="w-5 h-5 flex items-center justify-center mr-3 flex-shrink-0">
                    <i class="ri-logout-box-line"></i>
                </div>
                <span class="sidebar-text">Sign Out</span>
            </div>
            <span
                class="text-xs text-gray-400 sidebar-text group-hover:text-gray-600 transition-colors">v<?php echo \App\Config\Version::get(); ?></span>
        </a>
    </div>
</aside>

<!-- Mobile Menu Button -->
<div class="lg:hidden fixed top-4 left-4 z-50">
    <button id="mobileMenuBtn"
        class="w-10 h-10 bg-white rounded-lg shadow-lg flex items-center justify-center text-gray-600 hover:text-primary transition-colors duration-200">
        <i class="ri-menu-line text-xl"></i>
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');
        const sidebarLogo = document.getElementById('sidebarLogo');
        const sidebarFavicon = document.getElementById('sidebarFavicon');

        if (mobileMenuBtn && sidebar) {
            mobileMenuBtn.addEventListener('click', function () {
                sidebar.classList.toggle('-translate-x-full');
            });
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function (e) {
            if (window.innerWidth < 1024) {
                if (!sidebar.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                    sidebar.classList.add('-translate-x-full');
                }
            }
        });

        // Desktop sidebar collapse
        if (sidebarCollapseBtn) {
            // Check localStorage for saved state
            const isCollapsed = localStorage.getItem('userSidebarCollapsed') === 'true';
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

            // Switch logo to favicon
            if (sidebarLogo && sidebarFavicon) {
                sidebarLogo.classList.add('hidden');
                sidebarFavicon.classList.remove('hidden');

                // Make favicon clickable to expand
                sidebarFavicon.style.cursor = 'pointer';
                sidebarFavicon.onclick = expandSidebar;
            }

            // Change collapse icon
            const icon = sidebarCollapseBtn.querySelector('i');
            icon.classList.remove('ri-menu-fold-line');
            icon.classList.add('ri-menu-unfold-line');

            // Center menu items
            document.querySelectorAll('.sidebar-nav-item').forEach(item => {
                item.classList.add('justify-center');
            });

            // Save state
            localStorage.setItem('userSidebarCollapsed', 'true');
        }

        function expandSidebar() {
            sidebar.classList.remove('collapsed');
            sidebar.classList.remove('w-20');
            sidebar.classList.add('w-64');

            // Show all text elements
            document.querySelectorAll('.sidebar-text').forEach(el => {
                el.classList.remove('hidden');
            });

            // Switch back to full logo
            if (sidebarLogo && sidebarFavicon) {
                sidebarLogo.classList.remove('hidden');
                sidebarFavicon.classList.add('hidden');

                // Remove favicon click handler
                sidebarFavicon.style.cursor = 'default';
                sidebarFavicon.onclick = null;
            }

            // Change collapse icon
            const icon = sidebarCollapseBtn.querySelector('i');
            icon.classList.remove('ri-menu-unfold-line');
            icon.classList.add('ri-menu-fold-line');

            // Reset menu items alignment
            document.querySelectorAll('.sidebar-nav-item').forEach(item => {
                item.classList.remove('justify-center');
            });

            // Save state
            localStorage.setItem('userSidebarCollapsed', 'false');
        }

        // Member actions dropdown
        const memberActionsBtn = document.getElementById('memberActionsBtn');
        const memberActionsDropdown = document.getElementById('memberActionsDropdown');

        if (memberActionsBtn && memberActionsDropdown) {
            memberActionsBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                memberActionsDropdown.classList.toggle('hidden');
            });

            document.addEventListener('click', function (e) {
                if (!memberActionsBtn.contains(e.target) && !memberActionsDropdown.contains(e.target)) {
                    memberActionsDropdown.classList.add('hidden');
                }
            });
        }

        // Active state for sidebar links
        const currentPath = window.location.pathname;
        const sidebarLinks = document.querySelectorAll('.sidebar-nav-item');

        sidebarLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && currentPath.includes(href.replace('<?php echo \App\Helpers\Url::appUrl(); ?>', ''))) {
                link.classList.add('active');
            }
        });
    });
</script>