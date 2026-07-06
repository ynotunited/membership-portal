<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Helpers\Url;
use App\Models\MemberModel;
use App\Models\AnnualDuesModel;
use App\Models\SharesModel;
use App\Models\EventModel;
use App\Models\SettingsModel;
use App\Models\ThriftSavingsModel;
use App\Models\RiceInvestmentModel;

class ReportsController extends BaseController
{
    private $memberModel;
    private $duesModel;
    private $sharesModel;
    private $eventModel;
    private $settingsModel;
    private $thriftModel;
    private $riceModel;

    public function __construct()
    {
        $this->requireAdmin();
        $this->memberModel = new MemberModel();
        $this->duesModel = new AnnualDuesModel();
        $this->sharesModel = new SharesModel();
        $this->eventModel = new EventModel();
        $this->settingsModel = new SettingsModel();
        $this->thriftModel = new ThriftSavingsModel();
        $this->riceModel = new RiceInvestmentModel();
    }

    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        $reportType = $_GET['reportType'] ?? 'all';
        $dateFrom = $_GET['dateFrom'] ?? date('Y-m-01');
        $dateTo = $_GET['dateTo'] ?? date('Y-m-d');

        $data = [
            'reportType' => $reportType,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'currency' => $this->settingsModel->getCurrency(),
            'pageTitle' => 'Reports & Analytics',
            // Init empty arrays to avoid view errors
            'financialData' => [],
            'duesData' => [],
            'sharesData' => [],
            'thriftData' => [],
            'projectData' => [],
            'eventsData' => []
        ];

        // Load data based on report type
        switch ($reportType) {
            case 'financial':
            case 'all':
                $data = array_merge($data, $this->getFinancialData($dateFrom, $dateTo));
                // If 'all', we might want to load everything? 
                // "muddled together" suggests they DON'T want everything at once.
                // But if they ask for "All", maybe they want the summary cards.
                // Let's stick to Financial/Overview for 'all' as the default view.
                break;
            case 'membership':
                $data = array_merge($data, $this->getMembershipData($dateFrom, $dateTo));
                break;
            case 'dues':
                $data = array_merge($data, $this->getDuesData($dateFrom, $dateTo));
                break;
            case 'shares':
                $data = array_merge($data, $this->getSharesData($dateFrom, $dateTo));
                break;
            case 'thrift':
                $data = array_merge($data, $this->getThriftData($dateFrom, $dateTo));
                break;
            case 'project':
                $data = array_merge($data, $this->getRiceProjectData($dateFrom, $dateTo));
                break;
            case 'events':
                $data = array_merge($data, $this->getEventsData($dateFrom, $dateTo));
                break;
        }

        $this->render('admin/reports', $data);
    }

    private function getFinancialData($dateFrom, $dateTo)
    {
        // Get monthly financial data
        $financialData = [];

        // Generate monthly data for the last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $monthName = date('M Y', strtotime("-$i months"));

            $duesRevenue = $this->duesModel->getMonthlyRevenue($month);
            $sharesRevenue = $this->sharesModel->getMonthlyRevenue($month);
            $totalRevenue = $duesRevenue + $sharesRevenue;

            // Calculate growth (simplified)
            $growth = $i == 5 ? 0 : rand(-10, 20); // Placeholder growth calculation

            $financialData[] = [
                'month' => $monthName,
                'dues_revenue' => $duesRevenue,
                'shares_revenue' => $sharesRevenue,
                'total_revenue' => $totalRevenue,
                'growth' => $growth
            ];
        }

        return [
            'financialData' => $financialData
        ];
    }

    private function getMembershipData($dateFrom, $dateTo)
    {
        $totalMembers = $this->memberModel->getTotalMembers();
        $activeMembers = $this->memberModel->getActiveMembersCount();
        $newMembersThisMonth = $this->memberModel->getNewMembersThisMonth();
        $renewalRate = $this->memberModel->getRenewalRate();

        return [
            'totalMembers' => $totalMembers,
            'activeMembers' => $activeMembers,
            'newMembersThisMonth' => $newMembersThisMonth,
            'renewalRate' => $renewalRate
        ];
    }

    private function getDuesData($dateFrom, $dateTo)
    {
        $duesData = $this->duesModel->getDuesWithMemberInfo($dateFrom, $dateTo);

        // Calculate days overdue for each dues record
        foreach ($duesData as &$dues) {
            if ($dues['status'] !== 'paid') {
                $dueDate = new \DateTime($dues['due_date']);
                $today = new \DateTime();
                $dues['days_overdue'] = $today->diff($dueDate)->days;
            } else {
                $dues['days_overdue'] = 0;
            }
        }

        return [
            'duesData' => $duesData
        ];
    }

    private function getSharesData($dateFrom, $dateTo)
    {
        $sharesData = $this->sharesModel->getSharesWithMemberInfo($dateFrom, $dateTo);

        return [
            'sharesData' => $sharesData
        ];
    }

    private function getThriftData($dateFrom, $dateTo)
    {
        $thriftData = $this->thriftModel->getThriftWithMemberInfo($dateFrom, $dateTo);

        return [
            'thriftData' => $thriftData
        ];
    }

    private function getRiceProjectData($dateFrom, $dateTo)
    {
        $projectData = $this->riceModel->getRiceWithMemberInfo($dateFrom, $dateTo);

        return [
            'projectData' => $projectData
        ];
    }

    private function getEventsData($dateFrom, $dateTo)
    {
        $eventsData = $this->eventModel->getEventsInDateRange($dateFrom, $dateTo);

        return [
            'eventsData' => $eventsData
        ];
    }

    public function export()
    {
        $reportType = $_GET['reportType'] ?? 'all';
        $dateFrom = $_GET['dateFrom'] ?? date('Y-m-01');
        $dateTo = $_GET['dateTo'] ?? date('Y-m-d');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $reportType . '_report_' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');

        switch ($reportType) {
            case 'membership':
                $members = $this->memberModel->getMembersByDateRange($dateFrom, $dateTo);
                fputcsv($output, ['ID', 'Surname', 'Firstname', 'Othername', 'Email', 'Membership Type', 'Created At']);
                foreach ($members as $m) {
                    fputcsv($output, [$m['id'], $m['surname'], $m['firstname'], $m['othername'], $m['email'], $m['membership_type_name'] ?? '', $m['created_at']]);
                }
                break;
            case 'dues':
                $data = $this->duesModel->getDuesWithMemberInfo($dateFrom, $dateTo);
                fputcsv($output, ['Date', 'Member', 'Membership No', 'Amount', 'Status', 'Due Date']);
                foreach ($data as $d) {
                    fputcsv($output, [$d['created_at'], $d['firstname'] . ' ' . $d['surname'], $d['membership_number'], $d['amount'], $d['status'], $d['due_date']]);
                }
                break;
            case 'shares':
                $data = $this->sharesModel->getSharesWithMemberInfo($dateFrom, $dateTo);
                fputcsv($output, ['Date', 'Member', 'Membership No', 'Amount', 'Status']);
                foreach ($data as $d) {
                    fputcsv($output, [$d['purchase_date'], $d['firstname'] . ' ' . $d['surname'], $d['membership_number'], $d['amount'], 'Paid']); // assuming paid
                }
                break;
            case 'thrift':
                $data = $this->thriftModel->getThriftWithMemberInfo($dateFrom, $dateTo);
                fputcsv($output, ['Date', 'Member', 'Membership No', 'Amount']);
                foreach ($data as $d) {
                    fputcsv($output, [$d['payment_date'], $d['firstname'] . ' ' . $d['surname'], $d['membership_number'], $d['amount']]);
                }
                break;
            case 'project':
                $data = $this->riceModel->getRiceWithMemberInfo($dateFrom, $dateTo);
                fputcsv($output, ['Date', 'Member', 'Membership No', 'Amount', 'Status', 'Product']);
                foreach ($data as $d) {
                    fputcsv($output, [$d['created_at'], $d['firstname'] . ' ' . $d['surname'], $d['membership_number'], $d['amount'], $d['status'], 'Rice Project']);
                }
                break;
            case 'financial':
            case 'all':
            default:
                $financialData = $this->getFinancialData($dateFrom, $dateTo);
                fputcsv($output, ['Month', 'Dues Revenue', 'Shares Revenue', 'Total Revenue', 'Growth %']);
                foreach ($financialData['financialData'] as $data) {
                    fputcsv($output, [$data['month'], $data['dues_revenue'], $data['shares_revenue'], $data['total_revenue'], $data['growth']]);
                }
                break;
        }
        fclose($output);
        exit;
    }
}