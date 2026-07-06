<?php

namespace App\Models;

class RevenueModel extends BaseModel
{
    public function getRevenueByDateRange($fromDate, $toDate)
    {
        $revenueData = [];

        // Get renewal revenue
        $renewalStmt = $this->getConnection()->prepare("
            SELECT 
                CONCAT(m.surname, ' ', m.firstname, ' ', m.othername) AS fullname,
                m.membership_number,
                r.total_amount,
                r.renew_date,
                'Registration' AS revenue_type,
                r.payment_type,
                NULL AS cash_received_by
            FROM renew r
            JOIN members m ON r.member_id = m.id
            WHERE r.renew_date BETWEEN :from_date AND :to_date
            ORDER BY r.renew_date DESC
        ");
        $renewalStmt->execute(['from_date' => $fromDate, 'to_date' => $toDate]);
        $renewals = $renewalStmt->fetchAll();

        foreach ($renewals as $renewal) {
            $revenueData[] = $renewal;
        }

        // Get annual dues revenue
        $duesStmt = $this->getConnection()->prepare("
            SELECT 
                CONCAT(m.surname, ' ', m.firstname, ' ', m.othername) AS fullname,
                m.membership_number,
                ad.amount AS total_amount,
                ad.payment_date AS renew_date,
                'Annual Dues' AS revenue_type,
                'Cash' AS payment_type,
                ad.notes AS cash_received_by
            FROM annual_dues ad
            JOIN members m ON ad.member_id = m.id
            WHERE ad.payment_date BETWEEN :from_date AND :to_date
            ORDER BY ad.payment_date DESC
        ");
        $duesStmt->execute(['from_date' => $fromDate, 'to_date' => $toDate]);
        $dues = $duesStmt->fetchAll();

        foreach ($dues as $due) {
            $revenueData[] = $due;
        }

        // Get shares revenue
        $sharesStmt = $this->getConnection()->prepare("
            SELECT 
                CONCAT(m.surname, ' ', m.firstname, ' ', m.othername) AS fullname,
                m.membership_number,
                s.amount AS total_amount,
                s.purchase_date AS renew_date,
                'Shares' AS revenue_type,
                'Cash' AS payment_type,
                s.notes AS cash_received_by
            FROM shares s
            JOIN members m ON s.member_id = m.id
            WHERE s.purchase_date BETWEEN :from_date AND :to_date
            ORDER BY s.purchase_date DESC
        ");
        $sharesStmt->execute(['from_date' => $fromDate, 'to_date' => $toDate]);
        $shares = $sharesStmt->fetchAll();

        foreach ($shares as $share) {
            $revenueData[] = $share;
        }

        // Get thrift revenue
        $thriftStmt = $this->getConnection()->prepare("
            SELECT 
                CONCAT(m.surname, ' ', m.firstname, ' ', m.othername) AS fullname,
                m.membership_number,
                ts.amount AS total_amount,
                ts.payment_date AS renew_date,
                'Thrift' AS revenue_type,
                'Cash' AS payment_type,
                NULL AS cash_received_by
            FROM thrift_savings ts
            JOIN members m ON ts.user_id = m.id
            WHERE ts.payment_date BETWEEN :from_date AND :to_date
            ORDER BY ts.payment_date DESC
        ");
        $thriftStmt->execute(['from_date' => $fromDate, 'to_date' => $toDate]);
        $thrifts = $thriftStmt->fetchAll();

        foreach ($thrifts as $thrift) {
            $revenueData[] = $thrift;
        }

        // Sort by date descending
        usort($revenueData, function ($a, $b) {
            return strtotime($b['renew_date']) - strtotime($a['renew_date']);
        });

        return $revenueData;
    }

    public function getRevenueSummary($fromDate, $toDate)
    {
        $summary = [
            'total_revenue' => 0,
            'registration_revenue' => 0,
            'dues_revenue' => 0,
            'shares_revenue' => 0,
            'thrift_revenue' => 0,
            'total_transactions' => 0
        ];

        // Get renewal summary
        $renewalStmt = $this->getConnection()->prepare("
            SELECT COUNT(*) as count, SUM(total_amount) as total
            FROM renew 
            WHERE renew_date BETWEEN :from_date AND :to_date
        ");
        $renewalStmt->execute(['from_date' => $fromDate, 'to_date' => $toDate]);
        $renewalData = $renewalStmt->fetch();
        $summary['registration_revenue'] = $renewalData['total'] ?? 0;
        $summary['total_transactions'] += $renewalData['count'] ?? 0;

        // Get dues summary
        $duesStmt = $this->getConnection()->prepare("
            SELECT COUNT(*) as count, SUM(amount) as total
            FROM annual_dues 
            WHERE payment_date BETWEEN :from_date AND :to_date
        ");
        $duesStmt->execute(['from_date' => $fromDate, 'to_date' => $toDate]);
        $duesData = $duesStmt->fetch();
        $summary['dues_revenue'] = $duesData['total'] ?? 0;
        $summary['total_transactions'] += $duesData['count'] ?? 0;

        // Get shares summary
        $sharesStmt = $this->getConnection()->prepare("
            SELECT COUNT(*) as count, SUM(amount) as total
            FROM shares 
            WHERE purchase_date BETWEEN :from_date AND :to_date
        ");
        $sharesStmt->execute(['from_date' => $fromDate, 'to_date' => $toDate]);
        $sharesData = $sharesStmt->fetch();
        $summary['shares_revenue'] = $sharesData['total'] ?? 0;
        $summary['total_transactions'] += $sharesData['count'] ?? 0;

        // Get thrift summary
        $thriftStmt = $this->getConnection()->prepare("
            SELECT COUNT(*) as count, SUM(amount) as total
            FROM thrift_savings 
            WHERE payment_date BETWEEN :from_date AND :to_date
        ");
        $thriftStmt->execute(['from_date' => $fromDate, 'to_date' => $toDate]);
        $thriftData = $thriftStmt->fetch();
        $summary['thrift_revenue'] = $thriftData['total'] ?? 0;
        $summary['total_transactions'] += $thriftData['count'] ?? 0;

        // Calculate total revenue
        $summary['total_revenue'] = $summary['registration_revenue'] + $summary['dues_revenue'] + $summary['shares_revenue'] + $summary['thrift_revenue'];

        return $summary;
    }
    public function getRevenueByDateRangeAndType($fromDate, $toDate, $type = 'all')
    {
        // Reuse the logic from getRevenueByDateRange but with filtering
        // For efficiency, we can just call getRevenueByDateRange and filter in PHP
        // or rewrite queries. For simplicity and since we already fetch everything, let's filter the array.
        // A better approach for huge datasets would be dynamic SQL generation.

        $allData = $this->getRevenueByDateRange($fromDate, $toDate);

        if ($type === 'all' || empty($type)) {
            return $allData;
        }

        return array_filter($allData, function ($item) use ($type) {
            // Map the internal type to the filter value
            // 'Annual Dues' -> 'dues'
            // 'Shares' -> 'shares'
            // 'Registration' -> 'registration'
            // 'Thrift' -> 'thrift'
            $itemType = strtolower($item['revenue_type']);
            if ($type === 'dues' && $itemType === 'annual dues')
                return true;
            if ($type === 'shares' && $itemType === 'shares')
                return true;
            if ($type === 'registration' && $itemType === 'registration')
                return true;
            if ($type === 'thrift' && $itemType === 'thrift')
                return true;
            return false;
        });
    }
}