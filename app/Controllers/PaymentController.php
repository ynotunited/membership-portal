<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PaymentModel;
use App\Models\MemberModel;
use App\Helpers\Url;

class PaymentController extends BaseController
{
    public function __construct()
    {
        $this->requireAdmin();
    }

    public function index() { $this->render('payments/index'); }
    public function history() { $this->render('payments/history'); }
    public function gateway()
    {
        $error = $success = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = \App\Helpers\SecurityHelper::sanitizeString($_POST['email'] ?? '');
            $amount = intval($_POST['amount'] ?? 0) * 100; // in kobo
            $membershipNumber = \App\Helpers\SecurityHelper::sanitizeString($_POST['membership_number'] ?? '');
            $phone = \App\Helpers\SecurityHelper::sanitizeString($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';
            $file = $_FILES['payment_proof'] ?? null;
            $callbackUrl = Url::appUrl() . '/payments/gateway';

            // Phone format by country (Nigeria, US, UK, Ghana)
            $countryPatterns = [
                'NG' => '/^\+234[0-9]{10}$/',
                'US' => '/^\+1[0-9]{10}$/',
                'UK' => '/^\+44[0-9]{10}$/',
                'GH' => '/^\+233[0-9]{9}$/',
            ];
            $validPhone = false;
            foreach ($countryPatterns as $pattern) {
                if (preg_match($pattern, $phone)) {
                    $validPhone = true;
                    break;
                }
            }
            if (!$validPhone) {
                $error = 'Invalid phone number format for supported countries.';
            } else {
                // Phone uniqueness (check members table)
                $memberModel = new MemberModel();
                $stmt = $memberModel->getConnection()->prepare("SELECT id FROM members WHERE contact_number = :phone");
                $stmt->execute(['phone' => $phone]);
                if ($stmt->fetch()) {
                    $error = 'Phone number already exists.';
                }
            }
            // Email verification placeholder (simulate as always verified)
            $emailVerified = true; // Set to false to simulate unverified
            if (!$emailVerified) {
                $error = 'Email not verified. Please check your inbox.';
            }
            if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password) || !preg_match('/[!@#$%^&*]/', $password)) {
                $error = 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character.';
            } elseif ($file && isset($file['tmp_name']) && $file['tmp_name']) {
                $allowedExts = ['jpg', 'jpeg', 'png', 'pdf'];
                $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];
                $maxSize = 5 * 1024 * 1024; // 5MB
                $dest = __DIR__ . '/../../public/uploads/payment_proofs';
                
                $res = \App\Helpers\SecurityHelper::handleSecureUpload($file, $allowedExts, $allowedMimes, $maxSize, $dest, 'payment_');
                if (is_array($res) && isset($res['error'])) {
                    $error = $res['error'];
                }
            }
            if (!$error) {
                if (!$email || !$amount || !$membershipNumber) {
                    $error = 'All fields are required.';
                } else {
                    $model = new PaymentModel();
                    $result = $model->initializePayment($email, $amount, $membershipNumber, $callbackUrl);
                    if ($result && isset($result['data']['authorization_url'])) {
                        header('Location: ' . $result['data']['authorization_url']);
                        exit;
                    } else {
                        $error = 'Failed to initialize payment: ' . ($result['message'] ?? 'Unknown error');
                    }
                }
            }
        }
        $this->render('payments/gateway', ['error' => $error, 'success' => $success]);
    }
} 