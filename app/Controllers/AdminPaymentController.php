<?php
namespace App\Controllers;

use App\Models\PaymentModel;
use App\Models\AnnualDuesModel;
use App\Models\SharesModel;
use App\Models\ThriftSavingsModel;
use App\Models\MemberModel;
use App\Helpers\Url;

class AdminPaymentController extends BaseController
{
    private $paymentModel;
    private $duesModel;
    private $sharesModel;
    private $thriftModel;
    private $memberModel;

    public function __construct()
    {
        $this->requireAdmin();
        $this->paymentModel = new PaymentModel();
        $this->duesModel = new AnnualDuesModel();
        $this->sharesModel = new SharesModel();
        $this->thriftModel = new ThriftSavingsModel();
        $this->memberModel = new MemberModel();
    }

    public function index()
    {
        $payments = $this->paymentModel->getPendingPayments();
        $this->render('admin/payments/index', [
            'payments' => $payments,
            'pageTitle' => 'Payment Approvals'
        ]);
    }

    public function approve()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $paymentId = $_POST['payment_id'] ?? null;
            if (!$paymentId) {
                $this->setFlashMessage('error', 'Invalid payment ID.');
                header('Location: ' . Url::appUrl() . '/admin/payments');
                exit;
            }

            $payment = $this->paymentModel->getPaymentRecord($paymentId);
            if (!$payment || $payment['status'] !== 'pending') {
                $this->setFlashMessage('error', 'Payment not pending or invalid.');
                header('Location: ' . Url::appUrl() . '/admin/payments');
                exit;
            }

            $member = $this->getMemberByMembershipNumber($payment['membership_number']);
            if (!$member) {
                $this->setFlashMessage('error', 'Member not found for this payment (Membership No: ' . $payment['membership_number'] . ').');
                header('Location: ' . Url::appUrl() . '/admin/payments');
                exit;
            }
            $userId = $member['id'];

            $amount = $payment['amount'] / 100; // Convert to Naira
            $type = $payment['payment_type'] ?? 'general';
            $date = date('Y-m-d');

            $success = false;

            try {
                switch ($type) {
                    case 'annual_dues':
                        $notes = 'Manual payment approved by admin';
                        $success = $this->duesModel->addAnnualDues($userId, $amount, 'paid', $date, $notes);
                        if ($success) {
                            // Update member status
                            $db = $this->memberModel->getConnection();
                            $stmt = $db->prepare("UPDATE members SET annual_dues_status = 'paid', annual_dues_date = ? WHERE id = ?");
                            $stmt->execute([$date, $userId]);
                        }
                        break;
                    case 'shares':
                        $notes = 'Manual payment approved by admin';
                        $shares = floor($amount / 100); // 100 per share
                        $success = $this->sharesModel->addShares($userId, $shares, $amount, 'paid', $date, $notes);
                        break;
                    case 'thrift_savings':
                        $success = $this->thriftModel->addPayment($userId, $amount, $date);
                        break;
                    default:
                        // Just mark as approved
                        $success = true;
                        break;
                }

                if ($success) {
                    $this->paymentModel->updatePaymentStatus($paymentId, 'success', 'ADMIN_APPROVED');
                    $this->setFlashMessage('success', 'Payment approved successfully.');
                } else {
                    $this->setFlashMessage('error', 'Failed to record payment benefit.');
                }

            } catch (\Exception $e) {
                $this->setFlashMessage('error', 'Error approving payment: ' . $e->getMessage());
            }

            header('Location: ' . Url::appUrl() . '/admin/payments');
            exit;
        }
    }

    public function reject()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $paymentId = $_POST['payment_id'] ?? null;
            if (!$paymentId) {
                $this->setFlashMessage('error', 'Invalid payment ID.');
                header('Location: ' . Url::appUrl() . '/admin/payments');
                exit;
            }

            // Verify the payment record exists and is pending before rejecting
            $payment = $this->paymentModel->getPaymentRecord($paymentId);
            if (!$payment || $payment['status'] !== 'pending') {
                $this->setFlashMessage('error', 'Payment not found or is not in a pending state.');
                header('Location: ' . Url::appUrl() . '/admin/payments');
                exit;
            }

            $this->paymentModel->updatePaymentStatus($paymentId, 'failed', null, 'Rejected by Admin');
            $this->setFlashMessage('success', 'Payment rejected.');
            header('Location: ' . Url::appUrl() . '/admin/payments');
            exit;
        }
    }

    private function getMemberByMembershipNumber($number)
    {
        $db = $this->memberModel->getConnection();
        $stmt = $db->prepare("SELECT * FROM members WHERE membership_number = ?");
        $stmt->execute([$number]);
        return $stmt->fetch();
    }
}
