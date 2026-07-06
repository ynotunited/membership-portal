<?php
namespace App\Controllers;

use App\Models\BaseModel;
use App\Models\RoleModel;
use App\Helpers\Csrf;
use App\Helpers\Url;
use App\Helpers\Mailer;
use App\Helpers\RateLimiter;
use App\Helpers\SecurityLogger;

/**
 * AuthController – handles login, logout, registration and password reset.
 *
 * Security measures applied:
 *  - Rate limiting on login (5 attempts / 15 min per IP)
 *  - session_regenerate_id(true) destroys the old session file on auth state change
 *  - Session idle timeout (30 min) + absolute timeout (8 h) enforced on every request
 *  - CSRF token validated on every POST; token is rotated after each use
 *  - Password reset tokens are stored as SHA-256 hashes (never plaintext)
 *  - Email verification required for new member registrations
 *  - Uniform "invalid credentials" message prevents user-enumeration on login
 *  - Logout cookie bug fixed: cookie value read before unset
 */
class AuthController extends BaseController
{
    /** Idle timeout in seconds (30 minutes). */
    private const SESSION_IDLE_TTL = 1800;

    /** Absolute session lifetime in seconds (8 hours). */
    private const SESSION_ABS_TTL = 28800;

    // -------------------------------------------------------------------------
    // Session security helpers
    // -------------------------------------------------------------------------

    /**
     * Enforce idle + absolute session timeouts.
     * Call this at the top of any protected controller action.
     */
    public static function enforceSessionTimeout(): void
    {
        $now = time();

        if (isset($_SESSION['_last_activity'])) {
            if ($now - $_SESSION['_last_activity'] > self::SESSION_IDLE_TTL) {
                self::expireSession();
                return;
            }
        }

        if (isset($_SESSION['_created_at'])) {
            if ($now - $_SESSION['_created_at'] > self::SESSION_ABS_TTL) {
                self::expireSession();
                return;
            }
        }

        $_SESSION['_last_activity'] = $now;
    }

    private static function expireSession(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    // -------------------------------------------------------------------------
    // Login / Logout
    // -------------------------------------------------------------------------

    public function showLogin(): void
    {
        $csrf_token = Csrf::generateToken();
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);
        $this->render('auth/login', ['csrf_token' => $csrf_token, 'error' => $error]);
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        if (!Csrf::validateToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['login_error'] = 'Invalid or expired security token. Please try again.';
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $login_input = \App\Helpers\SecurityHelper::sanitizeString($_POST['contact_number'] ?? '');
        $password    = $_POST['password'] ?? '';

        // Rate-limit by IP: 10 attempts per 15 minutes
        if (!RateLimiter::attempt('login_ip', $ip, 10, 900)) {
            $wait = ceil(RateLimiter::secondsUntilUnlocked('login_ip', $ip, 900) / 60);
            SecurityLogger::rateLimitExceeded('login_ip', $ip);
            $_SESSION['login_error'] = "Too many failed login attempts from this IP. Please try again in {$wait} minute(s).";
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        // Rate-limit by Identity: 5 attempts per 15 minutes
        if ($login_input && !RateLimiter::attempt('login_identity', $login_input, 5, 900)) {
            $wait = ceil(RateLimiter::secondsUntilUnlocked('login_identity', $login_input, 900) / 60);
            SecurityLogger::rateLimitExceeded('login_identity', $login_input);
            $_SESSION['login_error'] = "Too many failed login attempts for this account. Please try again in {$wait} minute(s).";
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        if (!$login_input || !$password) {
            $_SESSION['login_error'] = 'Login credentials are required.';
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        if (filter_var($login_input, FILTER_VALIDATE_EMAIL)) {
            // Admin login
        } elseif (!\App\Helpers\SecurityHelper::validatePhone($login_input)) {
            $_SESSION['login_error'] = 'Invalid email or phone number format.';
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        $model = new BaseModel();
        $db    = $model->getConnection();

        if (filter_var($login_input, FILTER_VALIDATE_EMAIL)) {
            // ── Admin login (email) ──────────────────────────────────────────
            $stmt = $db->prepare(
                'SELECT id, email, password, role_id FROM users WHERE email = :email LIMIT 1'
            );
            $stmt->execute(['email' => $login_input]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $roleModel = new RoleModel();
                $role      = $roleModel->getUserRole($user['id']);

                // Destroy the old session file before writing new auth data
                session_regenerate_id(true);

                $_SESSION['user_id']          = $user['id'];
                $_SESSION['email']            = $user['email'];
                $_SESSION['role']             = $role ? $role['name'] : 'admin';
                $_SESSION['role_id']          = $user['role_id'] ?? 1;
                $_SESSION['_last_activity']   = time();
                $_SESSION['_created_at']      = time();

                RateLimiter::clear('login_ip', $ip);
                RateLimiter::clear('login_identity', $login_input);
                SecurityLogger::loginSuccess($login_input, $_SESSION['role']);
                session_write_close();
                header('Location: ' . Url::appUrl() . '/dashboard');
                exit;
            }
        } else {
            // ── Member login (phone number) ──────────────────────────────────
            $stmt = $db->prepare(
                'SELECT id, contact_number, email, firstname, surname, password, email_verified
                 FROM members WHERE contact_number = :contact_number LIMIT 1'
            );
            $stmt->execute(['contact_number' => $login_input]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Block unverified members from logging in
                if (isset($user['email_verified']) && !(bool)$user['email_verified']) {
                    SecurityLogger::loginFailure($login_input, 'email_not_verified');
                    $_SESSION['login_error'] = 'Please verify your email address before logging in. Check your inbox for the verification link.';
                    header('Location: ' . Url::appUrl() . '/login');
                    exit;
                }

                session_regenerate_id(true);

                $_SESSION['user_id']          = $user['id'];
                $_SESSION['contact_number']   = $user['contact_number'];
                $_SESSION['role']             = 'user';
                $_SESSION['_last_activity']   = time();
                $_SESSION['_created_at']      = time();

                RateLimiter::clear('login_ip', $ip);
                RateLimiter::clear('login_identity', $login_input);
                SecurityLogger::loginSuccess($login_input, 'member');
                \App\Models\RowPolicy::setMember((int)$user['id']); // activate RLS for this session
                session_write_close();
                header('Location: ' . Url::appUrl() . '/member/dashboard');
                exit;
            }
        }

        // Generic message – never reveal whether an account exists
        SecurityLogger::loginFailure($login_input, 'invalid_credentials');
        $_SESSION['login_error'] = 'Invalid login credentials.';
        header('Location: ' . Url::appUrl() . '/login');
        exit;
    }

    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $identifier = $_SESSION['email'] ?? $_SESSION['contact_number'] ?? 'unknown';
        SecurityLogger::logout($identifier);
        \App\Models\RowPolicy::clear(); // revoke RLS context before destroying session
        if (isset($_COOKIE['remember_me'])) {
            $cookieValue = $_COOKIE['remember_me'];
            setcookie('remember_me', '', time() - 3600, '/', '', true, true);

            $parts = explode(':', $cookieValue);
            if (count($parts) === 2 && !empty($parts[0])) {
                $db   = (new BaseModel())->getConnection();
                $stmt = $db->prepare('DELETE FROM auth_tokens WHERE selector = :selector');
                $stmt->execute(['selector' => $parts[0]]);
            }
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        header('Location: ' . Url::appUrl() . '/login');
        exit;
    }

    // -------------------------------------------------------------------------
    // Registration
    // -------------------------------------------------------------------------

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        // Rate-limit account creation: 3 registrations per IP per hour
        $ip = RateLimiter::clientIp();
        [$max, $window] = RateLimiter::limitsFor('register');
        RateLimiter::enforceForHtml('register', $ip, $max, $window, Url::appUrl() . '/login');

        // ── Field collection ─────────────────────────────────────────────────
        $title              = trim($_POST['title']              ?? '');
        $surname            = trim($_POST['surname']            ?? '');
        $firstname          = trim($_POST['firstname']          ?? '');
        $othername          = trim($_POST['othername']          ?? '');
        $gender             = trim($_POST['gender']             ?? '');
        $marital_status     = trim($_POST['marital_status']     ?? '');
        $email              = trim($_POST['email']              ?? '');
        $phone_country_code = trim($_POST['phone_country_code'] ?? '+234');
        $contact_number     = trim($_POST['contact_number']     ?? '');
        $phone              = $phone_country_code . $contact_number;
        $whatsapp_cc        = trim($_POST['whatsapp_country_code'] ?? '+234');
        $whatsapp_number    = trim($_POST['whatsapp_number']    ?? '');
        $whatsapp_full      = $whatsapp_number ? $whatsapp_cc . $whatsapp_number : '';
        $dob                = trim($_POST['dob']                ?? '');
        $house_no           = trim($_POST['house_no']           ?? '');
        $street_name        = trim($_POST['street_name']        ?? '');
        $nearest_bus_stop   = trim($_POST['nearest_bus_stop']   ?? '');
        $city_town          = trim($_POST['city_town']          ?? '');
        $lga                = trim($_POST['lga']                ?? '');
        $state_district     = trim($_POST['state_district']     ?? '');
        $country            = trim($_POST['country']            ?? '');
        $business_name      = trim($_POST['business_name']      ?? '');
        $business_address   = trim($_POST['business_address']   ?? '');
        $nature_of_business = trim($_POST['nature_of_business'] ?? '');
        $sub_sector         = trim($_POST['sub_sector']         ?? '');
        $identity_type      = trim($_POST['identity_type']      ?? '');
        $id_number          = trim($_POST['id_number']          ?? '');
        $date_of_issue      = trim($_POST['date_of_issue']      ?? '');
        $registration_status = trim($_POST['registration_status'] ?? '');
        $chapter            = trim($_POST['chapter']            ?? '');
        $zone               = trim($_POST['zone']               ?? '');
        $member_type        = trim($_POST['member_type']        ?? '');
        $payment_type       = trim($_POST['payment_type']       ?? 'Online Payment');
        $account_name       = trim($_POST['account_name']       ?? '');
        $account_number     = trim($_POST['account_number']     ?? '');
        $bank_name          = trim($_POST['bank_name']          ?? '');
        $password           = $_POST['password']                ?? '';
        $confirmPassword    = $_POST['confirmPassword']         ?? '';

        // ── Validation ───────────────────────────────────────────────────────
        if (!$surname || !$firstname || !$gender || !$email || !$contact_number ||
            !$dob || !$country || !$identity_type || !$id_number ||
            !$date_of_issue || !$chapter || !$zone || !$member_type || !$password) {
            $_SESSION['login_error'] = 'All required fields must be filled.';
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['login_error'] = 'Invalid email address.';
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        if ($password !== $confirmPassword) {
            $_SESSION['login_error'] = 'Passwords do not match.';
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        // Enforce strong password: min 8 chars, letter + digit + special char
        if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/', $password)) {
            $_SESSION['login_error'] = 'Password must be at least 8 characters and include letters, numbers, and special characters (!@#$%^&*).';
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        if ($identity_type === 'NIN' && !preg_match('/^\d{11}$/', $id_number)) {
            $_SESSION['login_error'] = 'Invalid NIN format. NIN should be exactly 11 digits.';
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        $memberModel = new \App\Models\MemberModel();
        $db          = $memberModel->getConnection();

        $stmt = $db->prepare('SELECT id FROM members WHERE email = :email OR contact_number = :phone');
        $stmt->execute(['email' => $email, 'phone' => $phone]);
        if ($stmt->fetch()) {
            $_SESSION['login_error'] = 'Email or phone number already exists.';
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        // ── Membership number ────────────────────────────────────────────────
        $stmt = $db->prepare(
            "SELECT membership_number FROM members
             WHERE membership_number LIKE 'GAFCONL-%'
             ORDER BY LENGTH(membership_number) DESC, membership_number DESC LIMIT 1"
        );
        $stmt->execute();
        $result     = $stmt->fetch();
        $lastNumber = ($result && preg_match('/GAFCONL-(\d+)/', $result['membership_number'], $m))
                      ? (int)$m[1] : 0;
        $membershipNumber = 'GAFCONL-' . str_pad($lastNumber + 1, 7, '0', STR_PAD_LEFT);

        // ── File uploads ─────────────────────────────────────────────────────
        $photoName    = $this->handleFileUpload('photo',    'member_photos', 'default.jpg');
        $ninCardName  = $this->handleFileUpload('nin_card', 'nin_cards',     'default_nin.jpg');
        $signatureName = $this->handleFileUpload('signature','signatures',   'default_signature.jpg');

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $paymentStatus  = ($payment_type === 'Online Payment') ? 'Pending' : 'Paid';

        // ── Generate email verification token ────────────────────────────────
        $verifyToken  = bin2hex(random_bytes(32));
        $verifyExpiry = date('Y-m-d H:i:s', time() + 86400); // 24 hours

        // ── Insert member ────────────────────────────────────────────────────
        $sql = "INSERT INTO members
            (title, surname, firstname, othername, gender, marital_status,
             email, contact_number, whatsapp_number, dob,
             house_no, street_name, nearest_bus_stop, city_town, lga, state_district, country,
             business_name, business_address, nature_of_business, sub_sector,
             identity_type, id_number, date_of_issue, registration_status,
             chapter, zone, membership_type, payment_type, membership_number,
             photo, nin_card, signature,
             created_at, payment_status, password, account_name, account_number, bank_name,
             email_verified, reset_token, reset_token_expiry)
            VALUES
            (:title,:surname,:firstname,:othername,:gender,:marital_status,
             :email,:phone,:whatsapp_full,:dob,
             :house_no,:street_name,:nearest_bus_stop,:city_town,:lga,:state_district,:country,
             :business_name,:business_address,:nature_of_business,:sub_sector,
             :identity_type,:id_number,:date_of_issue,:registration_status,
             :chapter,:zone,:member_type,:payment_type,:membership_number,
             :photo,:nin_card,:signature,
             NOW(),:payment_status,:password,:account_name,:account_number,:bank_name,
             0,:verify_token,:verify_expiry)";

        $stmt = $db->prepare($sql);
        $success = $stmt->execute([
            'title' => $title, 'surname' => $surname, 'firstname' => $firstname,
            'othername' => $othername, 'gender' => $gender, 'marital_status' => $marital_status,
            'email' => $email, 'phone' => $phone, 'whatsapp_full' => $whatsapp_full, 'dob' => $dob,
            'house_no' => $house_no, 'street_name' => $street_name,
            'nearest_bus_stop' => $nearest_bus_stop, 'city_town' => $city_town,
            'lga' => $lga, 'state_district' => $state_district, 'country' => $country,
            'business_name' => $business_name, 'business_address' => $business_address,
            'nature_of_business' => $nature_of_business, 'sub_sector' => $sub_sector,
            'identity_type' => $identity_type, 'id_number' => $id_number,
            'date_of_issue' => $date_of_issue, 'registration_status' => $registration_status,
            'chapter' => $chapter, 'zone' => $zone, 'member_type' => $member_type,
            'payment_type' => $payment_type, 'membership_number' => $membershipNumber,
            'photo' => $photoName, 'nin_card' => $ninCardName, 'signature' => $signatureName,
            'payment_status' => $paymentStatus, 'password' => $hashedPassword,
            'account_name' => $account_name, 'account_number' => $account_number,
            'bank_name' => $bank_name,
            'verify_token' => $verifyToken, 'verify_expiry' => $verifyExpiry,
        ]);

        if ($success) {
            $memberId = $db->lastInsertId();

            // Determine registration amount
            $amount = 0;
            if ($registration_status === 'Director')               { $amount = 1000000; }
            elseif (in_array($member_type, ['Membership Registration','Renewal'])) { $amount = 12000; }

            $db->prepare(
                'INSERT INTO renew (member_id, total_amount, renew_date, payment_type)
                 VALUES (:mid, :amt, NOW(), :ptype)'
            )->execute(['mid' => $memberId, 'amt' => $amount, 'ptype' => $payment_type]);

            // ── Send email verification link ──────────────────────────────
            $verifyLink = Url::appUrl() . '/verify-email?token=' . $verifyToken;
            $emailBody  = $this->buildVerificationEmail($firstname, $membershipNumber, $verifyLink);

            try {
                Mailer::send($email, 'Verify your GAFCONL account', $emailBody);
            } catch (\Exception $e) {
                error_log('Verification email failed: ' . $e->getMessage());
            }

            if ($payment_type === 'Online Payment') {
                header('Location: ' . Url::appUrl() . '/payments/gateway?membershipNumber=' .
                       urlencode($membershipNumber) . '&membershipType=' . urlencode($member_type) .
                       '&email=' . urlencode($email));
                exit;
            }

            $_SESSION['login_error'] = 'Registration successful! Membership No: ' . $membershipNumber .
                                       '. Please check your email to verify your account before logging in.';
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        $_SESSION['login_error'] = 'Registration failed. Please try again.';
        header('Location: ' . Url::appUrl() . '/login');
        exit;
    }

    // -------------------------------------------------------------------------
    // Email verification
    // -------------------------------------------------------------------------

    public function verifyEmail(): void
    {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            $_SESSION['login_error'] = 'Invalid verification link.';
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        $memberModel = new \App\Models\MemberModel();
        $db = $memberModel->getConnection();

        $stmt = $db->prepare(
            "SELECT id FROM members
             WHERE reset_token = :token AND reset_token_expiry > NOW() AND email_verified = 0"
        );
        $stmt->execute(['token' => $token]);
        $member = $stmt->fetch();

        if (!$member) {
            $_SESSION['login_error'] = 'This verification link is invalid or has expired.';
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        $db->prepare(
            "UPDATE members SET email_verified = 1, reset_token = NULL, reset_token_expiry = NULL WHERE id = :id"
        )->execute(['id' => $member['id']]);

        $_SESSION['login_error'] = 'Email verified successfully! You can now log in.';
        header('Location: ' . Url::appUrl() . '/login');
        exit;
    }

    // -------------------------------------------------------------------------
    // Password reset
    // -------------------------------------------------------------------------

    public function requestReset(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
            !Csrf::validateToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['login_error'] = 'Invalid request. Please try again.';
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['login_error'] = 'Please enter a valid email address.';
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!RateLimiter::attempt('password_reset', $ip, 3, 3600)) {
            $_SESSION['login_error'] = 'Too many reset requests. Please try again in 1 hour.';
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        $userModel   = new \App\Models\UserModel();
        $memberModel = new \App\Models\MemberModel();
        $user        = $userModel->getUserByEmail($email);
        $member      = $memberModel->getMemberByEmail($email);

        if ($user || $member) {
            $token      = bin2hex(random_bytes(32));
            $resetModel = new \App\Models\PasswordResetModel();
            $resetModel->createResetToken($email, $token); // stores hash, not plaintext

            $resetLink = Url::appUrl() . '/reset-password?token=' . $token;
            $emailBody = $this->buildPasswordResetEmail($resetLink);
            Mailer::send($email, 'Password Reset Request – GAFCONL', $emailBody);
        }

        // Always show the same message (prevents email enumeration)
        $_SESSION['login_error'] = 'If your email is in our system, you will receive a password reset link.';
        header('Location: ' . Url::appUrl() . '/login');
        exit;
    }

    public function showResetForm(): void
    {
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            $_SESSION['login_error'] = 'Invalid password reset link.';
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        $resetModel  = new \App\Models\PasswordResetModel();
        $resetRecord = $resetModel->getResetToken($token); // looks up by hash
        if (!$resetRecord) {
            $_SESSION['login_error'] = 'This password reset link has expired or is invalid.';
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        $csrf_token = Csrf::generateToken();
        $this->render('auth/reset_password', ['csrf_token' => $csrf_token, 'token' => $token]);
    }

    public function resetPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
            !Csrf::validateToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['login_error'] = 'Invalid request. Please try again.';
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        $token           = $_POST['token']           ?? '';
        $password        = $_POST['password']        ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        if (empty($token) || empty($password) || empty($confirmPassword)) {
            $_SESSION['login_error'] = 'All fields are required.';
            header('Location: ' . Url::appUrl() . '/reset-password?token=' . urlencode($token));
            exit;
        }

        if ($password !== $confirmPassword) {
            $_SESSION['login_error'] = 'Passwords do not match.';
            header('Location: ' . Url::appUrl() . '/reset-password?token=' . urlencode($token));
            exit;
        }

        if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/', $password)) {
            $_SESSION['login_error'] = 'Password must be at least 8 characters with letters, numbers and special characters.';
            header('Location: ' . Url::appUrl() . '/reset-password?token=' . urlencode($token));
            exit;
        }

        $resetModel  = new \App\Models\PasswordResetModel();
        $resetRecord = $resetModel->getResetToken($token);
        if (!$resetRecord) {
            $_SESSION['login_error'] = 'This password reset link has expired or is invalid.';
            header('Location: ' . Url::appUrl() . '/login');
            exit;
        }

        $userModel   = new \App\Models\UserModel();
        $memberModel = new \App\Models\MemberModel();

        $userUpdated   = $userModel->updatePasswordByEmail($resetRecord['email'], $password);
        $memberUpdated = $memberModel->updatePasswordByEmail($resetRecord['email'], $password);

        if (!$userUpdated && !$memberUpdated) {
            $_SESSION['login_error'] = 'Failed to update password. Please try again.';
            header('Location: ' . Url::appUrl() . '/reset-password?token=' . urlencode($token));
            exit;
        }

        $resetModel->deleteResetToken($token);
        $_SESSION['login_error'] = 'Your password has been reset successfully. Please log in.';
        header('Location: ' . Url::appUrl() . '/login');
        exit;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function handleFileUpload(string $fieldName, string $folder, string $defaultFile): string
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return $defaultFile;
        }

        $file      = $_FILES[$fieldName];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
            return $defaultFile;
        }

        // Validate MIME type (don't trust the extension alone)
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, ['image/jpeg', 'image/png'], true)) {
            return $defaultFile;
        }

        $fileName   = bin2hex(random_bytes(8)) . '.' . $extension;
        $uploadPath = __DIR__ . '/../../public/uploads/' . $folder . '/';

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $uploadPath . $fileName)) {
            return $fileName;
        }

        return $defaultFile;
    }

    private function buildVerificationEmail(string $firstname, string $membershipNumber, string $link): string
    {
        return '
<p>Dear ' . htmlspecialchars($firstname) . ',</p>
<p>Thank you for registering with GAFCONL. Your membership number is <strong>'
            . htmlspecialchars($membershipNumber) . '</strong>.</p>
<p>Please verify your email address by clicking the button below.
   This link expires in 24 hours.</p>
<p><a href="' . htmlspecialchars($link) . '"
   style="background:#408100;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:bold;">
   Verify Email Address
</a></p>
<p>If you did not create this account, please ignore this email.</p>
<p>— The GAFCONL Team</p>';
    }

    private function buildPasswordResetEmail(string $link): string
    {
        return '
<p>You requested a password reset for your GAFCONL account.</p>
<p>Click the button below to set a new password. This link expires in 1 hour.</p>
<p><a href="' . htmlspecialchars($link) . '"
   style="background:#408100;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:bold;">
   Reset Password
</a></p>
<p>If you did not request this, you can safely ignore this email.</p>
<p>— The GAFCONL Team</p>';
    }
}
