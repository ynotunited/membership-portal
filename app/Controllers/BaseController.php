<?php
namespace App\Controllers;

use App\Helpers\PermissionHelper;

class BaseController
{
    protected function render($view, $data = [])
    {
        // Auto-include user data for admin views if not already provided
        if (strpos($view, 'admin/') === 0 || strpos($view, 'dashboard/') === 0) {
            if (!isset($data['user']) && isset($_SESSION['user_id'])) {
                try {
                    $userModel = new \App\Models\UserModel();
                    $data['user'] = $userModel->getUserById($_SESSION['user_id']);
                } catch (\Exception $e) {
                    // If user model fails, continue without user data
                }
            }

            // Auto-include notification count if not provided
            if (!isset($data['notificationCount']) && isset($_SESSION['user_id'])) {
                try {
                    $notificationModel = new \App\Models\NotificationModel();
                    $data['notificationCount'] = $notificationModel->getUnreadCount($_SESSION['user_id']);
                } catch (\Exception $e) {
                    // If notification model fails, set to 0
                    $data['notificationCount'] = 0;
                }
            }
        }

        extract($data);
        require_once __DIR__ . '/../Views/' . $view . '.php';
    }

    protected function renderUserLayout($view, $data = [])
    {
        // Auto-include user data for member views if not already provided
        if (!isset($data['user']) && isset($_SESSION['user_id'])) {
            try {
                $memberModel = new \App\Models\MemberModel();
                $data['user'] = $memberModel->getMemberById($_SESSION['user_id']);
            } catch (\Exception $e) {
                // If member model fails, continue without user data
            }
        }

        extract($data);

        // Start output buffering to capture the view content
        ob_start();
        require_once __DIR__ . '/../Views/' . $view . '.php';
        $content = ob_get_clean();

        // Include the user layout with the content
        include __DIR__ . '/../Views/layouts/user.php';
    }

    private function getViewContent($view, $data = [])
    {
        extract($data);
        ob_start();
        require_once __DIR__ . '/../Views/' . $view . '.php';
        return ob_get_clean();
    }

    protected function setFlashMessage($type, $message)
    {
        $_SESSION[$type] = $message;
    }

    protected function requireAdmin()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . \App\Helpers\Url::appUrl() . '/login');
            exit;
        }

        \App\Controllers\AuthController::enforceSessionTimeout();

        // Check if user is an admin user (not a member)
        // Admin users have email login and role_id set
        if (!isset($_SESSION['email']) || !isset($_SESSION['role_id'])) {
            header('Location: ' . \App\Helpers\Url::appUrl() . '/login');
            exit;
        }
    }

    protected function requireUser()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . \App\Helpers\Url::appUrl() . '/login');
            exit;
        }

        \App\Controllers\AuthController::enforceSessionTimeout();

        // Members should be identified explicitly, but keep a small legacy
        // fallback for older sessions that only carry contact_number.
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
            return; // Member is properly authenticated
        }

        if (!isset($_SESSION['role']) && isset($_SESSION['contact_number']) && !isset($_SESSION['email'])) {
            $_SESSION['role'] = 'user';
            return; // Allow access for members without role set
        }

        header('Location: ' . \App\Helpers\Url::appUrl() . '/login');
        exit;
    }

    protected function requirePermission($permission)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . \App\Helpers\Url::appUrl() . '/login');
            exit;
        }

        if (!PermissionHelper::hasPermission($permission)) {
            http_response_code(403);
            echo "Access Denied: You don't have permission to access this resource.";
            exit;
        }
    }

    protected function requireAnyPermission($permissions)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . \App\Helpers\Url::appUrl() . '/login');
            exit;
        }

        if (!PermissionHelper::hasAnyPermission($permissions)) {
            http_response_code(403);
            echo "Access Denied: You don't have permission to access this resource.";
            exit;
        }
    }

    protected function requireAllPermissions($permissions)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . \App\Helpers\Url::appUrl() . '/login');
            exit;
        }

        if (!PermissionHelper::hasAllPermissions($permissions)) {
            http_response_code(403);
            echo "Access Denied: You don't have permission to access this resource.";
            exit;
        }
    }

    protected function isAdmin()
    {
        return PermissionHelper::isAdmin();
    }

    protected function isSecretary()
    {
        return PermissionHelper::isSecretary();
    }

    protected function isFinancialSecretary()
    {
        return PermissionHelper::isFinancialSecretary();
    }

    protected function isTreasurer()
    {
        return PermissionHelper::isTreasurer();
    }
}
