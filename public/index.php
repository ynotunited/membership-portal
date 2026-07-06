<?php
// =============================================================================
// Bootstrap
// =============================================================================

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Controllers\AuthController;
use App\Helpers\SecurityLogger;
use App\Middleware\MonitoringMiddleware;

// ── 1. Load environment variables ────────────────────────────────────────────
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Make getenv() work on all platforms (some hosts only populate $_ENV)
foreach ($_ENV as $key => $value) {
    putenv("$key=$value");
}

// ── 2. PHP error display: NEVER show errors to the browser in production ──────
$isDebug = filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN);

ini_set('display_errors',         $isDebug ? '1' : '0');
ini_set('display_startup_errors', $isDebug ? '1' : '0');
error_reporting(E_ALL);

// Always log errors to file regardless of environment
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// ── 3. Session hardening ──────────────────────────────────────────────────────
if (session_status() !== PHP_SESSION_ACTIVE) {
    $secureCookie = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params([
        'lifetime' => 0,           // Session cookie – expires when browser closes
        'path'     => '/',
        'domain'   => '',          // Current domain only
        'secure'   => $secureCookie,
        'httponly' => true,        // JS cannot access the session cookie
        'samesite' => 'Lax',       // CSRF mitigation for cross-site requests
    ]);

    // Idle timeout enforced in AuthController::enforceSessionTimeout()
    ini_set('session.gc_maxlifetime', '1800'); // 30 min server-side GC
    ini_set('session.use_strict_mode', '1');   // Reject unrecognised session IDs
    ini_set('session.use_only_cookies', '1');  // No session ID in URL
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    session_start();
}

// ── 4. Start request monitoring ───────────────────────────────────────────────
$monitoring = new MonitoringMiddleware();
$monitoring->handle();

// ── 5. Restore RLS context from session (must happen before any model query) ──
// For member sessions, set the MySQL @app_member_id variable so all rls_* views
// automatically filter to this member's rows only.
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
    \App\Models\RowPolicy::setMember((int)$_SESSION['user_id']);
}

// ── 6. Remember-me token check ───────────────────────────────────────────────
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    $parts = explode(':', $_COOKIE['remember_me'], 2);
    if (count($parts) === 2 && !empty($parts[0]) && !empty($parts[1])) {
        [$selector, $token] = $parts;
        try {
            $model = new \App\Models\BaseModel();
            $db    = $model->getConnection();

            $stmt = $db->prepare(
                'SELECT * FROM auth_tokens WHERE selector = :selector AND expires >= NOW()'
            );
            $stmt->execute(['selector' => $selector]);
            $authToken = $stmt->fetch();

            if ($authToken && hash_equals(
                $authToken['hashed_token'],
                hash('sha256', base64_decode($token))
            )) {
                $stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
                $stmt->execute(['id' => $authToken['user_id']]);
                $user = $stmt->fetch();

                if ($user) {
                    session_regenerate_id(true);
                    $_SESSION['user_id']        = $user['id'];
                    $_SESSION['email']          = $user['email'];
                    $_SESSION['role']           = $user['role'] ?? 'user';
                    $_SESSION['_last_activity'] = time();
                    $_SESSION['_created_at']    = time();
                }
            }
        } catch (\Throwable $e) {
            // Remember-me failure is non-fatal; clear the bad cookie
            setcookie('remember_me', '', time() - 3600, '/', '', true, true);
            error_log('Remember-me check failed: ' . $e->getMessage());
        }
    }
}

// ── 6. Detect and log unusual traffic patterns ────────────────────────────────
(function () {
    $ua  = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $uri = $_SERVER['REQUEST_URI']     ?? '';

    // Common scanner / exploitation tool signatures
    $suspiciousUAPatterns = [
        'sqlmap', 'nikto', 'nmap', 'masscan', 'nessus',
        'acunetix', 'burpsuite', 'metasploit', 'zgrab', 'nuclei',
    ];
    foreach ($suspiciousUAPatterns as $sig) {
        if (stripos($ua, $sig) !== false) {
            SecurityLogger::unusualTraffic('suspicious_user_agent', ['ua' => substr($ua, 0, 200)]);
            break;
        }
    }

    // Common path-traversal and injection probes in the URI
    $suspiciousUriPatterns = [
        '../', '..\\',              // path traversal
        '/etc/passwd', '/etc/shadow',
        'union+select', 'union%20select', 'union select',
        '<script', 'javascript:',
        'eval(', 'base64_decode(',
        '/wp-admin', '/phpmyadmin',  // WordPress/phpMyAdmin scans
        '.git/', '.env',
    ];
    foreach ($suspiciousUriPatterns as $sig) {
        if (stripos($uri, $sig) !== false) {
            SecurityLogger::unusualTraffic('suspicious_uri_pattern', ['uri' => substr($uri, 0, 300)]);
            break;
        }
    }
})();

// ── 7. Route ──────────────────────────────────────────────────────────────────
// Apply API Gateway, WAF, and Behavior detection middleware
\App\Helpers\SecurityMiddleware::handle();

// Set error handler after security middleware
\App\Helpers\ErrorHandler::register();

// Load Routes
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Strip the base path so routes work under /gafconl-app/public or at root
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$basePaths  = array_filter([
    rtrim(dirname($scriptName), '/'),
    '/gafconl-app/public',
    '/gafconl-app',
]);

foreach ($basePaths as $basePath) {
    if ($basePath !== '' && strpos($uri, $basePath) === 0) {
        $uri = substr($uri, strlen($basePath));
        break;
    }
}
if ($uri === '' || $uri === false) {
    $uri = '/';
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($uri) {
    case '/':
        $auth = new AuthController();
        $auth->showLogin();
        break;
    case '/login':
        $auth = new AuthController();
        if ($method === 'POST') { $auth->login(); } else { $auth->showLogin(); }
        break;
    case '/register':
        $auth = new AuthController();
        if ($method === 'POST') { $auth->register(); } else { $auth->showLogin(); }
        break;
    case '/logout':
        (new AuthController())->logout();
        break;
    case '/request-reset':
        $auth = new AuthController();
        if ($method === 'POST') { $auth->requestReset(); } else {
            header('Location: ' . \App\Helpers\Url::appUrl() . '/login'); exit;
        }
        break;
    case '/verify-email':
        (new AuthController())->verifyEmail();
        break;
    case '/reset-password':
        $auth = new AuthController();
        if ($method === 'POST') { $auth->resetPassword(); } else { $auth->showResetForm(); }
        break;

    // Dashboard
    case '/dashboard':
        (new \App\Controllers\DashboardController())->index();
        break;
    case '/dashboard/activity-feed':
        (new \App\Controllers\DashboardController())->getActivityFeed();
        break;

    // Members
    case '/members':
        (new \App\Controllers\MemberController())->index();
        break;
    case '/members/add':
        (new \App\Controllers\MemberController())->add();
        break;
    case '/members/edit':
        (new \App\Controllers\MemberController())->edit();
        break;
    case '/members/delete':
        (new \App\Controllers\MemberController())->delete();
        break;
    case '/members/profile':
        (new \App\Controllers\MemberController())->profile();
        break;
    case '/members/membership-card':
        (new \App\Controllers\MemberController())->membershipCard();
        break;
    case '/members/export':
        (new \App\Controllers\MemberController())->export();
        break;
    case '/members/bulk':
        (new \App\Controllers\MemberController())->bulk();
        break;

    // Membership types
    case '/membership-types':
        (new \App\Controllers\MembershipTypeController())->index();
        break;
    case '/membership-types/add':
        (new \App\Controllers\MembershipTypeController())->add();
        break;
    case '/membership-types/edit':
        (new \App\Controllers\MembershipTypeController())->edit();
        break;
    case '/membership-types/delete':
        (new \App\Controllers\MembershipTypeController())->delete();
        break;

    // Search
    case '/search':
        (new \App\Controllers\SearchController())->search();
        break;

    // Dues
    case '/dues':
        (new \App\Controllers\DuesController())->index();
        break;
    case '/dues/add':
        (new \App\Controllers\DuesController())->add();
        break;
    case '/dues/edit':
        (new \App\Controllers\DuesController())->edit();
        break;
    case '/dues/delete':
        (new \App\Controllers\DuesController())->delete();
        break;
    case '/dues/export':
        (new \App\Controllers\DuesController())->export();
        break;

    // Shares
    case '/shares':
        (new \App\Controllers\SharesController())->index();
        break;
    case '/shares/add':
        (new \App\Controllers\SharesController())->add();
        break;
    case '/shares/edit':
        (new \App\Controllers\SharesController())->edit();
        break;
    case '/shares/delete':
        (new \App\Controllers\SharesController())->delete();
        break;
    case '/shares/export':
        (new \App\Controllers\SharesController())->export();
        break;

    // Rice Project
    case '/rice-project':
        (new \App\Controllers\RiceProjectController())->index();
        break;
    case '/rice-project/submit':
        (new \App\Controllers\RiceProjectController())->submit();
        break;
    case '/admin/rice-project':
        (new \App\Controllers\RiceProjectController())->adminIndex();
        break;
    case '/admin/rice-project/approve':
        (new \App\Controllers\RiceProjectController())->approve();
        break;
    case '/admin/rice-project/reject':
        (new \App\Controllers\RiceProjectController())->reject();
        break;

    // Admin Investments
    case '/admin/investments':
        (new \App\Controllers\AdminInvestmentsController())->index();
        break;
    case '/admin/investments/add':
        (new \App\Controllers\AdminInvestmentsController())->add();
        break;
    case '/admin/investments/edit':
        (new \App\Controllers\AdminInvestmentsController())->edit();
        break;

    // Admin Loans
    case '/admin/loans':
        (new \App\Controllers\AdminLoansController())->index();
        break;
    case '/admin/loans/view':
        (new \App\Controllers\AdminLoansController())->view();
        break;

    // Thrift
    case '/thrift':
        (new \App\Controllers\ThriftController())->index();
        break;
    case '/thrift/add':
        (new \App\Controllers\ThriftController())->add();
        break;
    case '/thrift/edit':
        (new \App\Controllers\ThriftController())->edit();
        break;
    case '/thrift/delete':
        (new \App\Controllers\ThriftController())->delete();
        break;
    case '/thrift/export':
        (new \App\Controllers\ThriftController())->export();
        break;

    // Reports & Revenue
    case '/reports':
        (new \App\Controllers\ReportsController())->index();
        break;
    case '/reports/export':
        (new \App\Controllers\ReportsController())->export();
        break;
    case '/revenue':
        (new \App\Controllers\RevenueController())->index();
        break;
    case '/revenue/export':
        (new \App\Controllers\RevenueController())->export();
        break;

    // Roles
    case '/roles':
        (new \App\Controllers\RoleController())->index();
        break;
    case '/roles/add':
        (new \App\Controllers\RoleController())->add();
        break;
    case '/roles/edit':
        (new \App\Controllers\RoleController())->edit();
        break;
    case '/roles/delete':
        (new \App\Controllers\RoleController())->delete();
        break;

    // Admin Payments
    case '/admin/payments':
        (new \App\Controllers\AdminPaymentController())->index();
        break;
    case '/admin/payments/approve':
        (new \App\Controllers\AdminPaymentController())->approve();
        break;
    case '/admin/payments/reject':
        (new \App\Controllers\AdminPaymentController())->reject();
        break;

    // Users
    case '/users':
        (new \App\Controllers\UserController())->index();
        break;
    case '/users/add':
        (new \App\Controllers\UserController())->add();
        break;
    case '/users/edit':
        (new \App\Controllers\UserController())->edit();
        break;
    case '/users/delete':
        (new \App\Controllers\UserController())->delete();
        break;
    case '/users/reset-password':
        (new \App\Controllers\UserController())->resetPassword();
        break;

    // Member portal
    case '/member/paystack/initiate':
        $controller = new \App\Controllers\UserDashboardController();
        $controller->paystackInitiate();
        break;

    case '/member/paywall':
        $controller = new \App\Controllers\UserDashboardController();
        $controller->paywall();
        break;

    case '/member/dashboard':
        $controller = new \App\Controllers\UserDashboardController();
        $controller->index();
        break;
    case '/member/profile':
        (new \App\Controllers\UserDashboardController())->profile();
        break;
    case '/member/dues':
        (new \App\Controllers\UserDashboardController())->dues();
        break;
    case '/member/dues/pay':
        (new \App\Controllers\UserDashboardController())->payDues();
        break;
    case '/member/dues/payment-callback':
        (new \App\Controllers\UserDashboardController())->paymentCallback();
        break;
    case '/member/dues/retry':
        (new \App\Controllers\UserDashboardController())->retryPayment();
        break;
    case '/member/dues/mock-payment':
        $title = $pageTitle = 'Demo Payment Gateway';
        $pageSubtitle = 'This is a demo payment page for testing purposes.';
        ob_start();
        if (!defined('LAYOUT_INCLUDED')) { define('LAYOUT_INCLUDED', true); }
        require_once __DIR__ . '/../app/Views/user/mock-payment.php';
        $content = ob_get_clean();
        include __DIR__ . '/../app/Views/layouts/user.php';
        break;
    case '/member/dues/download-statement':
        (new \App\Controllers\UserDashboardController())->downloadStatement();
        break;
    case '/member/shares':
        (new \App\Controllers\UserDashboardController())->shares();
        break;
    case '/member/shares/pay':
        (new \App\Controllers\UserDashboardController())->payShares();
        break;
    case '/member/shares/payment-callback':
        (new \App\Controllers\UserDashboardController())->sharesPaymentCallback();
        break;
    case '/member/thrift':
        (new \App\Controllers\UserDashboardController())->thrift();
        break;
    case '/member/thrift/pay':
        (new \App\Controllers\UserDashboardController())->payThrift();
        break;
    case '/member/thrift/payment-callback':
        (new \App\Controllers\UserDashboardController())->thriftPaymentCallback();
        break;
    case '/member/events':
        (new \App\Controllers\UserDashboardController())->events();
        break;
    case '/member/calendar':
        (new \App\Controllers\UserDashboardController())->calendar();
        break;
    case '/member/forum':
        (new \App\Controllers\UserDashboardController())->forum();
        break;
    case '/member/id-card':
        (new \App\Controllers\UserDashboardController())->idCard();
        break;
    case '/member/change-password':
        (new \App\Controllers\UserDashboardController())->changePassword();
        break;

    // Investments & Loans
    case '/member/investments':
        (new \App\Controllers\InvestmentsController())->index();
        break;
    case '/member/investments/browse':
        (new \App\Controllers\InvestmentsController())->browse();
        break;
    case '/member/investments/view':
        (new \App\Controllers\InvestmentsController())->viewProject();
        break;
    case '/member/investments/payment-callback':
        (new \App\Controllers\InvestmentsController())->paymentCallback();
        break;
    case '/member/loans/request':
        (new \App\Controllers\InvestmentsController())->requestLoan();
        break;
    case '/member/loans/repay':
        (new \App\Controllers\InvestmentsController())->repayLoan();
        break;
    case '/member/loans/payment-callback':
        (new \App\Controllers\InvestmentsController())->loanPaymentCallback();
        break;

    // Settings
    case '/settings':
    case '/settings/edit':
        (new \App\Controllers\SettingsController())->index();
        break;
    case '/settings/update':
        (new \App\Controllers\SettingsController())->updateSystemSettings();
        break;
    case '/settings/change-password':
        (new \App\Controllers\SettingsController())->changePassword();
        break;

    // Events
    case '/events':
        (new \App\Controllers\EventController())->index();
        break;
    case '/events/add':
        (new \App\Controllers\EventController())->add();
        break;

    // Forum
    case '/forum':
        (new \App\Controllers\ForumController())->index();
        break;
    case '/forum/new-topic':
        (new \App\Controllers\ForumController())->newTopic();
        break;
    case '/forum/search':
        (new \App\Controllers\ForumController())->search();
        break;
    case '/forum/reply':
        (new \App\Controllers\ForumController())->reply();
        break;
    case '/forum/reaction':
        (new \App\Controllers\ForumController())->reaction();
        break;
    case '/forum/mark-solution':
        (new \App\Controllers\ForumController())->markSolution();
        break;
    case '/forum/delete-topic':
        (new \App\Controllers\ForumController())->deleteTopic();
        break;

    // AI Chat
    case '/ai-chat/chat':
        (new \App\Controllers\AIChatController())->chat();
        break;
    case '/ai-chat/test':
        (new \App\Controllers\AIChatController())->test();
        break;

    // Webhooks (server-to-server — no session, auth = HMAC signature)
    case '/webhooks/paystack':
        (new \App\Controllers\WebhookController())->paystack();
        break;

    // Legal pages (public — no auth required)
    case '/legal/privacy-policy':
        (new \App\Controllers\LegalController())->privacyPolicy();
        break;
    case '/legal/terms-of-use':
        (new \App\Controllers\LegalController())->termsOfUse();
        break;
    case '/legal/data-compliance':
        (new \App\Controllers\LegalController())->dataCompliance();
        break;
    case '/legal/ip-infringement':
        (new \App\Controllers\LegalController())->ipInfringement();
        break;

    // Registration
    case '/registration/download-form':
        (new \App\Controllers\RegistrationController())->downloadForm();
        break;
    case '/registration/offline-submission':
        (new \App\Controllers\RegistrationController())->submitOfflineForm();
        break;

    default:
        if (strpos($uri, '/events/edit') === 0) {
            (new \App\Controllers\EventController())->edit(); break;
        }
        if (strpos($uri, '/events/delete') === 0) {
            (new \App\Controllers\EventController())->delete(); break;
        }
        if (strpos($uri, '/forum/category/') === 0) {
            (new \App\Controllers\ForumController())->category(); break;
        }
        if (strpos($uri, '/forum/topic/') === 0) {
            (new \App\Controllers\ForumController())->topic(); break;
        }
        if (strpos($uri, '/forum/edit-topic/') === 0) {
            (new \App\Controllers\ForumController())->editTopic(); break;
        }

        http_response_code(404);
        require __DIR__ . '/../app/Views/404.php';
        break;
}
