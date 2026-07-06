<?php
namespace App\Controllers;

use App\Models\ThriftSavingsModel;
use App\Models\MemberModel;

class ThriftController extends BaseController
{
    private $thriftModel;
    private $memberModel;

    public function __construct()
    {
        $this->requireAdmin();
        $this->thriftModel = new ThriftSavingsModel();
        $this->memberModel = new MemberModel();
    }

    public function index()
    {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
        $search = $_GET['search'] ?? '';

        $savings = $this->thriftModel->getPaginatedThrift($page, $perPage, $search);
        $totalSavings = $this->thriftModel->getTotalThriftCount($search);

        $totalPages = ceil($totalSavings / $perPage);

        // If it's an AJAX request, we only return the table part
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            ob_start();
            $pageTitle = 'Thrift Savings List'; // Define for the view
            $currentPage = $page; // Pass to view
            include dirname(__DIR__) . '/Views/admin/thrift/index.php';
            $fullContent = ob_get_clean();

            // Extract the results part from the full content
            if (preg_match('/<!-- RESULTS_START -->(.*)<!-- RESULTS_END -->/s', $fullContent, $matches)) {
                echo $matches[1];
            } else {
                echo $fullContent;
            }
            exit;
        }

        $this->render('admin/thrift/index', [
            'savings' => $savings,
            'pageTitle' => 'Thrift Savings List',
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'search' => $search
        ]);
    }



    public function export()
    {
        $savings = $this->thriftModel->getPaginatedThrift(1, 100000, $_GET['search'] ?? ''); // Get all for export
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="thrift_savings_export_' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Date', 'Member Name', 'Membership No', 'Amount']);

        foreach ($savings as $s) {
            fputcsv($output, [
                $s['payment_date'],
                $s['firstname'] . ' ' . $s['surname'],
                $s['membership_number'],
                $s['amount']
            ]);
        }
        fclose($output);
        exit;
    }
    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $memberId = $_POST['member_id'] ?? '';
            $amount = $_POST['amount'] ?? 0;
            $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');
            $notes = $_POST['notes'] ?? '';

            if (empty($memberId) || empty($amount)) {
                $this->setFlashMessage('error', 'Member and amount are required.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/thrift/add');
                exit;
            }

            // Using addPayment($userId, $amount, $paymentDate, $reference = null)
            $result = $this->thriftModel->addPayment(
                $memberId,
                $amount,
                $paymentDate
            );

            if ($result) {
                $this->setFlashMessage('success', 'Thrift savings recorded successfully.');
            } else {
                $this->setFlashMessage('error', 'Failed to record thrift savings.');
            }

            header('Location: ' . \App\Helpers\Url::appUrl() . '/thrift');
            exit;
        }

        $members = $this->memberModel->getAllMembers();

        $this->render('admin/thrift/add', [
            'members' => $members,
            'pageTitle' => 'Add Thrift Savings'
        ]);
    }
    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->setFlashMessage('error', 'Invalid savings ID.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/thrift');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amount = $_POST['amount'] ?? 0;
            $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');

            if (empty($amount)) {
                $this->setFlashMessage('error', 'Amount is required.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/thrift/edit?id=' . $id);
                exit;
            }

            $result = $this->thriftModel->updatePayment($id, $amount, $paymentDate);

            if ($result) {
                $this->setFlashMessage('success', 'Thrift savings updated successfully.');
            } else {
                $this->setFlashMessage('error', 'Failed to update thrift savings.');
            }

            header('Location: ' . \App\Helpers\Url::appUrl() . '/thrift');
            exit;
        }

        $saving = $this->thriftModel->getPaymentById($id);
        if (!$saving) {
            $this->setFlashMessage('error', 'Savings record not found.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/thrift');
            exit;
        }

        $member = $this->memberModel->getMemberById($saving['user_id']); // member_id is user_id in thrift table

        $this->render('admin/thrift/edit', [
            'saving' => $saving,
            'member' => $member,
            'pageTitle' => 'Edit Thrift Savings'
        ]);
    }

    public function delete()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->setFlashMessage('error', 'Invalid savings ID.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/thrift');
            exit;
        }

        $result = $this->thriftModel->deletePayment($id);

        if ($result) {
            $this->setFlashMessage('success', 'Thrift savings deleted successfully.');
        } else {
            $this->setFlashMessage('error', 'Failed to delete thrift savings.');
        }

        header('Location: ' . \App\Helpers\Url::appUrl() . '/thrift');
        exit;
    }
}
