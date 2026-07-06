<?php

namespace App\Controllers;

use App\Models\SharesModel;
use App\Models\MemberModel;

class SharesController extends BaseController
{
    private $sharesModel;
    private $memberModel;

    public function __construct()
    {
        // parent::__construct();
        $this->sharesModel = new SharesModel();
        $this->memberModel = new MemberModel();
    }

    public function index()
    {
        $this->requireAdmin();
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
        $search = $_GET['search'] ?? '';

        // Get shares with search and pagination
        $shares = $this->sharesModel->getPaginatedShares($page, $perPage, $search);
        $totalCount = $this->sharesModel->getTotalSharesCount($search);

        $totalPages = ceil($totalCount / $perPage);

        // If it's an AJAX request, we only return the table part
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            ob_start();
            $pageTitle = 'Shares List'; // Define for the view
            $totalShares = $totalCount; // View might expect $totalShares
            include dirname(__DIR__) . '/Views/admin/shares/index.php';
            $fullContent = ob_get_clean();

            // Extract the results part from the full content
            if (preg_match('/<!-- RESULTS_START -->(.*)<!-- RESULTS_END -->/s', $fullContent, $matches)) {
                echo $matches[1];
            } else {
                echo $fullContent;
            }
            exit;
        }

        $this->render('admin/shares/index', [
            'shares' => $shares,
            'pageTitle' => 'Shares List',
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'search' => $search,
            'totalShares' => $totalCount
        ]);
    }

    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $memberId = $_POST['member_id'] ?? '';
            $numberOfShares = $_POST['number_of_shares'] ?? 0;
            $amountPerShare = $_POST['amount_per_share'] ?? 0;
            $totalAmount = $_POST['total_amount'] ?? 0;
            $notes = $_POST['notes'] ?? '';

            if (empty($memberId) || empty($numberOfShares) || empty($amountPerShare)) {
                $this->setFlashMessage('error', 'Member ID, number of shares, and amount per share are required.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/shares/add');
                exit;
            }

            $result = $this->sharesModel->addShares(
                $memberId,
                $numberOfShares,
                $totalAmount,
                date('Y-m-d'),
                $notes
            );

            if ($result) {
                $this->setFlashMessage('success', 'Shares purchase recorded successfully.');
            } else {
                $this->setFlashMessage('error', 'Failed to record shares purchase.');
            }

            header('Location: ' . \App\Helpers\Url::appUrl() . '/shares');
            exit;
        }

        $members = $this->memberModel->getAllMembers();
        $title = 'Add Shares Purchase';
        extract(['members' => $members, 'title' => $title]);
        require dirname(__DIR__) . '/Views/shares/add.php';
    }

    public function edit()
    {
        $this->requireAdmin();
        \App\Helpers\PermissionHelper::requirePermission('shares.edit');

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            $this->setFlashMessage('error', 'Invalid share ID.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/shares');
            exit;
        }

        // Ownership check: load record first, fail fast if not found
        $share = $this->sharesModel->getShareById($id);
        if (!$share) {
            $this->setFlashMessage('error', 'Share record not found.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/shares');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Only allow safe, explicitly whitelisted columns
            $data = [
                'number_of_shares' => (int)($_POST['number_of_shares'] ?? 0),
                'amount'           => (float)($_POST['amount'] ?? 0),
                'notes'            => trim($_POST['notes'] ?? ''),
                'purchase_date'    => $_POST['purchase_date'] ?? date('Y-m-d'),
            ];

            $result = $this->sharesModel->updateShare($id, $data);

            if ($result) {
                $this->setFlashMessage('success', 'Share purchase updated successfully.');
            } else {
                $this->setFlashMessage('error', 'Failed to update share purchase.');
            }

            header('Location: ' . \App\Helpers\Url::appUrl() . '/shares');
            exit;
        }

        $member = $this->memberModel->getMemberById($share['member_id']);

        $this->render('admin/shares/edit', [
            'share'     => $share,
            'member'    => $member,
            'pageTitle' => 'Edit Share Purchase',
        ]);
    }

    public function delete()
    {
        $this->requireAdmin();
        \App\Helpers\PermissionHelper::requirePermission('shares.delete');

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            $this->setFlashMessage('error', 'Invalid share ID.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/shares');
            exit;
        }

        // Confirm the record exists before deleting
        if (!$this->sharesModel->getShareById($id)) {
            $this->setFlashMessage('error', 'Share record not found.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/shares');
            exit;
        }

        if ($this->sharesModel->deleteShare($id)) {
            $this->setFlashMessage('success', 'Share purchase deleted successfully.');
        } else {
            $this->setFlashMessage('error', 'Failed to delete share purchase.');
        }

        header('Location: ' . \App\Helpers\Url::appUrl() . '/shares');
        exit;
    }

    public function export()
    {
        $search = $_GET['search'] ?? '';
        $shares = $this->sharesModel->getPaginatedShares(1, 100000, $search); // Get all for export

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="shares_' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, [
            'Member ID',
            'Membership Number',
            'Firstname',
            'Surname',
            'Chapter',
            'Number of Shares',
            'Amount per Share',
            'Total Amount',
            'Purchase Date',
            'Notes'
        ]);
        foreach ($shares as $s) {
            fputcsv($output, [
                $s['member_id'],
                $s['membership_number'],
                $s['firstname'],
                $s['surname'],
                $s['chapter'],
                $s['number_of_shares'],
                number_format($s['amount_per_share'], 2),
                number_format($s['total_amount'], 2),
                $s['purchase_date'],
                $s['notes']
            ]);
        }
        fclose($output);
        exit;
    }
}