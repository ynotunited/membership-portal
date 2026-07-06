<?php
// Ensure this file is included within a layout
if (!defined('LAYOUT_INCLUDED')) {
    http_response_code(404);
    exit;
}

// Get current month and year from URL parameters or use current date
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Get previous and next month/year for navigation
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}

// Get month name
$monthName = date('F', mktime(0, 0, 0, $month, 1, $year));

// Get first day of month and number of days
$firstDay = date('w', mktime(0, 0, 0, $month, 1, $year));
$daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Calendar</h1>
            <p class="text-gray-600">View events in calendar format</p>
        </div>
        <div class="flex space-x-3">
            <a href="<?= \App\Helpers\Url::appUrl() ?>/member/events" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <i class="ri-list-check mr-2"></i>List View
            </a>
        </div>
    </div>

    <!-- Calendar Navigation -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="flex items-center text-gray-600 hover:text-gray-900">
                <i class="ri-arrow-left-s-line mr-2"></i>
                Previous
            </a>
            
            <h2 class="text-xl font-semibold text-gray-900"><?= $monthName ?> <?= $year ?></h2>
            
            <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="flex items-center text-gray-600 hover:text-gray-900">
                Next
                <i class="ri-arrow-right-s-line ml-2"></i>
            </a>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <!-- Calendar Header -->
        <div class="grid grid-cols-7 gap-px bg-gray-200">
            <div class="bg-gray-50 p-3 text-center text-sm font-medium text-gray-500">Sun</div>
            <div class="bg-gray-50 p-3 text-center text-sm font-medium text-gray-500">Mon</div>
            <div class="bg-gray-50 p-3 text-center text-sm font-medium text-gray-500">Tue</div>
            <div class="bg-gray-50 p-3 text-center text-sm font-medium text-gray-500">Wed</div>
            <div class="bg-gray-50 p-3 text-center text-sm font-medium text-gray-500">Thu</div>
            <div class="bg-gray-50 p-3 text-center text-sm font-medium text-gray-500">Fri</div>
            <div class="bg-gray-50 p-3 text-center text-sm font-medium text-gray-500">Sat</div>
        </div>

        <!-- Calendar Days -->
        <div class="grid grid-cols-7 gap-px bg-gray-200">
            <?php
            $day = 1;
            $currentDate = date('Y-m-d');
            
            // Fill in empty cells for days before the first day of the month
            for ($i = 0; $i < $firstDay; $i++) {
                echo '<div class="bg-white p-3 min-h-[120px]"></div>';
            }
            
            // Fill in the days of the month
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $isToday = $date === $currentDate;
                $hasEvents = isset($eventsByDate[$date]);
                
                echo '<div class="bg-white p-3 min-h-[120px] relative">';
                echo '<div class="flex items-center justify-between mb-2">';
                echo '<span class="text-sm font-medium ' . ($isToday ? 'bg-secondary text-white px-2 py-1 rounded-full' : 'text-gray-900') . '">' . $day . '</span>';
                if ($hasEvents) {
                    echo '<span class="w-2 h-2 bg-red-500 rounded-full"></span>';
                }
                echo '</div>';
                
                // Display events for this day
                if ($hasEvents) {
                    echo '<div class="space-y-1">';
                    foreach ($eventsByDate[$date] as $event) {
                        $isPast = strtotime($event['start_date']) < time();
                        $eventTime = date('g:i A', strtotime($event['start_date']));
                        
                        echo '<div class="text-xs p-1 rounded ' . ($isPast ? 'bg-gray-100 text-gray-600' : 'bg-primary/10 text-blue-800') . '">';
                        echo '<div class="font-medium truncate">' . htmlspecialchars($event['title']) . '</div>';
                        echo '<div class="text-xs">' . $eventTime . '</div>';
                        echo '</div>';
                    }
                    echo '</div>';
                }
                
                echo '</div>';
            }
            
            // Fill in empty cells for days after the last day of the month
            $lastDayOfWeek = date('w', mktime(0, 0, 0, $month, $daysInMonth, $year));
            for ($i = $lastDayOfWeek; $i < 6; $i++) {
                echo '<div class="bg-white p-3 min-h-[120px]"></div>';
            }
            ?>
        </div>
    </div>

    <!-- Legend -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Legend</h3>
        <div class="flex items-center space-x-6">
            <div class="flex items-center">
                <div class="w-3 h-3 bg-secondary rounded-full mr-2"></div>
                <span class="text-sm text-gray-600">Today</span>
            </div>
            <div class="flex items-center">
                <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                <span class="text-sm text-gray-600">Has Events</span>
            </div>
            <div class="flex items-center">
                <div class="w-3 h-3 bg-primary/10 rounded-full mr-2"></div>
                <span class="text-sm text-gray-600">Upcoming Events</span>
            </div>
            <div class="flex items-center">
                <div class="w-3 h-3 bg-gray-100 rounded-full mr-2"></div>
                <span class="text-sm text-gray-600">Past Events</span>
            </div>
        </div>
    </div>
</div> 