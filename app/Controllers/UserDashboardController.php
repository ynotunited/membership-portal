<?php
namespace App\Controllers;

use App\Models\MemberModel;
use App\Models\AnnualDuesModel;
use App\Models\SharesModel;
use App\Models\EventModel;

class UserDashboardController extends BaseController
{
    private $memberModel;
    private $duesModel;
    private $sharesModel;
    private $eventModel;

    public function __construct()
    {
        $this->requireUser();
        $this->memberModel = new MemberModel();
        $this->duesModel = new AnnualDuesModel();
        $this->sharesModel = new SharesModel();
        $this->eventModel = new EventModel();
        $this->checkPaywall();
    }

    private function checkPaywall()
    {
        $userId = $_SESSION['user_id'];
        
        // Don't loop infinitely if we are already on the paywall or payment routes
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($currentUri, '/paywall') !== false || strpos($currentUri, '/paystack') !== false) {
            return;
        }

        // Example Logic: Check if dues are paid for the current year
        $currentYear = date('Y');
        $hasPaid = $this->duesModel->hasPaidForYear($userId, $currentYear);

        if (!$hasPaid) {
            header('Location: ' . \App\Helpers\Url::appUrl() . '/member/paywall');
            exit;
        }
    }

    public function index()
    {
        $userId = $_SESSION['user_id'];

        // Get user details
        $user = $this->memberModel->getMemberById($userId);

        // Get user statistics
        $stats = $this->getUserStats($userId);

        // Get recent activity
        $recentActivity = $this->getRecentActivity($userId);

        // Get upcoming events
        $upcomingEvents = $this->eventModel->getUpcomingEvents(5);

        // Get thrift savings data
        $thriftData = $this->getThriftSavings($userId);

        $this->renderUserLayout('user/dashboard', [
            'title' => 'Member Dashboard',
            'pageTitle' => 'Dashboard',
            'pageSubtitle' => 'Welcome back! Here\'s your membership overview.',
            'user' => $user,
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'upcomingEvents' => $upcomingEvents,
            'thriftData' => $thriftData
        ]);
    }

    public function paywall()
    {
        $this->renderUserLayout('user/paywall', [
            'title' => 'Annual Dues Required',
            'pageTitle' => 'Paywall'
        ]);
    }

    public function paystackInitiate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \App\Helpers\Url::appUrl() . '/member/paywall');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user = $this->memberModel->getMemberById($userId);
        
        $reference = 'DUES_' . $userId . '_' . time();
        $amount = 10000; // NGN 10,000

        try {
            $paystack = new \App\Helpers\PaystackService();
            $response = $paystack->initializeTransaction(
                $user['email'] ?? 'member'.$userId.'@gafconl.com', 
                $amount, 
                $reference,
                ['user_id' => $userId, 'type' => 'annual_dues', 'year' => date('Y')]
            );

            if ($response['status'] === true) {
                header('Location: ' . $response['data']['authorization_url']);
                exit;
            } else {
                die('Paystack Error: ' . $response['message']);
            }
        } catch (\Exception $e) {
            die('Payment Initiation Failed: ' . $e->getMessage());
        }
    }

    public function profile()
    {
        $userId = $_SESSION['user_id'];
        $user = $this->memberModel->getMemberById($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle profile update
            $this->updateProfile($userId);
        }

        $this->renderUserLayout('user/profile', [
            'title' => 'Update Profile',
            'pageTitle' => 'Update Profile',
            'pageSubtitle' => 'Update your membership information.',
            'user' => $user
        ]);
    }

    private function updateProfile($userId)
    {
        // Validate required fields
        $title = trim($_POST['title'] ?? '');
        $firstname = trim($_POST['firstname'] ?? '');
        $surname = trim($_POST['surname'] ?? '');
        $gender = trim($_POST['gender'] ?? '');
        $marital_status = trim($_POST['marital_status'] ?? '');
        $dob = $_POST['dob'] ?? '';
        $email = trim($_POST['email'] ?? '');
        $contact_number = trim($_POST['contact_number'] ?? '');
        $country = trim($_POST['country'] ?? '');
        $state_district = trim($_POST['state_district'] ?? '');
        $lga = trim($_POST['lga'] ?? '');
        $city_town = trim($_POST['city_town'] ?? '');
        $nearest_bus_stop = trim($_POST['nearest_bus_stop'] ?? '');
        $street_name = trim($_POST['street_name'] ?? '');
        $house_no = trim($_POST['house_no'] ?? '');
        $identity_type = trim($_POST['identity_type'] ?? '');
        $id_number = trim($_POST['id_number'] ?? '');
        $date_of_issue = $_POST['date_of_issue'] ?? '';
        $chapter = trim($_POST['chapter'] ?? '');

        // Check required fields
        $requiredFields = [
            'title',
            'firstname',
            'surname',
            'gender',
            'marital_status',
            'dob',
            'email',
            'contact_number',
            'country',
            'state_district',
            'lga',
            'city_town',
            'nearest_bus_stop',
            'street_name',
            'house_no',
            'identity_type',
            'id_number',
            'date_of_issue',
            'chapter'
        ];

        foreach ($requiredFields as $field) {
            if (empty($$field)) {
                $this->setFlashMessage('error', 'Please fill in all required fields.');
                return;
            }
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlashMessage('error', 'Please enter a valid email address.');
            return;
        }

        // Check if email is already taken by another member
        $db = $this->memberModel->getConnection();
        $stmt = $db->prepare("SELECT id FROM members WHERE email = ? AND id != ?");
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            $this->setFlashMessage('error', 'Email address is already taken by another member.');
            return;
        }

        // Process phone numbers with country codes
        $phone_country_code = $_POST['phone_country_code'] ?? '+234';
        $whatsapp_country_code = $_POST['whatsapp_country_code'] ?? '+234';
        $whatsapp_number = trim($_POST['whatsapp_number'] ?? '');

        $full_contact_number = $phone_country_code . $contact_number;
        $full_whatsapp_number = $whatsapp_country_code . $whatsapp_number;

        // Prepare update data
        $updateData = [
            'title' => $title,
            'firstname' => $firstname,
            'surname' => $surname,
            'othername' => trim($_POST['othername'] ?? ''),
            'gender' => $gender,
            'marital_status' => $marital_status,
            'dob' => $dob,
            'email' => $email,
            'contact_number' => $full_contact_number,
            'whatsapp_number' => $full_whatsapp_number,
            'country' => $country,
            'state_district' => $state_district,
            'lga' => $lga,
            'city_town' => $city_town,
            'nearest_bus_stop' => $nearest_bus_stop,
            'street_name' => $street_name,
            'house_no' => $house_no,
            'business_name' => trim($_POST['business_name'] ?? ''),
            'nature_of_business' => trim($_POST['nature_of_business'] ?? ''),
            'sub_sector' => trim($_POST['sub_sector'] ?? ''),
            'business_address' => trim($_POST['business_address'] ?? ''),
            'identity_type' => $identity_type,
            'id_number' => $id_number,
            'date_of_issue' => $date_of_issue,
            'chapter' => $chapter,
            'zone' => trim($_POST['zone'] ?? '')
        ];

        // Handle file upload if provided
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/member_photos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileInfo = pathinfo($_FILES['photo']['name']);
            $extension = strtolower($fileInfo['extension']);

            // Validate file type
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($extension, $allowedTypes)) {
                $this->setFlashMessage('error', 'Please upload a valid image file (JPG, PNG, GIF).');
                return;
            }

            // Validate file size (max 5MB)
            if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
                $this->setFlashMessage('error', 'Image file size must be less than 5MB.');
                return;
            }

            // Generate unique filename
            $filename = 'member_' . $userId . '_' . time() . '.' . $extension;
            $uploadPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
                $updateData['photo'] = $filename;
            }
        }

        // Handle identity card upload
        if (isset($_FILES['nin_card']) && $_FILES['nin_card']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/nin_cards/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileInfo = pathinfo($_FILES['nin_card']['name']);
            $extension = strtolower($fileInfo['extension']);

            // Validate file type
            $allowedTypes = ['jpg', 'jpeg', 'png'];
            if (!in_array($extension, $allowedTypes)) {
                $this->setFlashMessage('error', 'Please upload a valid image file for identity card (JPG, PNG).');
                return;
            }

            // Validate file size (max 5MB)
            if ($_FILES['nin_card']['size'] > 5 * 1024 * 1024) {
                $this->setFlashMessage('error', 'Identity card file size must be less than 5MB.');
                return;
            }

            // Generate unique filename
            $filename = 'nin_' . $userId . '_' . time() . '.' . $extension;
            $uploadPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['nin_card']['tmp_name'], $uploadPath)) {
                $updateData['nin_card'] = $filename;
            }
        }

        // Handle signature upload
        if (isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/signatures/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileInfo = pathinfo($_FILES['signature']['name']);
            $extension = strtolower($fileInfo['extension']);

            // Validate file type
            $allowedTypes = ['jpg', 'jpeg', 'png'];
            if (!in_array($extension, $allowedTypes)) {
                $this->setFlashMessage('error', 'Please upload a valid image file for signature (JPG, PNG).');
                return;
            }

            // Validate file size (max 5MB)
            if ($_FILES['signature']['size'] > 5 * 1024 * 1024) {
                $this->setFlashMessage('error', 'Signature file size must be less than 5MB.');
                return;
            }

            // Generate unique filename
            $filename = 'signature_' . $userId . '_' . time() . '.' . $extension;
            $uploadPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['signature']['tmp_name'], $uploadPath)) {
                $updateData['signature'] = $filename;
            }
        }

        // Build SQL update statement
        $sql = "UPDATE members SET 
                title = ?, firstname = ?, surname = ?, othername = ?, gender = ?, 
                marital_status = ?, dob = ?, email = ?, contact_number = ?, 
                whatsapp_number = ?, country = ?, state_district = ?, lga = ?, 
                city_town = ?, nearest_bus_stop = ?, street_name = ?, house_no = ?, 
                business_name = ?, nature_of_business = ?, sub_sector = ?, 
                business_address = ?, identity_type = ?, id_number = ?, 
                date_of_issue = ?, chapter = ?, zone = ?";

        $params = [
            $updateData['title'],
            $updateData['firstname'],
            $updateData['surname'],
            $updateData['othername'],
            $updateData['gender'],
            $updateData['marital_status'],
            $updateData['dob'],
            $updateData['email'],
            $updateData['contact_number'],
            $updateData['whatsapp_number'],
            $updateData['country'],
            $updateData['state_district'],
            $updateData['lga'],
            $updateData['city_town'],
            $updateData['nearest_bus_stop'],
            $updateData['street_name'],
            $updateData['house_no'],
            $updateData['business_name'],
            $updateData['nature_of_business'],
            $updateData['sub_sector'],
            $updateData['business_address'],
            $updateData['identity_type'],
            $updateData['id_number'],
            $updateData['date_of_issue'],
            $updateData['chapter'],
            $updateData['zone']
        ];

        // Add photo to update if uploaded
        if (isset($updateData['photo'])) {
            $sql .= ", photo = ?";
            $params[] = $updateData['photo'];
        }

        // Add nin_card to update if uploaded
        if (isset($updateData['nin_card'])) {
            $sql .= ", nin_card = ?";
            $params[] = $updateData['nin_card'];
        }

        // Add signature to update if uploaded
        if (isset($updateData['signature'])) {
            $sql .= ", signature = ?";
            $params[] = $updateData['signature'];
        }

        $sql .= " WHERE id = ?";
        $params[] = $userId;

        $stmt = $db->prepare($sql);

        if ($stmt->execute($params)) {
            $this->setFlashMessage('success', 'Profile updated successfully.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/member/profile');
            exit;
        } else {
            $this->setFlashMessage('error', 'Failed to update profile. Please try again.');
        }
    }

    public function dues()
    {
        $userId = $_SESSION['user_id'];
        $user = $this->memberModel->getMemberById($userId);

        // Get dues history
        $duesHistory = $this->duesModel->getMemberDuesHistory($userId);

        // Calculate status based on Calendar Year (Jan 1 - Dec 31)
        $currentYear = date('Y');
        $hasPaidThisYear = $this->duesModel->hasPaidForYear($userId, $currentYear);

        // Force status to be correct based on calendar year
        $user['annual_dues_status'] = $hasPaidThisYear ? 'paid' : 'unpaid';

        $isOverdue = !$hasPaidThisYear;
        $nextDueDate = $hasPaidThisYear ? ($currentYear + 1) . '-01-01' : date('Y-m-d');

        $this->renderUserLayout('user/dues', [
            'title' => 'Annual Dues',
            'pageTitle' => 'Annual Dues',
            'pageSubtitle' => 'Manage your annual membership dues payments.',
            'user' => $user,
            'duesHistory' => $duesHistory,
            'isOverdue' => $isOverdue,
            'nextDueDate' => $nextDueDate
        ]);
    }

    public function payDues()
    {
        $userId = $_SESSION['user_id'];
        $user   = $this->memberModel->getMemberById($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Rate-limit payment initiation: 5 per 5 min per user
            [$max, $win] = \App\Helpers\RateLimiter::limitsFor('payment_init');
            \App\Helpers\RateLimiter::enforceForHtml(
                'payment_init', 'user_' . $userId, $max, $win,
                \App\Helpers\Url::appUrl() . '/member/dues'
            );

            // Client-generated idempotency key (UUID v4) — prevents duplicate charges
            // on network retries. Client must generate a fresh UUID per new payment intent.
            $idempotencyKey = trim($_POST['idempotency_key'] ?? '');
            if (empty($idempotencyKey)) {
                $idempotencyKey = \App\Models\PaymentIdempotency::isValidUuid($_SERVER['HTTP_X_IDEMPOTENCY_KEY'] ?? '')
                    ? $_SERVER['HTTP_X_IDEMPOTENCY_KEY']
                    : '';
            }

            $amount = 12000; // ₦12,000 annual dues
            $email = $user['email'];
            $membershipNumber = $user['membership_number'];
            $gateway = $_POST['payment_gateway'] ?? 'paystack';

            // Check if this is an early renewal
            $isEarlyRenewal = ($user['annual_dues_status'] ?? 'unpaid') === 'paid';

            // Initialize payment
            $paymentModel = new \App\Models\PaymentModel();
            $callbackUrl = \App\Helpers\Url::appUrl() . '/member/dues/payment-callback';

            $paymentData = $paymentModel->initializePayment($email, $amount * 100, $membershipNumber, $callbackUrl, $gateway, 'annual_dues', $idempotencyKey);

            if ($paymentData['status']) {
                // Handle Manual Payment
                if ($gateway === 'manual') {
                    $this->setFlashMessage('success', 'Payment notification submitted! Please complete the transfer to the provided account. Your payment is pending approval.');
                    header('Location: ' . \App\Helpers\Url::appUrl() . '/member/dues');
                    exit;
                }

                // Store payment intent in session for verification
                $_SESSION['payment_reference'] = $paymentData['data']['reference'];
                $_SESSION['payment_amount'] = $amount;
                $_SESSION['payment_type'] = 'annual_dues';
                $_SESSION['is_early_renewal'] = $isEarlyRenewal;
                $_SESSION['payment_gateway'] = $gateway;
                $_SESSION['payment_id'] = $paymentData['data']['id'] ?? $paymentData['data']['payment_id']; // Store payment ID

                // Redirect to payment gateway
                header('Location: ' . $paymentData['data']['authorization_url']);
                exit;
            } else {
                $this->setFlashMessage('error', 'Failed to initialize payment. Please try again.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/member/dues');
                exit;
            }
        }

        // Show payment form
        $title = 'Pay Annual Dues';
        $pageTitle = 'Pay Annual Dues';
        $pageSubtitle = 'Complete your annual membership dues payment.';

        ob_start();

        if (!defined('LAYOUT_INCLUDED')) {
            define('LAYOUT_INCLUDED', true);
        }

        require_once __DIR__ . '/../Views/user/pay-dues.php';

        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/user.php';
    }

    public function paymentCallback()
    {
        // Handle both GET (from real payment gateway) and POST (from mock payment form)
        $reference   = $_GET['reference'] ?? $_POST['reference'] ?? '';
        $status      = $_GET['status'] ?? $_POST['status'] ?? '';
        $gateway     = $_SESSION['payment_gateway'] ?? 'paystack';
        $paymentId   = $_GET['payment_id'] ?? $_POST['payment_id'] ?? null;

        if (empty($reference)) {
            $this->setFlashMessage('error', 'Invalid payment reference.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/member/dues');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user   = $this->memberModel->getMemberById($userId);

        // ── CRITICAL IDOR PROTECTION ──────────────────────────────────────────
        // If a payment_id is provided, verify ownership before marking it success.
        // Otherwise an attacker can pass another user's payment_id in the callback.
        if ($paymentId) {
            $paymentModel  = new \App\Models\PaymentModel();
            $paymentRecord = $paymentModel->getPaymentRecordByMember($paymentId);

            if (!$paymentRecord) {
                $this->setFlashMessage('error', 'Payment record not found.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/member/dues');
                exit;
            }

            // Ownership check: the payment's membership_number must match the logged-in user
            if ($paymentRecord['membership_number'] !== $user['membership_number']) {
                \App\Helpers\SecurityLogger::idorAttempt(
                    'dues_payment_callback',
                    $user['membership_number'],
                    $paymentRecord['membership_number']
                );
                $this->setFlashMessage('error', 'Access denied: this payment does not belong to you.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/member/dues');
                exit;
            }
        }

        $paymentModel = new \App\Models\PaymentModel();

        // For demo mode, if status is success, proceed with payment
        if ($status === 'success') {
            $userId = $_SESSION['user_id'];
            $amount = 12000; // ₦12,000
            $isEarlyRenewal = $_SESSION['is_early_renewal'] ?? false;

            // Update payment status to success
            if ($paymentId) {
                $paymentModel->updatePaymentStatus($paymentId, 'success', $reference);
            }

            // Record the payment in annual_dues table
            $paymentDate = date('Y-m-d');
            $notes = $isEarlyRenewal ? 'Early renewal payment via ' . ucfirst($gateway) . ' Gateway' : 'Online payment via ' . ucfirst($gateway) . ' Gateway';
            $success = $this->duesModel->addAnnualDues($userId, $amount, 'paid', $paymentDate, $notes);

            if ($success) {
                // Update member's dues status
                $db = $this->memberModel->getConnection();
                $stmt = $db->prepare("UPDATE members SET annual_dues_status = 'paid', annual_dues_date = ? WHERE id = ?");
                $stmt->execute([$paymentDate, $userId]);

                if ($isEarlyRenewal) {
                    $this->setFlashMessage('success', 'Early renewal successful! Your membership has been extended.');
                } else {
                    $this->setFlashMessage('success', 'Payment successful! Your annual dues have been paid.');
                }
            } else {
                $this->setFlashMessage('error', 'Payment verified but failed to record. Please contact support.');
            }

            // Clear session payment data
            $this->clearPaymentSession();

            header('Location: ' . \App\Helpers\Url::appUrl() . '/member/dues');
            exit;
        }

        // Handle payment failure
        if ($status === 'failed' || $status === 'cancelled') {
            $errorMessage = $_GET['message'] ?? $_POST['message'] ?? 'Payment was cancelled or failed';

            // Update payment status to failed
            if ($paymentId) {
                $paymentModel->updatePaymentStatus($paymentId, 'failed', $reference, $errorMessage);
            }

            $this->setFlashMessage('error', 'Payment failed: ' . $errorMessage);
            $this->clearPaymentSession();

            header('Location: ' . \App\Helpers\Url::appUrl() . '/member/dues');
            exit;
        }

        // For real payment gateway, verify payment
        $paymentData = $paymentModel->verifyPayment($reference, $gateway);

        if ($paymentData['status'] && $paymentData['data']['status'] === 'success') {
            $userId = $_SESSION['user_id'];
            $amount = $paymentData['data']['amount'] / 100; // Convert from kobo to naira
            $isEarlyRenewal = $_SESSION['is_early_renewal'] ?? false;

            // Update payment status to success
            if ($paymentId) {
                $paymentModel->updatePaymentStatus($paymentId, 'success', $reference);
            }

            // Record the payment in annual_dues table
            $paymentDate = date('Y-m-d');
            $notes = $isEarlyRenewal ? 'Early renewal payment via ' . ucfirst($gateway) : 'Online payment via ' . ucfirst($gateway);
            $success = $this->duesModel->addAnnualDues($userId, $amount, 'paid', $paymentDate, $notes);

            if ($success) {
                // Update member's dues status
                $db = $this->memberModel->getConnection();
                $stmt = $db->prepare("UPDATE members SET annual_dues_status = 'paid', annual_dues_date = ? WHERE id = ?");
                $stmt->execute([$paymentDate, $userId]);

                if ($isEarlyRenewal) {
                    $this->setFlashMessage('success', 'Early renewal successful! Your membership has been extended.');
                } else {
                    $this->setFlashMessage('success', 'Payment successful! Your annual dues have been paid.');
                }
            } else {
                $this->setFlashMessage('error', 'Payment verified but failed to record. Please contact support.');
            }
        } else {
            // Payment verification failed
            $errorMessage = $paymentData['message'] ?? 'Payment verification failed';

            // Update payment status to failed
            if ($paymentId) {
                $paymentModel->updatePaymentStatus($paymentId, 'failed', $reference, $errorMessage);
            }

            $this->setFlashMessage('error', 'Payment verification failed: ' . $errorMessage);
        }

        // Clear session payment data
        $this->clearPaymentSession();

        header('Location: ' . \App\Helpers\Url::appUrl() . '/member/dues');
        exit;
    }

    public function retryPayment()
    {
        $userId = $_SESSION['user_id'];
        $user = $this->memberModel->getMemberById($userId);
        $paymentId = $_POST['payment_id'] ?? null;

        if (!$paymentId) {
            $this->setFlashMessage('error', 'Invalid payment ID.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/member/dues');
            exit;
        }

        $paymentModel = new \App\Models\PaymentModel();
        $paymentRecord = $paymentModel->getPaymentRecordByMember($paymentId);

        if (!$paymentRecord || $paymentRecord['email'] !== $user['email']) {
            $this->setFlashMessage('error', 'Payment record not found or access denied.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/member/dues');
            exit;
        }

        // Create new payment attempt
        $amount = $paymentRecord['amount'];
        $email = $paymentRecord['email'];
        $membershipNumber = $paymentRecord['membership_number'];
        $gateway = $paymentRecord['gateway'];
        $callbackUrl = \App\Helpers\Url::appUrl() . '/member/dues/payment-callback';

        $paymentData = $paymentModel->initializePayment($email, $amount, $membershipNumber, $callbackUrl, $gateway, $paymentRecord['payment_type'] ?? 'annual_dues');

        if ($paymentData['status']) {
            // Store payment intent in session for verification
            $_SESSION['payment_reference'] = $paymentData['data']['reference'];
            $_SESSION['payment_amount'] = $amount / 100; // Convert back to naira
            $_SESSION['payment_type'] = 'annual_dues';
            $_SESSION['payment_gateway'] = $gateway;
            $_SESSION['payment_id'] = $paymentData['data']['payment_id'];
            $_SESSION['is_retry'] = true;
            $_SESSION['original_payment_id'] = $paymentId;

            // Redirect to payment gateway
            header('Location: ' . $paymentData['data']['authorization_url']);
            exit;
        } else {
            $this->setFlashMessage('error', 'Failed to retry payment: ' . $paymentData['message']);
            header('Location: ' . \App\Helpers\Url::appUrl() . '/member/dues');
            exit;
        }
    }

    private function clearPaymentSession()
    {
        unset($_SESSION['payment_reference']);
        unset($_SESSION['payment_amount']);
        unset($_SESSION['payment_type']);
        unset($_SESSION['is_early_renewal']);
        unset($_SESSION['payment_gateway']);
        unset($_SESSION['payment_id']);
    }

    public function shares()
    {
        $userId = $_SESSION['user_id'];
        $user = $this->memberModel->getMemberById($userId);

        // Get shares data
        $sharesData = $this->sharesModel->getMemberShares($userId);
        $sharesHistory = $this->sharesModel->getMemberSharesHistory($userId);

        // Calculate total shares
        $totalShares = (int) ($sharesData['total_shares'] ?? 0);

        $this->renderUserLayout('user/shares', [
            'title' => 'Buy Shares',
            'pageTitle' => 'Buy Shares',
            'pageSubtitle' => 'Purchase cooperative shares and track your investments.',
            'user' => $user,
            'sharesData' => $sharesData,
            'sharesHistory' => $sharesHistory,
            'totalShares' => $totalShares
        ]);
    }

    public function payShares()
    {
        $userId = $_SESSION['user_id'];
        $user   = $this->memberModel->getMemberById($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Rate-limit payment initiation: 5 per 5 min per user
            [$max, $win] = \App\Helpers\RateLimiter::limitsFor('payment_init');
            \App\Helpers\RateLimiter::enforceForHtml(
                'payment_init', 'user_' . $userId, $max, $win,
                \App\Helpers\Url::appUrl() . '/member/shares'
            );

            $amountToInvest = (float)($_POST['amount_to_invest'] ?? 10000);
            $gateway        = $_POST['payment_gateway'] ?? 'paystack';

            // Validate minimum investment
            if ($amountToInvest < 10000) {
                $this->setFlashMessage('error', 'Minimum investment amount is ₦10,000 (100 shares at ₦100 each).');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/member/shares');
                exit;
            }

            // Calculate shares based on amount
            $shares = floor($amountToInvest / 100); // ₦100 per share

            if ($shares < 100) {
                $this->setFlashMessage('error', 'Investment amount must be at least ₦10,000 to get minimum 100 shares.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/member/shares');
                exit;
            }

            $email = $user['email'];
            $membershipNumber = $user['membership_number'];

            // Initialize payment
            $paymentModel   = new \App\Models\PaymentModel();
            $callbackUrl    = \App\Helpers\Url::appUrl() . '/member/shares/payment-callback';
            $idempotencyKey = trim($_POST['idempotency_key'] ?? '');
            if (empty($idempotencyKey)) {
                $hdrKey = $_SERVER['HTTP_X_IDEMPOTENCY_KEY'] ?? '';
                $idempotencyKey = \App\Models\PaymentIdempotency::isValidUuid($hdrKey) ? $hdrKey : '';
            }

            $paymentData = $paymentModel->initializePayment($email, (int)($amountToInvest * 100), $membershipNumber, $callbackUrl, $gateway, 'shares', $idempotencyKey);

            if ($paymentData['status']) {
                // Handle Manual Payment
                if ($gateway === 'manual') {
                    $this->setFlashMessage('success', 'Share purchase request submitted! Please complete the transfer to the provided account. Your request is pending approval.');
                    header('Location: ' . \App\Helpers\Url::appUrl() . '/member/shares');
                    exit;
                }

                // Store payment intent in session for verification
                $_SESSION['payment_reference'] = $paymentData['data']['reference'];
                $_SESSION['payment_amount'] = $amountToInvest;
                $_SESSION['payment_type'] = 'shares';
                $_SESSION['payment_gateway'] = $gateway;
                $_SESSION['payment_id'] = $paymentData['data']['payment_id'];
                $_SESSION['shares_count'] = $shares;

                // Redirect to payment gateway
                header('Location: ' . $paymentData['data']['authorization_url']);
                exit;
            } else {
                $this->setFlashMessage('error', 'Failed to initialize payment. Please try again.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/member/shares');
                exit;
            }
        }

        // Show payment form
        $title = 'Purchase Shares';
        $pageTitle = 'Purchase Shares';
        $pageSubtitle = 'Complete your share purchase payment.';

        ob_start();

        if (!defined('LAYOUT_INCLUDED')) {
            define('LAYOUT_INCLUDED', true);
        }

        require_once __DIR__ . '/../Views/user/pay-shares.php';

        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/user.php';
    }

    public function sharesPaymentCallback()
    {
        // Handle both GET (from real payment gateway) and POST (from mock payment form)
        $reference = $_GET['reference'] ?? $_POST['reference'] ?? '';
        $status    = $_GET['status'] ?? $_POST['status'] ?? '';
        $gateway   = $_SESSION['payment_gateway'] ?? 'paystack';
        $paymentId = $_GET['payment_id'] ?? $_POST['payment_id'] ?? null;

        if (empty($reference)) {
            $this->setFlashMessage('error', 'Invalid payment reference.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/member/shares');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user   = $this->memberModel->getMemberById($userId);

        // ── IDOR PROTECTION ───────────────────────────────────────────────────
        // Verify the payment_id in the callback belongs to the logged-in member.
        if ($paymentId) {
            $paymentModel  = new \App\Models\PaymentModel();
            $paymentRecord = $paymentModel->getPaymentRecordByMember($paymentId);

            if (!$paymentRecord) {
                $this->setFlashMessage('error', 'Payment record not found.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/member/shares');
                exit;
            }

            if ($paymentRecord['membership_number'] !== $user['membership_number']) {
                \App\Helpers\SecurityLogger::idorAttempt(
                    'shares_payment_callback',
                    $user['membership_number'],
                    $paymentRecord['membership_number']
                );
                $this->setFlashMessage('error', 'Access denied: this payment does not belong to you.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/member/shares');
                exit;
            }
        }

        $paymentModel = new \App\Models\PaymentModel();

        // For demo mode, if status is success, proceed with payment
        if ($status === 'success') {
            $userId = $_SESSION['user_id'];
            $amount = $_SESSION['payment_amount'] ?? 100;
            $shares = $_SESSION['shares_count'] ?? 1;

            // Update payment status to success
            if ($paymentId) {
                $paymentModel->updatePaymentStatus($paymentId, 'success', $reference);
            }

            // Record the payment in shares table
            $paymentDate = date('Y-m-d');
            $notes = 'Share purchase via ' . ucfirst($gateway) . ' Gateway';
            $success = $this->sharesModel->addShares($userId, $shares, $amount, $paymentDate, $notes);

            if ($success) {
                $this->setFlashMessage('success', 'Share purchase successful! You have purchased ' . $shares . ' share(s).');
            } else {
                $this->setFlashMessage('error', 'Payment verified but failed to record shares. Please contact support.');
            }

            // Clear session payment data
            $this->clearSharesPaymentSession();

            header('Location: ' . \App\Helpers\Url::appUrl() . '/member/shares');
            exit;
        }

        // Handle payment failure
        if ($status === 'failed' || $status === 'cancelled') {
            $errorMessage = $_GET['message'] ?? $_POST['message'] ?? 'Payment was cancelled or failed';

            // Update payment status to failed
            if ($paymentId) {
                $paymentModel->updatePaymentStatus($paymentId, 'failed', $reference, $errorMessage);
            }

            $this->setFlashMessage('error', 'Payment failed: ' . $errorMessage);
            $this->clearSharesPaymentSession();

            header('Location: ' . \App\Helpers\Url::appUrl() . '/member/shares');
            exit;
        }

        // For real payment gateway, verify payment
        $paymentData = $paymentModel->verifyPayment($reference, $gateway);

        if ($paymentData['status'] && $paymentData['data']['status'] === 'success') {
            $userId = $_SESSION['user_id'];
            $amount = $paymentData['data']['amount'] / 100; // Convert from kobo to naira
            $shares = $_SESSION['shares_count'] ?? 1;

            // Update payment status to success
            if ($paymentId) {
                $paymentModel->updatePaymentStatus($paymentId, 'success', $reference);
            }

            // Record the payment in shares table
            $paymentDate = date('Y-m-d');
            $notes = 'Share purchase via ' . ucfirst($gateway);
            $success = $this->sharesModel->addShares($userId, $shares, $amount, $paymentDate, $notes);

            if ($success) {
                $this->setFlashMessage('success', 'Share purchase successful! You have purchased ' . $shares . ' share(s).');
            } else {
                $this->setFlashMessage('error', 'Payment verified but failed to record shares. Please contact support.');
            }
        } else {
            // Payment verification failed
            $errorMessage = $paymentData['message'] ?? 'Payment verification failed';

            // Update payment status to failed
            if ($paymentId) {
                $paymentModel->updatePaymentStatus($paymentId, 'failed', $reference, $errorMessage);
            }

            $this->setFlashMessage('error', 'Payment verification failed: ' . $errorMessage);
        }

        // Clear session payment data
        $this->clearSharesPaymentSession();

        header('Location: ' . \App\Helpers\Url::appUrl() . '/member/shares');
        exit;
    }

    private function clearSharesPaymentSession()
    {
        unset($_SESSION['payment_reference']);
        unset($_SESSION['payment_amount']);
        unset($_SESSION['payment_type']);
        unset($_SESSION['payment_gateway']);
        unset($_SESSION['payment_id']);
        unset($_SESSION['shares_count']);
    }

    public function thrift()
    {
        $userId = $_SESSION['user_id'];
        $user = $this->memberModel->getMemberById($userId);

        // Get thrift savings data
        $thriftData = $this->getThriftSavings($userId);
        $thriftHistory = $this->getThriftHistory($userId);

        $this->renderUserLayout('user/thrift', [
            'title' => 'Thrift Savings',
            'pageTitle' => 'Thrift Savings',
            'pageSubtitle' => 'Manage your monthly thrift savings contributions.',
            'user' => $user,
            'thriftData' => $thriftData,
            'thriftHistory' => $thriftHistory
        ]);
    }

    public function payThrift()
    {
        $userId = $_SESSION['user_id'];
        $user   = $this->memberModel->getMemberById($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Rate-limit payment initiation: 5 per 5 min per user
            [$max, $win] = \App\Helpers\RateLimiter::limitsFor('payment_init');
            \App\Helpers\RateLimiter::enforceForHtml(
                'payment_init', 'user_' . $userId, $max, $win,
                \App\Helpers\Url::appUrl() . '/member/thrift'
            );

            $amount  = (float)($_POST['amount']  ?? 1000);
            $notes   = trim($_POST['notes']       ?? '');
            $gateway = $_POST['payment_gateway']  ?? 'paystack';

            // Validate minimum amount
            if ($amount < 100) {
                $this->setFlashMessage('error', 'Minimum contribution amount is ₦100.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/member/thrift');
                exit;
            }

            $email = $user['email'];
            $membershipNumber = $user['membership_number'];

            // Initialize payment
            $paymentModel   = new \App\Models\PaymentModel();
            $callbackUrl    = \App\Helpers\Url::appUrl() . '/member/thrift/payment-callback';
            $idempotencyKey = trim($_POST['idempotency_key'] ?? '');
            if (empty($idempotencyKey)) {
                $hdrKey = $_SERVER['HTTP_X_IDEMPOTENCY_KEY'] ?? '';
                $idempotencyKey = \App\Models\PaymentIdempotency::isValidUuid($hdrKey) ? $hdrKey : '';
            }

            $paymentData = $paymentModel->initializePayment($email, (int)($amount * 100), $membershipNumber, $callbackUrl, $gateway, 'thrift_savings', $idempotencyKey);

            if ($paymentData['status']) {
                // Handle Manual Payment
                if ($gateway === 'manual') {
                    $this->setFlashMessage('success', 'Thrift contribution initiated! Please complete the transfer to the provided account. Your contribution is pending approval.');
                    header('Location: ' . \App\Helpers\Url::appUrl() . '/member/thrift');
                    exit;
                }

                // Store payment intent in session for verification
                $_SESSION['payment_reference'] = $paymentData['data']['reference'];
                $_SESSION['payment_amount'] = $amount;
                $_SESSION['payment_type'] = 'thrift_savings';
                $_SESSION['payment_gateway'] = $gateway;
                $_SESSION['payment_id'] = $paymentData['data']['payment_id'];
                $_SESSION['thrift_notes'] = $notes;

                // Redirect to payment gateway
                header('Location: ' . $paymentData['data']['authorization_url']);
                exit;
            } else {
                $this->setFlashMessage('error', 'Failed to initialize payment. Please try again.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/member/thrift');
                exit;
            }
        }

        // Show payment form
        $title = 'Pay Thrift Contribution';
        $pageTitle = 'Pay Thrift Contribution';
        $pageSubtitle = 'Complete your monthly thrift savings contribution.';

        ob_start();

        if (!defined('LAYOUT_INCLUDED')) {
            define('LAYOUT_INCLUDED', true);
        }

        require_once __DIR__ . '/../Views/user/pay-thrift.php';

        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/user.php';
    }

    public function thriftPaymentCallback()
    {
        // Handle both GET (from real payment gateway) and POST (from mock payment form)
        $reference = $_GET['reference'] ?? $_POST['reference'] ?? '';
        $status    = $_GET['status'] ?? $_POST['status'] ?? '';
        $gateway   = $_SESSION['payment_gateway'] ?? 'paystack';
        $paymentId = $_GET['payment_id'] ?? $_POST['payment_id'] ?? null;

        if (empty($reference)) {
            $this->setFlashMessage('error', 'Invalid payment reference.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/member/thrift');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user   = $this->memberModel->getMemberById($userId);

        // ── IDOR PROTECTION ───────────────────────────────────────────────────
        // Verify the payment_id in the callback belongs to the logged-in member.
        if ($paymentId) {
            $paymentModel  = new \App\Models\PaymentModel();
            $paymentRecord = $paymentModel->getPaymentRecordByMember($paymentId);

            if (!$paymentRecord) {
                $this->setFlashMessage('error', 'Payment record not found.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/member/thrift');
                exit;
            }

            if ($paymentRecord['membership_number'] !== $user['membership_number']) {
                \App\Helpers\SecurityLogger::idorAttempt(
                    'thrift_payment_callback',
                    $user['membership_number'],
                    $paymentRecord['membership_number']
                );
                $this->setFlashMessage('error', 'Access denied: this payment does not belong to you.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/member/thrift');
                exit;
            }
        }

        $paymentModel = new \App\Models\PaymentModel();

        // For demo mode, if status is success, proceed with payment
        if ($status === 'success') {
            $userId = $_SESSION['user_id'];
            $amount = $_SESSION['payment_amount'] ?? 100;
            $notes = $_SESSION['thrift_notes'] ?? '';

            // Update payment status to success
            if ($paymentId) {
                $paymentModel->updatePaymentStatus($paymentId, 'success', $reference);
            }

            // Record the payment in thrift_savings table
            $paymentDate = date('Y-m-d');
            $thriftModel = new \App\Models\ThriftSavingsModel();
            $success = $thriftModel->addPayment($userId, $amount, $paymentDate);

            if ($success) {
                $this->setFlashMessage('success', 'Thrift contribution successful! Your contribution of ₦' . number_format($amount, 2) . ' has been recorded.');
            } else {
                $this->setFlashMessage('error', 'Payment verified but failed to record contribution. Please contact support.');
            }

            // Clear session payment data
            $this->clearThriftPaymentSession();

            header('Location: ' . \App\Helpers\Url::appUrl() . '/member/thrift');
            exit;
        }

        // Handle payment failure
        if ($status === 'failed' || $status === 'cancelled') {
            $errorMessage = $_GET['message'] ?? $_POST['message'] ?? 'Payment was cancelled or failed';

            // Update payment status to failed
            if ($paymentId) {
                $paymentModel->updatePaymentStatus($paymentId, 'failed', $reference, $errorMessage);
            }

            $this->setFlashMessage('error', 'Payment failed: ' . $errorMessage);
            $this->clearThriftPaymentSession();

            header('Location: ' . \App\Helpers\Url::appUrl() . '/member/thrift');
            exit;
        }

        // For real payment gateway, verify payment
        $paymentData = $paymentModel->verifyPayment($reference, $gateway);

        if ($paymentData['status'] && $paymentData['data']['status'] === 'success') {
            $userId = $_SESSION['user_id'];
            $amount = $paymentData['data']['amount'] / 100; // Convert from kobo to naira
            $notes = $_SESSION['thrift_notes'] ?? '';

            // Update payment status to success
            if ($paymentId) {
                $paymentModel->updatePaymentStatus($paymentId, 'success', $reference);
            }

            // Record the payment in thrift_savings table
            $paymentDate = date('Y-m-d');
            $thriftModel = new \App\Models\ThriftSavingsModel();
            $success = $thriftModel->addPayment($userId, $amount, $paymentDate);

            if ($success) {
                $this->setFlashMessage('success', 'Thrift contribution successful! Your contribution of ₦' . number_format($amount, 2) . ' has been recorded.');
            } else {
                $this->setFlashMessage('error', 'Payment verified but failed to record contribution. Please contact support.');
            }
        } else {
            // Payment verification failed
            $errorMessage = $paymentData['message'] ?? 'Payment verification failed';

            // Update payment status to failed
            if ($paymentId) {
                $paymentModel->updatePaymentStatus($paymentId, 'failed', $reference, $errorMessage);
            }

            $this->setFlashMessage('error', 'Payment verification failed: ' . $errorMessage);
        }

        // Clear session payment data
        $this->clearThriftPaymentSession();

        header('Location: ' . \App\Helpers\Url::appUrl() . '/member/thrift');
        exit;
    }

    private function clearThriftPaymentSession()
    {
        unset($_SESSION['payment_reference']);
        unset($_SESSION['payment_amount']);
        unset($_SESSION['payment_type']);
        unset($_SESSION['payment_gateway']);
        unset($_SESSION['payment_id']);
        unset($_SESSION['thrift_notes']);
    }

    public function events()
    {
        $userId = $_SESSION['user_id'];
        $user = $this->memberModel->getMemberById($userId);

        // Get upcoming events
        $upcomingEvents = $this->eventModel->getUpcomingEvents(10);

        // Get past events
        $pastEvents = $this->eventModel->getPastEvents(10);

        // Set variables for the view
        $title = 'Events';
        $pageTitle = 'Events';
        $pageSubtitle = 'Stay updated with cooperative events and activities.';

        // Start output buffering
        ob_start();

        // Define constant to prevent 404 error in view
        if (!defined('LAYOUT_INCLUDED')) {
            define('LAYOUT_INCLUDED', true);
        }

        // Include the view
        require_once __DIR__ . '/../Views/user/events.php';

        // Get the content and include the layout
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/user.php';
    }

    public function calendar()
    {
        $userId = $_SESSION['user_id'];
        $user = $this->memberModel->getMemberById($userId);

        // Get current month and year
        $month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
        $year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');

        // Get all events for the current month
        $startDate = date('Y-m-01', mktime(0, 0, 0, $month, 1, $year));
        $endDate = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));

        // Debug: Log the date range
        error_log("Calendar Debug: Date range - $startDate to $endDate");

        $events = $this->eventModel->getEventsInDateRange($startDate, $endDate);

        // Debug: Log the number of events found
        error_log("Calendar Debug: Found " . count($events) . " events");

        // Organize events by date
        $eventsByDate = [];
        foreach ($events as $event) {
            $date = date('Y-m-d', strtotime($event['start_date']));
            if (!isset($eventsByDate[$date])) {
                $eventsByDate[$date] = [];
            }
            $eventsByDate[$date][] = $event;
        }

        // Organise events by date for calendar rendering
        $title = 'Calendar';
        $pageTitle = 'Calendar';
        $pageSubtitle = 'View events in calendar format.';

        // Start output buffering
        ob_start();

        // Define constant to prevent 404 error in view
        if (!defined('LAYOUT_INCLUDED')) {
            define('LAYOUT_INCLUDED', true);
        }

        // Include the view
        require_once __DIR__ . '/../Views/user/calendar.php';

        // Get the content and include the layout
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/user.php';
    }

    public function forum()
    {
        $userId = $_SESSION['user_id'];
        $user = $this->memberModel->getMemberById($userId);

        // Redirect to the main forum
        header('Location: ' . \App\Helpers\Url::appUrl() . '/forum');
        exit;
    }

    public function idCard()
    {
        $userId = $_SESSION['user_id'];
        $user = $this->memberModel->getMemberById($userId);

        if (!$user) {
            $this->setFlashMessage('error', 'Member not found.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/member/dashboard');
            exit;
        }

        // Render the membership card directly
        $this->render('members/membership_card', [
            'member' => $user
        ]);
    }

    public function changePassword()
    {
        $userId = $_SESSION['user_id'];
        $user = $this->memberModel->getMemberById($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validate current password
            if (!password_verify($currentPassword, $user['password'])) {
                $this->setFlashMessage('error', 'Current password is incorrect.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/member/change-password');
                exit;
            }

            // Validate new password
            if (strlen($newPassword) < 6) {
                $this->setFlashMessage('error', 'New password must be at least 6 characters long.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/member/change-password');
                exit;
            }

            if ($newPassword !== $confirmPassword) {
                $this->setFlashMessage('error', 'New passwords do not match.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/member/change-password');
                exit;
            }

            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $db = $this->memberModel->getConnection();
            $stmt = $db->prepare("UPDATE members SET password = ? WHERE id = ?");

            if ($stmt->execute([$hashedPassword, $userId])) {
                $this->setFlashMessage('success', 'Password updated successfully.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/member/dashboard');
                exit;
            } else {
                $this->setFlashMessage('error', 'Failed to update password.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/member/change-password');
                exit;
            }
        }

        $this->renderUserLayout('user/change-password', [
            'title' => 'Change Password',
            'pageTitle' => 'Change Password',
            'pageSubtitle' => 'Update your account password.',
            'user' => $user
        ]);
    }

    public function downloadStatement()
    {
        $userId = $_SESSION['user_id'];
        $user = $this->memberModel->getMemberById($userId);

        // Get dues history
        $duesHistory = $this->duesModel->getMemberDuesHistory($userId);

        // Generate PDF statement (simplified version - you can enhance this with a proper PDF library)
        $statement = $this->generateDuesStatement($user, $duesHistory);

        // Set headers for download
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="dues_statement_' . $user['membership_number'] . '_' . date('Y-m-d') . '.txt"');

        echo $statement;
        exit;
    }

    private function generateDuesStatement($user, $duesHistory)
    {
        $statement = "GLOBAL APEX FARMERS COOPERATIVE NIGERIA LIMITED\n";
        $statement .= "ANNUAL DUES STATEMENT\n";
        $statement .= "==========================================\n\n";

        $statement .= "Member Information:\n";
        $statement .= "Name: " . $user['firstname'] . " " . $user['surname'] . "\n";
        $statement .= "Membership Number: " . $user['membership_number'] . "\n";
        $statement .= "Email: " . $user['email'] . "\n";
        $statement .= "Status: " . ucfirst($user['annual_dues_status'] ?? 'unpaid') . "\n\n";

        $statement .= "Payment History:\n";
        $statement .= "================\n";

        if (!empty($duesHistory)) {
            foreach ($duesHistory as $payment) {
                $statement .= "Date: " . date('M d, Y', strtotime($payment['payment_date'])) . "\n";
                $statement .= "Amount: ₦" . number_format($payment['amount'], 2) . "\n";
                $statement .= "Status: " . ucfirst($payment['status'] ?? 'pending') . "\n";
                $statement .= "Reference: " . ($payment['reference'] ?? 'N/A') . "\n";
                $statement .= "Notes: " . ($payment['notes'] ?? 'N/A') . "\n";
                $statement .= "---\n";
            }
        } else {
            $statement .= "No payment history found.\n";
        }

        $statement .= "\nStatement Generated: " . date('M d, Y H:i:s') . "\n";
        $statement .= "This is an official statement from GAFCONL.\n";

        return $statement;
    }

    private function getUserStats($userId)
    {
        $db = $this->memberModel->getConnection();

        // Get total shares
        // Get total shares
        $totalShares = $this->sharesModel->getTotalSharesByMember($userId);

        // Get total thrift savings
        $thriftModel = new \App\Models\ThriftSavingsModel();
        $thriftSummary = $thriftModel->getSummary($userId);
        $totalSavings = $thriftSummary['total_savings'] ?? 0;

        // Get dues status
        $currentYear = date('Y');
        $hasPaidThisYear = $this->duesModel->hasPaidForYear($userId, $currentYear);
        $duesStatus = $hasPaidThisYear ? 'paid' : 'unpaid';

        $user = $this->memberModel->getMemberById($userId);

        return [
            'total_shares' => $totalShares,
            'total_savings' => $totalSavings,
            'dues_status' => $duesStatus,
            'dues_date' => $user['annual_dues_date'] ?? null
        ];
    }

    private function getRecentActivity($userId)
    {
        $db = $this->memberModel->getConnection();
        $activity = [];

        // Get recent dues payments
        $stmt = $db->prepare("SELECT 'dues' as type, amount, payment_date as date FROM annual_dues WHERE member_id = ? ORDER BY payment_date DESC LIMIT 3");
        $stmt->execute([$userId]);
        $dues = $stmt->fetchAll();

        foreach ($dues as $due) {
            $activity[] = [
                'type' => 'dues',
                'description' => 'Annual dues payment of ₦' . number_format($due['amount'], 2),
                'date' => $due['date'],
                'icon' => 'ri-calendar-check-line',
                'icon_bg' => 'bg-green-100',
                'icon_color' => 'text-green-600'
            ];
        }

        // Get recent share purchases
        $stmt = $db->prepare("SELECT 'shares' as type, number_of_shares as shares, amount, purchase_date as date FROM shares WHERE member_id = ? ORDER BY purchase_date DESC LIMIT 3");
        $stmt->execute([$userId]);
        $shares = $stmt->fetchAll();

        foreach ($shares as $share) {
            $activity[] = [
                'type' => 'shares',
                'description' => 'Purchased ' . $share['shares'] . ' shares for ₦' . number_format($share['amount'], 2),
                'date' => $share['date'],
                'icon' => 'ri-pie-chart-line',
                'icon_bg' => 'bg-blue-100',
                'icon_color' => 'text-blue-600'
            ];
        }

        // Sort by date
        usort($activity, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return array_slice($activity, 0, 5);
    }

    private function getThriftSavings($userId)
    {
        $thriftModel = new \App\Models\ThriftSavingsModel();
        $result = $thriftModel->getSummary($userId);

        $lastPaymentDate = $result['last_payment_date'];
        $nextDueDate = $lastPaymentDate ?
            date('Y-m-d', strtotime('+1 month', strtotime($lastPaymentDate))) :
            date('Y-m-d', strtotime('first day of next month'));

        return [
            'total_savings' => $result['total_savings'] ?? 0,
            'last_payment_date' => $lastPaymentDate,
            'next_due_date' => $nextDueDate,
            'payment_count' => $result['payment_count'] ?? 0
        ];
    }

    private function getThriftHistory($userId)
    {
        $thriftModel = new \App\Models\ThriftSavingsModel();
        return $thriftModel->getHistory($userId);
    }

}
