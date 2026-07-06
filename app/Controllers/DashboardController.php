<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Helpers\Url;
use App\Models\MemberModel;
use App\Models\AnnualDuesModel;
use App\Models\SharesModel;
use App\Models\EventModel;
use App\Models\SettingsModel;
use App\Models\UserModel;
use App\Models\AuditLogModel;

class DashboardController extends BaseController
{
    private $memberModel;
    private $duesModel;
    private $sharesModel;
    private $eventModel;
    private $settingsModel;
    private $auditLogModel;

    public function __construct()
    {
        $this->requireAdmin();
        $this->memberModel = new MemberModel();
        $this->duesModel = new AnnualDuesModel();
        $this->sharesModel = new SharesModel();
        $this->eventModel = new EventModel();
        $this->settingsModel = new SettingsModel();
        $this->auditLogModel = new AuditLogModel();
    }

    public function index()
    {
        $this->requireAdmin();

        // Data for stats cards
        $totalMembers = $this->memberModel->getTotalMembers();
        $activeMembers = $this->memberModel->getActiveMembersCount();
        $stats = [
            'total_members' => $totalMembers,
            'active_members' => $activeMembers,
            'new_members_this_month' => $this->memberModel->getNewMembersThisMonth(),
            'active_members_percentage' => ($totalMembers > 0) ? ($activeMembers / $totalMembers) : 0,
            'total_revenue' => $this->duesModel->getTotalRevenue() + $this->sharesModel->getTotalRevenue(),
            'revenue_this_month' => $this->duesModel->getMonthlyRevenue(date('Y-m')) + $this->sharesModel->getMonthlyRevenue(date('Y-m')),
            'upcoming_events' => $this->eventModel->getUpcomingEventsCount(),
        ];
        
        // Data for revenue chart (last 6 months)
        $revenue_chart_data = $this->getRevenueChartData();

        // Recent activity / Audit Log
        $recent_activity = $this->getRecentActivity();
        
        // Get recent audit logs for activity feed
        $activity_feed = $this->auditLogModel->getRecentActivityFeed(10);

        // Membership type breakdown
        $membership_type_data = $this->memberModel->getMemberCountByType();

        // Upcoming Renewals
        $upcoming_renewals = $this->memberModel->getUpcomingRenewals(30); // 30 days window

        // System Health
        $system_health = $this->getSystemHealth();

        $top_members_dues = $this->memberModel->getTopMembersByDues(5);
        $top_members_shares = $this->memberModel->getTopMembersByShares(5);
        $geo_distribution = $this->memberModel->getMemberGeoDistribution();

        $userModel = new UserModel();
        $user = $userModel->getUserById($_SESSION['user_id']);

        // Notification count
        $notificationCount = 0;
        if (isset($_SESSION['user_id'])) {
            $notificationModel = new \App\Models\NotificationModel();
            $notificationCount = $notificationModel->getUnreadCount($_SESSION['user_id']);
        }

        $this->render('dashboard/index', [
            'pageTitle' => 'Admin Dashboard',
            'activePage' => 'dashboard',
            'user' => $user,
            'stats' => $stats,
            'revenue_chart_data' => $revenue_chart_data,
            'recent_activity' => $recent_activity,
            'activity_feed' => $activity_feed,
            'membership_type_data' => $membership_type_data,
            'upcoming_renewals' => $upcoming_renewals,
            'system_health' => $system_health,
            'top_members_dues' => $top_members_dues,
            'top_members_shares' => $top_members_shares,
            'geo_distribution' => $geo_distribution,
            'notificationCount' => $notificationCount
        ]);
    }

    // Add a new method for real-time activity feed updates
    public function getActivityFeed()
    {
        $this->requireAdmin();
        
        // Get the 'since' parameter from the request, default to 1 hour ago
        $since = $_GET['since'] ?? date('Y-m-d H:i:s', strtotime('-1 hour'));
        
        // Get recent activities since the specified time
        $activities = $this->auditLogModel->getRecentActivityFeedSince($since, 10);
        
        // Format the activities for the frontend
        $formattedActivities = [];
        foreach ($activities as $activity) {
            $formattedActivities[] = [
                'id' => $activity['id'],
                'user' => !empty($activity['firstname']) ? $activity['firstname'] . ' ' . $activity['surname'] : 'System',
                'action' => $activity['action'],
                'details' => $activity['details'],
                'time' => $activity['created_at'],
                'time_ago' => $this->timeAgo($activity['created_at'])
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'activities' => $formattedActivities,
            'last_updated' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
    
    // Helper method to calculate time ago
    private function timeAgo($datetime)
    {
        $time = strtotime($datetime);
        $currentTime = time();
        $diff = $currentTime - $time;
        
        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } else {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        }
    }

    private function getRevenueChartData()
    {
        $data = ['labels' => [], 'dues' => [], 'shares' => []];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $data['labels'][] = date('M Y', strtotime($month));
            $data['dues'][] = $this->duesModel->getMonthlyRevenue($month);
            $data['shares'][] = $this->sharesModel->getMonthlyRevenue($month);
        }
        return $data;
    }

    private function getSystemHealth()
    {
        // Check DB Connection
        try {
            $this->memberModel->getConnection()->query("SELECT 1");
            $db_status = ['status' => 'Operational', 'color' => 'bg-green-500'];
        } catch (\PDOException $e) {
            $db_status = ['status' => 'Down', 'color' => 'bg-red-500'];
        }

        // Check Mailer (basic check for config values)
        if (getenv('SMTP_HOST') && getenv('SMTP_USER')) {
            $mailer_status = ['status' => 'Configured', 'color' => 'bg-green-500'];
        } else {
             $mailer_status = ['status' => 'Not Configured', 'color' => 'bg-yellow-500'];
        }

        return [
            'database' => $db_status,
            'mailer' => $mailer_status,
        ];
    }

    private function getRecentActivity()
    {
        // This is a placeholder. In a real app, you'd fetch this from a dedicated audit log table.
        $recentDues = $this->duesModel->getRecentDues(3);
        $recentShares = $this->sharesModel->getRecentShares(2);

        $activity = [];

        foreach($recentDues as $due) {
            $activity[] = [
                'icon' => 'ri-wallet-3-line',
                'icon_bg' => 'bg-green-100',
                'icon_color' => 'text-green-600',
                'description' => 'Dues payment of $' . number_format($due['amount'], 2) . ' from ' . $due['firstname'],
                'time' => date('M j, g:i A', strtotime($due['payment_date'])),
            ];
        }

        foreach($recentShares as $share) {
             $activity[] = [
                'icon' => 'ri-line-chart-line',
                'icon_bg' => 'bg-blue-100',
                'icon_color' => 'text-blue-600',
                'description' => 'Shares purchase of $' . number_format($share['amount'], 2) . ' by ' . $share['firstname'],
                'time' => date('M j, g:i A', strtotime($share['purchase_date'])),
            ];
        }

        // Sort activity by time (most recent first) before returning a slice
        usort($activity, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return array_slice($activity, 0, 5);
    }
}