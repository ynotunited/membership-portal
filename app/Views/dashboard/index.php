<?php
// Set page variables for the layout
$title = 'Admin Dashboard';
$pageTitle = 'Dashboard';
$pageSubtitle = 'Welcome back! Here\'s what\'s happening with your cooperative.';

// Start output buffering to capture the content
ob_start();
?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    
    <!-- Total Members -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                <i class="ri-group-line text-xl text-primary"></i>
            </div>
            <span class="text-sm font-medium text-gray-500">Total Members</span>
        </div>
        <div class="flex items-end justify-between">
            <div>
                <h3 class="text-2xl font-semibold text-gray-800">
                    <?php echo number_format($stats['total_members'] ?? 0); ?>
                </h3>
                <p class="text-sm text-green-600 flex items-center mt-1">
                    <i class="ri-arrow-up-line mr-1"></i>
                    +<?php echo $stats['new_members_this_month'] ?? 0; ?> this month
                </p>
            </div>
        </div>
    </div>
    
    <!-- Active Members -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="ri-user-heart-line text-xl text-green-600"></i>
            </div>
            <span class="text-sm font-medium text-gray-500">Active Members</span>
        </div>
        <div class="flex items-end justify-between">
            <div>
                <h3 class="text-2xl font-semibold text-gray-800">
                    <?php echo number_format($stats['active_members'] ?? 0); ?>
                </h3>
                <p class="text-sm text-primary flex items-center mt-1">
                    <i class="ri-percent-line mr-1"></i>
                    <?php echo number_format(($stats['active_members_percentage'] ?? 0) * 100, 1); ?>% of total
                </p>
            </div>
        </div>
    </div>
    
    <!-- Total Revenue -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                <i class="ri-wallet-3-line text-xl text-primary"></i>
            </div>
            <span class="text-sm font-medium text-gray-500">Total Revenue</span>
        </div>
        <div class="flex items-end justify-between">
            <div>
                <h3 class="text-2xl font-semibold text-gray-800">
                    ₦<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?>
                </h3>
                <p class="text-sm text-green-600 flex items-center mt-1">
                    <i class="ri-arrow-up-line mr-1"></i>
                    ₦<?php echo number_format($stats['revenue_this_month'] ?? 0, 2); ?> this month
                </p>
            </div>
        </div>
    </div>
    
    <!-- Upcoming Events -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="ri-calendar-event-line text-xl text-purple-600"></i>
            </div>
            <span class="text-sm font-medium text-gray-500">Upcoming Events</span>
        </div>
        <div class="flex items-end justify-between">
            <div>
                <h3 class="text-2xl font-semibold text-gray-800">
                    <?php echo $stats['upcoming_events'] ?? 0; ?>
                </h3>
                <p class="text-sm text-gray-600 flex items-center mt-1">
                    <i class="ri-calendar-line mr-1"></i>
                    Next 30 days
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    
    <!-- Real-time Activity Feed -->
    <div class="lg:col-span-2 bg-white rounded-lg p-6 shadow-sm border border-gray-200">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Activity Feed</h3>
            <div class="flex items-center space-x-2">
                <span id="activity-status" class="text-xs text-gray-500">Live</span>
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
            </div>
        </div>
        
        <div id="activity-feed-container" class="space-y-4">
            <?php if (!empty($activity_feed)): ?>
                <?php foreach ($activity_feed as $activity): ?>
                    <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200 activity-item" data-id="<?php echo $activity['id']; ?>">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="ri-user-line text-blue-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">
                                <?php echo !empty($activity['firstname']) ? htmlspecialchars($activity['firstname'] . ' ' . $activity['surname']) : 'System'; ?>
                            </p>
                            <p class="text-sm text-gray-600">
                                <?php echo htmlspecialchars($activity['action']); ?>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                <?php echo date('M j, g:i A', strtotime($activity['created_at'])); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-history-line text-2xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500">No recent activity to display</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- System Health -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">System Health</h3>
            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
        </div>
        
        <div class="space-y-4">
            <!-- Database Status -->
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                        <i class="ri-database-2-line text-primary"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Database</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 <?php echo $system_health['database']['color'] ?? 'bg-green-500'; ?> rounded-full"></div>
                    <span class="text-xs text-gray-600"><?php echo $system_health['database']['status'] ?? 'Operational'; ?></span>
                </div>
            </div>
            
            <!-- Email Service Status -->
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="ri-mail-send-line text-green-600"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Email Service</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 <?php echo $system_health['mailer']['color'] ?? 'bg-green-500'; ?> rounded-full"></div>
                    <span class="text-xs text-gray-600"><?php echo $system_health['mailer']['status'] ?? 'Configured'; ?></span>
                </div>
            </div>
            
            <!-- Storage Status -->
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="ri-hard-drive-line text-yellow-600"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Storage</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    <span class="text-xs text-gray-600">85% Available</span>
                </div>
            </div>
        </div>
        
        <div class="mt-6 pt-4 border-t border-gray-200">
            <a href="<?php echo \App\Helpers\Url::appUrl(); ?>/settings" 
               class="text-sm text-primary hover:text-secondary transition-colors duration-200">
                System Settings →
            </a>
        </div>
    </div>
</div>

<!-- Additional Stats Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
    <!-- Membership Types Breakdown -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Membership Types</h3>
            <a href="<?php echo \App\Helpers\Url::appUrl(); ?>/membership-types" 
               class="text-sm text-primary hover:text-secondary transition-colors duration-200">
                Manage Types
            </a>
        </div>
        
        <div class="space-y-4">
            <?php if (!empty($membership_type_data)): ?>
                <?php foreach ($membership_type_data as $type): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                <i class="ri-vip-crown-line text-primary"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700">
                                <?php echo htmlspecialchars($type['type']); ?>
                            </span>
                        </div>
                        <span class="text-sm font-semibold text-gray-800">
                            <?php echo number_format($type['count']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4">
                    <p class="text-gray-500 text-sm">No membership types configured</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Upcoming Renewals -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Upcoming Renewals</h3>
            <span class="text-sm text-gray-500">Next 30 days</span>
        </div>
        
        <div class="space-y-4">
            <?php if (!empty($upcoming_renewals)): ?>
                <?php foreach (array_slice($upcoming_renewals, 0, 5) as $renewal): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="ri-calendar-check-line text-orange-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700">
                                    <?php echo htmlspecialchars($renewal['firstname'] . ' ' . $renewal['surname']); ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    Due: <?php echo date('M j, Y', strtotime($renewal['renewal_date'])); ?>
                                </p>
                            </div>
                        </div>
                        <button class="text-xs text-primary hover:text-secondary transition-colors duration-200">
                            Remind
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="ri-check-line text-green-600"></i>
                    </div>
                    <p class="text-gray-500 text-sm">No upcoming renewals</p>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($upcoming_renewals) && count($upcoming_renewals) > 5): ?>
            <div class="mt-4 pt-4 border-t border-gray-200">
                <a href="<?php echo \App\Helpers\Url::appUrl(); ?>/members" 
                   class="text-sm text-primary hover:text-secondary transition-colors duration-200">
                    View all renewals (<?php echo count($upcoming_renewals); ?>) →
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Real-time Activity Feed JavaScript -->
<script>
(function() {
    let lastUpdate = new Date().toISOString();
    let isUpdating = false;
    
    // Function to fetch new activities
    function fetchNewActivities() {
        if (isUpdating) return;
        
        isUpdating = true;
        
        fetch('<?php echo \App\Helpers\Url::appUrl(); ?>/dashboard/activity-feed?since=' + encodeURIComponent(lastUpdate))
            .then(response => response.json())
            .then(data => {
                if (data.success && data.activities.length > 0) {
                    updateActivityFeed(data.activities);
                    lastUpdate = data.last_updated;
                }
                isUpdating = false;
            })
            .catch(error => {
                console.error('Error fetching activities:', error);
                isUpdating = false;
            });
    }
    
    // Function to update the activity feed UI
    function updateActivityFeed(activities) {
        const container = document.getElementById('activity-feed-container');
        
        // Add new activities to the top of the feed
        activities.forEach(activity => {
            // Check if activity already exists to prevent duplicates
            if (!document.querySelector(`.activity-item[data-id="${activity.id}"]`)) {
                const activityElement = document.createElement('div');
                activityElement.className = 'flex items-start space-x-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200 activity-item';
                activityElement.setAttribute('data-id', activity.id);
                activityElement.innerHTML = `
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="ri-user-line text-blue-600"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">
                            ${activity.user}
                        </p>
                        <p class="text-sm text-gray-600">
                            ${activity.action}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Just now
                        </p>
                    </div>
                `;
                
                // Add to the top of the container
                container.insertBefore(activityElement, container.firstChild);
                
                // Highlight the new activity
                activityElement.classList.add('bg-blue-50');
                setTimeout(() => {
                    activityElement.classList.remove('bg-blue-50');
                }, 2000);
            }
        });
        
        // Remove old activities if we have more than 20
        const activityItems = container.querySelectorAll('.activity-item');
        if (activityItems.length > 20) {
            for (let i = 20; i < activityItems.length; i++) {
                activityItems[i].remove();
            }
        }
    }
    
    // Start polling for updates every 10 seconds
    setInterval(fetchNewActivities, 10000);
    
    // Also fetch updates when the page becomes visible again
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            fetchNewActivities();
        }
    });
})();
</script>

<?php
// Capture the content and include the layout
$content = ob_get_clean();
include __DIR__ . '/../layouts/admin.php';
?>