<!-- Rice Project Banner -->
<div class="relative bg-black rounded-lg overflow-hidden mb-8 shadow-lg group">
    <div class="relative h-48 md:h-64">
        <!-- Background Image -->
        <img src="<?= \App\Helpers\Url::appUrl() ?>/uploads/42264.jpg" alt="Rice Field"
            class="w-full h-full object-cover opacity-70">

        <!-- Content Overlay -->
        <div class="absolute inset-0 flex flex-col justify-center px-8 bg-gradient-to-r from-black/80 to-transparent">
            <h2 class="text-4xl md:text-5xl font-bold text-white mb-2">GAFCONL RICE PROJECT <span
                    class="text-sm text-green-400 font-medium tracking-wider">
                    - POWERED BY GAFCONL INVEST</span>
            </h2>
            <p class="text-green-400 font-semibold text-lg mb-4">Invest in the Future of Agriculture. 38% Interest p.a.
            </p>

            <div class="flex space-x-4">
                <a href="<?= \App\Helpers\Url::appUrl() ?>/rice-project"
                    class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition-colors">
                    <i class="ri-money-dollar-circle-line mr-2"></i> Invest Now
                </a>
                <button onclick="document.getElementById('dashboardVideoModal').classList.remove('hidden')"
                    class="inline-flex items-center bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white font-bold py-2 px-6 rounded-lg transition-colors cursor-pointer">
                    <i class="ri-play-circle-line mr-2"></i> Watch Presentation
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Video Modal -->
<div id="dashboardVideoModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title"
    role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
            onclick="document.getElementById('dashboardVideoModal').classList.add('hidden'); document.getElementById('promoVideo').pause();">
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-black rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="relative bg-black">
                <video id="promoVideo" controls class="w-full h-auto max-h-[80vh]">
                    <source src="<?= \App\Helpers\Url::appUrl() ?>/uploads/videos/rice_project.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <button
                    onclick="document.getElementById('dashboardVideoModal').classList.add('hidden'); document.getElementById('promoVideo').pause();"
                    class="absolute top-4 right-4 text-white hover:text-red-500 z-10 bg-black/50 rounded-full p-2">
                    <i class="ri-close-line text-2xl"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-8">

    <!-- Payment Status -->
    <div
        class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between mb-4">
            <div
                class="w-12 h-12 <?php echo ($user['payment_status'] ?? 'Pending') === 'Paid' ? 'bg-green-100' : 'bg-yellow-100'; ?> rounded-lg flex items-center justify-center">
                <i
                    class="ri-check-line text-xl <?php echo ($user['payment_status'] ?? 'Pending') === 'Paid' ? 'text-green-600' : 'text-yellow-600'; ?>"></i>
            </div>
            <span class="text-sm font-medium text-gray-500">Payment Status</span>
        </div>
        <div class="flex items-end justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">
                    <?php echo htmlspecialchars($user['payment_status'] ?? 'Pending'); ?>
                </h3>
                <p class="text-sm text-gray-600 mt-1">
                    <?php echo $user['payment_date'] ? date('M j, Y', strtotime($user['payment_date'])) : 'Not available'; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Annual Dues -->
    <div
        class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between mb-4">
            <div
                class="w-12 h-12 <?php echo ($stats['dues_status'] ?? 'unpaid') === 'paid' ? 'bg-green-100' : 'bg-red-100'; ?> rounded-lg flex items-center justify-center">
                <i
                    class="ri-calendar-check-line text-xl <?php echo ($stats['dues_status'] ?? 'unpaid') === 'paid' ? 'text-green-600' : 'text-red-600'; ?>"></i>
            </div>
            <span class="text-sm font-medium text-gray-500">Annual Dues</span>
        </div>
        <div class="flex items-end justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">
                    <?php echo ($stats['dues_status'] ?? 'unpaid') === 'paid' ? 'Paid' : 'Unpaid'; ?>
                </h3>
                <p class="text-sm text-gray-600 mt-1">
                    <?php echo $stats['dues_date'] ? date('M j, Y', strtotime($stats['dues_date'])) : 'Not paid yet'; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Total Shares -->
    <div
        class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                <i class="ri-pie-chart-line text-xl text-secondary"></i>
            </div>
            <span class="text-sm font-medium text-gray-500">Total Shares</span>
        </div>
        <div class="flex items-end justify-between">
            <div>
                <h3 class="text-2xl font-semibold text-gray-800">
                    <?php echo number_format($stats['total_shares'] ?? 0); ?>
                </h3>
                <p class="text-sm text-gray-600 mt-1">
                    ₦<?php echo number_format(($stats['total_shares'] ?? 0) * 100, 2); ?> value
                </p>
            </div>
        </div>
    </div>

    <!-- Rice Investments -->
    <?php
    $riceModel = new \App\Models\RiceInvestmentModel();
    $totalRiceInvestment = $riceModel->getTotalInvestmentByMember($user['id']);
    ?>
    <div
        class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="ri-plant-line text-xl text-green-600"></i>
            </div>
            <span class="text-sm font-medium text-gray-500">GAFCONL INVEST</span>
        </div>
        <div class="flex items-end justify-between">
            <div>
                <h3 class="text-2xl font-semibold text-gray-800">
                    ₦<?php echo number_format($totalRiceInvestment, 2); ?>
                </h3>
                <p class="text-sm text-gray-600 mt-1">
                    Total Invested With GAFCONL INVEST
                </p>
            </div>
        </div>
    </div>

    <!-- Thrift Savings -->
    <div
        class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="ri-wallet-3-line text-xl text-purple-600"></i>
            </div>
            <span class="text-sm font-medium text-gray-500">Thrift Savings</span>
        </div>
        <div class="flex items-end justify-between">
            <div>
                <h3 class="text-2xl font-semibold text-gray-800">
                    ₦<?php echo number_format($stats['total_savings'] ?? 0, 2); ?>
                </h3>
                <p class="text-sm text-gray-600 mt-1">
                    Total Savings Balance
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    <!-- Member Information -->
    <div class="lg:col-span-2 bg-white rounded-lg p-6 shadow-sm border border-gray-200">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Member Information</h3>
            <a href="<?php echo \App\Helpers\Url::appUrl(); ?>/member/profile"
                class="text-sm text-primary hover:text-secondary transition-colors duration-200">
                View Full Profile
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-primary/5 rounded-lg border border-primary/20">
                    <span class="text-sm font-medium text-gray-700">Membership Number</span>
                    <span
                        class="text-sm font-semibold text-primary"><?php echo htmlspecialchars($user['membership_number'] ?? 'N/A'); ?></span>
                </div>

                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">Chapter</span>
                    <span
                        class="text-sm text-gray-800"><?php echo htmlspecialchars($user['chapter'] ?? 'N/A'); ?></span>
                </div>

                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">Zone</span>
                    <span class="text-sm text-gray-800"><?php echo htmlspecialchars($user['zone'] ?? 'N/A'); ?></span>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">Registration Date</span>
                    <span
                        class="text-sm text-gray-800"><?php echo $user['created_at'] ? date('M j, Y', strtotime($user['created_at'])) : 'N/A'; ?></span>
                </div>

                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">Membership Type</span>
                    <span
                        class="text-sm text-gray-800"><?php echo htmlspecialchars($user['membership_type'] ?? 'N/A'); ?></span>
                </div>

                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">Contact</span>
                    <span
                        class="text-sm text-gray-800"><?php echo htmlspecialchars($user['contact_number'] ?? 'N/A'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Quick Actions</h3>
        </div>

        <div class="space-y-3">
            <a href="<?php echo \App\Helpers\Url::appUrl(); ?>/rice-project"
                class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg transition-colors duration-200 border border-green-200">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="ri-plant-line text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-green-800">Invest in Rice Project</p>
                    <p class="text-xs text-green-600">38% ROI per annum</p>
                </div>
            </a>

            <?php if (($user['annual_dues_status'] ?? 'unpaid') !== 'paid'): ?>
                <a href="<?php echo \App\Helpers\Url::appUrl(); ?>/member/dues"
                    class="flex items-center p-3 bg-red-50 hover:bg-red-100 rounded-lg transition-colors duration-200">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="ri-calendar-check-line text-red-600"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-red-800">Pay Annual Dues</p>
                        <p class="text-xs text-red-600">Required to access all features</p>
                    </div>
                </a>
            <?php endif; ?>

            <a href="<?php echo \App\Helpers\Url::appUrl(); ?>/member/shares"
                class="flex items-center p-3 bg-primary/5 hover:bg-primary/10 rounded-lg transition-colors duration-200">
                <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center mr-3">
                    <i class="ri-pie-chart-line text-secondary"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-blue-800">Buy Shares</p>
                    <p class="text-xs text-secondary">Invest in cooperative shares</p>
                </div>
            </a>

            <a href="<?php echo \App\Helpers\Url::appUrl(); ?>/member/thrift"
                class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors duration-200">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="ri-wallet-3-line text-purple-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-purple-800">Thrift Savings</p>
                    <p class="text-xs text-purple-600">Make monthly savings</p>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Recent Activity & Events -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Recent Activity -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Recent Activity</h3>
        </div>

        <div class="space-y-4">
            <?php if (!empty($recentActivity)): ?>
                <?php foreach ($recentActivity as $activity): ?>
                    <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg">
                        <div class="w-8 h-8 <?php echo $activity['icon_bg']; ?> rounded-lg flex items-center justify-center">
                            <i class="<?php echo $activity['icon']; ?> <?php echo $activity['icon_color']; ?>"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-800">
                                <?php echo htmlspecialchars($activity['description']); ?>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                <?php echo date('M j, Y', strtotime($activity['date'])); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-history-line text-2xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500">No recent activity</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upcoming Events -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Upcoming Events</h3>
            <a href="<?php echo \App\Helpers\Url::appUrl(); ?>/member/events"
                class="text-sm text-primary hover:text-secondary transition-colors duration-200">
                View All
            </a>
        </div>

        <div class="space-y-4">
            <?php if (!empty($upcomingEvents)): ?>
                <?php foreach ($upcomingEvents as $event): ?>
                    <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg">
                        <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                            <i class="ri-calendar-event-line text-secondary"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-800">
                                <?php echo htmlspecialchars($event['title'] ?? 'Event'); ?>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                <?php echo date('M j, Y', strtotime($event['event_date'])); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-calendar-line text-2xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500">No upcoming events</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>