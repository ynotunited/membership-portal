<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SettingsModel;
use App\Models\UserModel;

class SettingsController extends BaseController
{
    private $settingsModel;
    private $userModel;

    public function __construct()
    {
        $this->requireAdmin();
        $this->settingsModel = new SettingsModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $settings = $this->settingsModel->getSettings();
        $title = 'System Settings';
        $data = [
            'settings' => $settings,
            'title' => $title,
            'pageTitle' => $title
        ];
        $this->render('admin/settings', $data);
    }

    public function updateSystemSettings()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'system_name' => trim($_POST['system_name'] ?? ''),
                'currency' => trim($_POST['currency'] ?? '₦')
            ];

            // Handle logo upload if provided
            if (!empty($_FILES['logo']['name'])) {
                $logoResult = $this->handleLogoUpload($_FILES['logo']);
                if ($logoResult['success']) {
                    $data['logo'] = $logoResult['filename'];
                } else {
                    $this->setFlashMessage('error', $logoResult['message']);
                    header('Location: ' . \App\Helpers\Url::appUrl() . '/settings');
                    exit;
                }
            }

            $result = $this->settingsModel->updateSettings($data);
            if ($result) {
                $this->setFlashMessage('success', 'System settings updated successfully!');
            } else {
                $this->setFlashMessage('error', 'Failed to update system settings.');
            }

            header('Location: ' . \App\Helpers\Url::appUrl() . '/settings');
            exit;
        }
    }

    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validation
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $this->setFlashMessage('error', 'All password fields are required.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/settings');
                exit;
            }

            if ($newPassword !== $confirmPassword) {
                $this->setFlashMessage('error', 'New password and confirm password do not match.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/settings');
                exit;
            }

            if (strlen($newPassword) < 8) {
                $this->setFlashMessage('error', 'New password must be at least 8 characters long.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/settings');
                exit;
            }

            // Verify current password and update
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                $this->setFlashMessage('error', 'User session not found.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/settings');
                exit;
            }

            $result = $this->userModel->changePassword($userId, $currentPassword, $newPassword);
            if ($result['success']) {
                $this->setFlashMessage('success', 'Password changed successfully!');
            } else {
                $this->setFlashMessage('error', $result['message']);
            }

            header('Location: ' . \App\Helpers\Url::appUrl() . '/settings');
            exit;
        }
    }

    private function handleLogoUpload($file)
    {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.'];
        }

        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'File size too large. Maximum size is 5MB.'];
        }

        $uploadDir = dirname(__DIR__) . '/../public/uploads/logos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = 'logo_' . time() . '_' . uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => true, 'filename' => 'uploads/logos/' . $filename];
        } else {
            return ['success' => false, 'message' => 'Failed to upload file.'];
        }
    }
} 