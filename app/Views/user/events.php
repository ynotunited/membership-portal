<?php
// Ensure this file is included within a layout
if (!defined('LAYOUT_INCLUDED')) {
    http_response_code(404);
    exit;
}
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Events</h1>
            <p class="text-gray-600">Stay updated with cooperative events and activities</p>
        </div>
        <div class="flex space-x-3">
            <a href="<?= \App\Helpers\Url::appUrl() ?>/member/calendar" class="px-4 py-2 bg-secondary text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="ri-calendar-line mr-2"></i>View Calendar
            </a>
        </div>
    </div>

    <!-- Upcoming Events -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Upcoming Events</h3>
        </div>
        
        <div class="p-6">
            <?php if (!empty($upcomingEvents)): ?>
                <div class="space-y-4">
                    <?php foreach ($upcomingEvents as $event): ?>
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <div class="w-2 h-2 bg-primary rounded-full"></div>
                                        <h4 class="text-lg font-semibold text-gray-900">
                                            <?= htmlspecialchars($event['title']) ?>
                                        </h4>
                                    </div>
                                    
                                    <p class="text-gray-600 mb-3">
                                        <?= htmlspecialchars($event['description']) ?>
                                    </p>
                                    
                                    <div class="flex items-center space-x-6 text-sm text-gray-500">
                                        <div class="flex items-center">
                                            <i class="ri-calendar-line mr-2"></i>
                                            <?= date('M d, Y', strtotime($event['event_date'])) ?>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="ri-time-line mr-2"></i>
                                            <?= date('g:i A', strtotime($event['start_date'])) ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col items-end space-y-2">
                                    <span class="px-3 py-1 bg-primary/10 text-blue-800 text-xs font-medium rounded-full">
                                        Upcoming
                                    </span>
                                    <button class="px-4 py-2 bg-secondary text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                        Register
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="ri-calendar-line text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">No upcoming events</p>
                    <p class="text-sm text-gray-400">Check back later for new events</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Past Events -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Past Events</h3>
        </div>
        
        <div class="p-6">
            <?php if (!empty($pastEvents)): ?>
                <div class="space-y-4">
                    <?php foreach ($pastEvents as $event): ?>
                        <div class="border border-gray-200 rounded-lg p-4 opacity-75">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                        <h4 class="text-lg font-semibold text-gray-900">
                                            <?= htmlspecialchars($event['title']) ?>
                                        </h4>
                                    </div>
                                    
                                    <p class="text-gray-600 mb-3">
                                        <?= htmlspecialchars($event['description']) ?>
                                    </p>
                                    
                                    <div class="flex items-center space-x-6 text-sm text-gray-500">
                                        <div class="flex items-center">
                                            <i class="ri-calendar-line mr-2"></i>
                                            <?= date('M d, Y', strtotime($event['event_date'])) ?>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="ri-time-line mr-2"></i>
                                            <?= date('g:i A', strtotime($event['start_date'])) ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col items-end space-y-2">
                                    <span class="px-3 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-full">
                                        Completed
                                    </span>
                                    <button class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm">
                                        View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="ri-calendar-line text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">No past events</p>
                    <p class="text-sm text-gray-400">Past events will appear here</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Event Categories -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Event Categories</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="border border-gray-200 rounded-lg p-4 text-center hover:shadow-md transition-shadow cursor-pointer">
                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mx-auto mb-3">
                    <i class="ri-group-line text-secondary text-xl"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-1">General Meetings</h4>
                <p class="text-sm text-gray-600">Monthly and annual member meetings</p>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4 text-center hover:shadow-md transition-shadow cursor-pointer">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                    <i class="ri-book-open-line text-green-600 text-xl"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-1">Training Sessions</h4>
                <p class="text-sm text-gray-600">Educational and skill development programs</p>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4 text-center hover:shadow-md transition-shadow cursor-pointer">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                    <i class="ri-store-line text-purple-600 text-xl"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-1">Market Events</h4>
                <p class="text-sm text-gray-600">Trade fairs and market access events</p>
            </div>
        </div>
    </div>
</div> 