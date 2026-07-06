<?php
namespace App\Controllers;

use App\Models\RiceInvestmentModel;
use App\Models\MemberModel;

class RiceProjectController extends BaseController
{
    private $investmentModel;
    private $memberModel;

    public function __construct()
    {
        $this->investmentModel = new RiceInvestmentModel();
        $this->memberModel = new MemberModel();
    }

    // User Side: Show the presentation page
    public function index()
    {
        $this->requireUser();
        $userId = $_SESSION['user_id'];

        $investments = $this->investmentModel->getInvestmentsByMember($userId);

        $this->render('user/rice-project', [
            'pageTitle' => 'GAFCONL Rice Project',
            'user' => $this->memberModel->getMemberById($userId),
            'investments' => $investments
        ]);
    }

    // User Side: Handle form submission
    public function submit()
    {
        $this->requireUser();
        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amount = $_POST['amount'] ?? 0;
            $notes = $_POST['notes'] ?? '';

            // Handle File Upload
            $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/receipts/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $paymentProof = '';
            if (isset($_FILES['payment_receipt']) && $_FILES['payment_receipt']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['payment_receipt']['tmp_name'];
                $fileName = $_FILES['payment_receipt']['name'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));

                $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'pdf');
                if (in_array($fileExtension, $allowedfileExtensions)) {
                    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                    $dest_path = $uploadDir . $newFileName;

                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $paymentProof = 'uploads/receipts/' . $newFileName;
                    } else {
                        $this->setFlashMessage('error', 'Error moving the file to upload directory.');
                        header('Location: ' . \App\Helpers\Url::appUrl() . '/rice-project');
                        exit;
                    }
                } else {
                    $this->setFlashMessage('error', 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions));
                    header('Location: ' . \App\Helpers\Url::appUrl() . '/rice-project');
                    exit;
                }
            } else {
                $this->setFlashMessage('error', 'Please upload a payment receipt.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/rice-project');
                exit;
            }

            if ($this->investmentModel->createInvestment($userId, $amount, $paymentProof, $notes)) {
                $this->setFlashMessage('success', 'Investment interest submitted successfully! Awaiting admin approval.');
            } else {
                $this->setFlashMessage('error', 'Failed to submit investment interest.');
            }

            header('Location: ' . \App\Helpers\Url::appUrl() . '/dashboard');
            exit;
        }
    }

    // Admin Side: List all investments
    public function adminIndex()
    {
        $this->requireAdmin();
        $investments = $this->investmentModel->getAllInvestmentsWithMemberInfo();

        $this->render('admin/rice-project/index', [
            'investments' => $investments,
            'pageTitle' => 'Rice Project Investments'
        ]);
    }

    // Admin Side: Approve investment
    public function approve()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \App\Helpers\Url::appUrl() . '/admin/rice-project');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            $this->setFlashMessage('error', 'Invalid investment ID.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/admin/rice-project');
            exit;
        }

        // Verify the record exists before changing its status
        $investment = $this->investmentModel->getInvestmentById($id);
        if (!$investment) {
            $this->setFlashMessage('error', 'Investment record not found.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/admin/rice-project');
            exit;
        }

        if ($this->investmentModel->updateStatus($id, 'approved')) {
            $this->setFlashMessage('success', 'Investment approved successfully.');
        } else {
            $this->setFlashMessage('error', 'Failed to approve investment.');
        }

        header('Location: ' . \App\Helpers\Url::appUrl() . '/admin/rice-project');
        exit;
    }

    // Admin Side: Reject investment
    public function reject()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \App\Helpers\Url::appUrl() . '/admin/rice-project');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            $this->setFlashMessage('error', 'Invalid investment ID.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/admin/rice-project');
            exit;
        }

        // Verify the record exists before changing its status
        $investment = $this->investmentModel->getInvestmentById($id);
        if (!$investment) {
            $this->setFlashMessage('error', 'Investment record not found.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/admin/rice-project');
            exit;
        }

        if ($this->investmentModel->updateStatus($id, 'rejected')) {
            $this->setFlashMessage('success', 'Investment rejected.');
        } else {
            $this->setFlashMessage('error', 'Failed to reject investment.');
        }

        header('Location: ' . \App\Helpers\Url::appUrl() . '/admin/rice-project');
        exit;
    }
}
