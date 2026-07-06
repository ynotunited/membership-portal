<?php

namespace App\Controllers;

use App\Models\AnnualDuesModel;
use App\Models\MemberModel;

class DuesController extends BaseController
{
    private $duesModel;
    private $memberModel;

    public function __construct()
    {
        $this->duesModel = new AnnualDuesModel();
        $this->memberModel = new MemberModel();
    }

    public function index()
    {
        $this->requireAdmin();
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = 10;

        $dues = $this->duesModel->getPaginatedDues($currentPage, $perPage, $search, $status);
        $totalDues = $this->duesModel->getTotalDuesCount($search, $status);
        $totalPages = ceil($totalDues / $perPage);

        // If it's an AJAX request, we only return the table part
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            ob_start();
            // The view expects $dues, $totalDues, $currentPage, $totalPages, $search, $status
            // We need to make these available to the included view
            // The view also expects $pageTitle, but for AJAX, it's not strictly necessary for the table part
            $pageTitle = 'Dues List'; // Define for the view
            include dirname(__DIR__) . '/Views/admin/dues/index.php';
            $fullContent = ob_get_clean();

            // Extract the table part from the full content
            if (preg_match('/<!-- RESULTS_START -->(.*)<!-- RESULTS_END -->/s', $fullContent, $matches)) {
                echo $matches[1];
            } else {
                echo $fullContent;
            }
            exit;
        }

        $this->render('admin/dues/index', [
            'dues' => $dues,
            'totalDues' => $totalDues,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'search' => $search,
            'status' => $status,
            'pageTitle' => 'Dues List'
        ]);
    }

    public function add()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $memberId = $_POST['member_id'] ?? '';
            $amount = $_POST['amount'] ?? 0;
            $year = $_POST['year'] ?? date('Y');
            $paymentMethod = $_POST['payment_method'] ?? 'manual';
            $transactionId = $_POST['transaction_id'] ?? '';
            $notes = $_POST['notes'] ?? '';

            if (empty($memberId) || empty($amount)) {
                $this->setFlashMessage('error', 'Member ID and amount are required.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/dues/add');
                exit;
            }

            $result = $this->duesModel->addDues([
                'member_id' => $memberId,
                'amount' => $amount,
                'status' => 'Paid',
                'payment_date' => date('Y-m-d'),
                'notes' => $notes
            ]);

            if ($result) {
                $this->setFlashMessage('success', 'Dues payment recorded successfully.');
            } else {
                $this->setFlashMessage('error', 'Failed to record dues payment.');
            }

            header('Location: ' . \App\Helpers\Url::appUrl() . '/dues');
            exit;
        }

        $members = $this->memberModel->getAllMembers();
        $title = 'Add Dues Payment';
        extract(['members' => $members, 'title' => $title]);
        require dirname(__DIR__) . '/Views/dues/add.php';
    }

    public function edit()
    {
        $this->requireAdmin();
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->setFlashMessage('error', 'Invalid dues ID.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/dues');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amount = $_POST['amount'] ?? 0;
            $year = $_POST['year'] ?? date('Y');
            $status = $_POST['status'] ?? 'paid';
            $paymentMethod = $_POST['payment_method'] ?? 'manual';
            $notes = $_POST['notes'] ?? '';
            $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');

            $data = [
                'amount' => $amount,
                'status' => $status,
                'notes' => $notes,
                'payment_date' => $paymentDate
            ];

            $result = $this->duesModel->updateDues($id, $data);

            if ($result) {
                $this->setFlashMessage('success', 'Dues updated successfully.');
            } else {
                $this->setFlashMessage('error', 'Failed to update dues.');
            }

            header('Location: ' . \App\Helpers\Url::appUrl() . '/dues');
            exit;
        }

        $due = $this->duesModel->getDuesById($id);
        if (!$due) {
            $this->setFlashMessage('error', 'Dues record not found.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/dues');
            exit;
        }

        $member = $this->memberModel->getMemberById($due['member_id']);

        $this->render('admin/dues/edit', [
            'due' => $due,
            'member' => $member,
            'pageTitle' => 'Edit Dues Payment'
        ]);
    }

    public function delete()
    {
        $this->requireAdmin();
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->setFlashMessage('error', 'Invalid dues ID.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/dues');
            exit;
        }

        $result = $this->duesModel->deleteDues($id);

        if ($result) {
            $this->setFlashMessage('success', 'Dues payment deleted successfully.');
        } else {
            $this->setFlashMessage('error', 'Failed to delete dues payment.');
        }

        header('Location: ' . \App\Helpers\Url::appUrl() . '/dues');
        exit;
    }

    public function export()
    {
        $this->requireAdmin();
        $dues = $this->duesModel->getAllDuesWithMemberInfo();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="dues_' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, [
            'Member ID',
            'Membership Number',
            'Firstname',
            'Surname',
            'Chapter',
            'Amount',
            'Year',
            'Payment Method',
            'Transaction ID',
            'Payment Date',
            'Notes'
        ]);
        foreach ($dues as $d) {
            fputcsv($output, [
                $d['member_id'],
                $d['membership_number'],
                $d['firstname'],
                $d['surname'],
                $d['chapter'],
                $d['amount'],
                $d['year'] ?? '',
                $d['payment_method'] ?? '',
                $d['transaction_id'] ?? '',
                $d['payment_date'] ?? $d['paid_at'] ?? '',
                $d['notes']
            ]);
        }
        fclose($output);
        exit;
    }


}