<?php

namespace App\Controllers;

use App\Models\RevenueModel;

class RevenueController extends BaseController
{
    private $revenueModel;

    public function __construct()
    {
        $this->revenueModel = new RevenueModel();
    }

    public function index()
    {
        $revenueData = [];
        $fromDate = '';
        $toDate = '';
        $reportType = 'all'; // Default
        $reportGenerated = false;
        $summary = [
            'total_revenue' => 0,
            'registration_revenue' => 0,
            'dues_revenue' => 0,
            'shares_revenue' => 0,
            'total_transactions' => 0
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fromDate = $_POST['fromDate'] ?? '';
            $toDate = $_POST['toDate'] ?? '';
            $reportType = $_POST['reportType'] ?? 'all';
            if ($reportType === 'renewal')
                $reportType = 'registration';

            if (!empty($fromDate) && !empty($toDate)) {
                // Use the new method that handles filtering
                $revenueData = $this->revenueModel->getRevenueByDateRangeAndType($fromDate, $toDate, $reportType);
                // Summary might need to be adjusted or we just show the full summary regardless of filter?
                // Usually summary cards should reflect the current filter or be hidden.
                // For now, let's just keep the global summary as context or recalculate based on filtered data.
                // Let's recalculate summary based on filtered data for accuracy in the view.

                // Manually calculate summary from the filtered $revenueData
                $summary['total_transactions'] = count($revenueData);
                $summary['total_revenue'] = 0;
                $summary['registration_revenue'] = 0;
                $summary['dues_revenue'] = 0;
                $summary['shares_revenue'] = 0;

                foreach ($revenueData as $r) {
                    $amt = $r['total_amount'] ?? $r['amount'] ?? 0;
                    $summary['total_revenue'] += $amt;
                    $rType = strtolower($r['revenue_type'] ?? '');
                    if ($rType === 'registration')
                        $summary['registration_revenue'] += $amt;
                    elseif ($rType === 'annual dues')
                        $summary['dues_revenue'] += $amt;
                    elseif ($rType === 'shares')
                        $summary['shares_revenue'] += $amt;
                }

                $reportGenerated = true;
            }
        }

        $title = 'Revenue Report';
        $data = [
            'revenueData' => $revenueData,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'reportType' => $reportType,
            'reportGenerated' => $reportGenerated,
            'summary' => $summary,
            'title' => $title,
            'pageTitle' => $title
        ];
        $this->render('admin/revenue', $data);
    }

    public function export()
    {
        $fromDate = $_GET['fromDate'] ?? '';
        $toDate = $_GET['toDate'] ?? '';
        $reportType = $_GET['reportType'] ?? 'all';
        if ($reportType === 'renewal')
            $reportType = 'registration';

        $revenueData = $this->revenueModel->getRevenueByDateRangeAndType($fromDate, $toDate, $reportType);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="revenue_report_' . $reportType . '_' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['#', 'Member', 'Membership Number', 'Revenue Type', 'Amount', 'Payment Method', 'Received By', 'Date']);
        $i = 1;
        foreach ($revenueData as $revenue) {
            fputcsv($output, [
                $i++,
                $revenue['member'] ?? $revenue['fullname'] ?? '',
                $revenue['membership_number'] ?? '',
                $revenue['revenue_type'] ?? '',
                $revenue['total_amount'] ?? $revenue['amount'] ?? '',
                $revenue['payment_method'] ?? $revenue['payment_type'] ?? '',
                $revenue['received_by'] ?? $revenue['cash_received_by'] ?? '',
                $revenue['date'] ?? $revenue['renew_date'] ?? ''
            ]);
        }
        fclose($output);
        exit;
    }
}