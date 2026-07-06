<?php
namespace App\Controllers;

use App\Models\MemberModel;
use App\Models\AnnualDuesModel;
use App\Models\SharesModel;
use App\Models\EventModel;
use App\Helpers\Url;
use App\Helpers\RateLimiter;

class SearchController extends BaseController
{
    public function search()
    {
        // ── Auth guard ───────────────────────────────────────────────────────
        $this->requireAdmin();

        // ── Rate limit: 30 search requests per minute per admin user ─────────
        [$max, $win] = RateLimiter::limitsFor('search');
        RateLimiter::enforceForApi(
            'search',
            'user_' . ($_SESSION['user_id'] ?? RateLimiter::clientIp()),
            $max,
            $win
        );

        $q       = trim($_GET['q'] ?? '');
        $results = [];

        // Require a minimum query length to prevent single-char scraping
        if (strlen($q) < 2) {
            header('Content-Type: application/json');
            echo json_encode(['results' => []]);
            exit;
        }

        $baseUrl = Url::appUrl();

        // Use the model's paginated/filtered methods instead of fetching all rows
        $memberModel = new MemberModel();
        $duesModel   = new AnnualDuesModel();
        $sharesModel = new SharesModel();
        $eventModel  = new EventModel();

        // Members — use the existing filtered query (already has LIKE clause)
        foreach ($memberModel->getPaginatedMembers(1, 10, $q) as $m) {
            $results[] = [
                'type'  => 'Member',
                'label' => htmlspecialchars($m['firstname'] . ' ' . $m['surname']),
                'url'   => $baseUrl . '/members/profile?id=' . (int)$m['id'],
            ];
        }

        // Dues — use paginated search
        foreach ($duesModel->getPaginatedDues(1, 5, $q) as $d) {
            $results[] = [
                'type'  => 'Dues',
                'label' => htmlspecialchars('Dues: ' . $d['firstname'] . ' ' . $d['surname'] . ' (₦' . number_format($d['amount']) . ')'),
                'url'   => $baseUrl . '/dues',
            ];
        }

        // Shares — use paginated search
        foreach ($sharesModel->getPaginatedShares(1, 5, $q) as $s) {
            $results[] = [
                'type'  => 'Shares',
                'label' => htmlspecialchars('Shares: ' . $s['firstname'] . ' ' . $s['surname'] . ' (₦' . number_format($s['amount']) . ')'),
                'url'   => $baseUrl . '/shares',
            ];
        }

        // Events — small table, safe to filter in PHP
        foreach ($eventModel->getAllEvents() as $e) {
            if (stripos($e['title'], $q) !== false) {
                $results[] = [
                    'type'  => 'Event',
                    'label' => htmlspecialchars($e['title']),
                    'url'   => $baseUrl . '/events',
                ];
            }
        }

        header('Content-Type: application/json');
        echo json_encode(['results' => array_slice($results, 0, 10)]);
        exit;
    }
}
