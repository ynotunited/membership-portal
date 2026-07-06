<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Helpers\PermissionHelper;

class UserController extends BaseController
{
    private $userModel;
    private $roleModel;

    public function __construct()
    {
        $this->requireAdmin(); // Only admins can manage users
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
    }

    public function index()
    {
        PermissionHelper::requirePermission('users.view');
        
        $users = $this->userModel->getAllUsers();
        $roles = $this->roleModel->getAllRoles();
        
        $this->render('admin/users/index', [
            'users' => $users,
            'roles' => $roles,
            'pageTitle' => 'Manage Users'
        ]);
    }

    public function add()
    {
        PermissionHelper::requirePermission('users.create');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $roleId = (int)($_POST['role_id'] ?? 0);

            // Validation
            if (empty($email) || empty($password)) {
                $this->setFlashMessage('error', 'Email and password are required.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/users/add');
                exit;
            }

            if ($password !== $confirmPassword) {
                $this->setFlashMessage('error', 'Passwords do not match.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/users/add');
                exit;
            }

            if (strlen($password) < 8) {
                $this->setFlashMessage('error', 'Password must be at least 8 characters long.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/users/add');
                exit;
            }

            // Check if email already exists
            $existingUser = $this->userModel->getUserByEmail($email);
            if ($existingUser) {
                $this->setFlashMessage('error', 'Email already exists.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/users/add');
                exit;
            }

            // Validate role exists
            $role = $this->roleModel->getRole($roleId);
            if (!$role) {
                $this->setFlashMessage('error', 'Invalid role selected.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/users/add');
                exit;
            }

            // Create user
            $userData = [
                'email' => $email,
                'password' => $password,
                'role_id' => $roleId
            ];

            if ($this->userModel->createUser($userData)) {
                $this->setFlashMessage('success', 'User created successfully.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/users');
                exit;
            } else {
                $this->setFlashMessage('error', 'Failed to create user.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/users/add');
                exit;
            }
        }

        $roles = $this->roleModel->getAllRoles();
        
        $this->render('admin/users/add', [
            'roles' => $roles,
            'pageTitle' => 'Add User'
        ]);
    }

    public function edit()
    {
        PermissionHelper::requirePermission('users.edit');
        
        $userId = (int)($_GET['id'] ?? 0);
        if (!$userId) {
            $this->setFlashMessage('error', 'Invalid user ID.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/users');
            exit;
        }

        $user = $this->userModel->getUserById($userId);
        if (!$user) {
            $this->setFlashMessage('error', 'User not found.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/users');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $roleId = (int)($_POST['role_id'] ?? 0);

            // Validation
            if (empty($email)) {
                $this->setFlashMessage('error', 'Email is required.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/users/edit?id=' . $userId);
                exit;
            }

            // Check if email already exists (excluding current user)
            $existingUser = $this->userModel->getUserByEmail($email);
            if ($existingUser && $existingUser['id'] != $userId) {
                $this->setFlashMessage('error', 'Email already exists.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/users/edit?id=' . $userId);
                exit;
            }

            // Validate role exists
            $role = $this->roleModel->getRole($roleId);
            if (!$role) {
                $this->setFlashMessage('error', 'Invalid role selected.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/users/edit?id=' . $userId);
                exit;
            }

            // Prepare update data
            $updateData = [
                'email' => $email,
                'role_id' => $roleId
            ];

            // Add password if provided
            if (!empty($password)) {
                if ($password !== $confirmPassword) {
                    $this->setFlashMessage('error', 'Passwords do not match.');
                    header('Location: ' . \App\Helpers\Url::appUrl() . '/users/edit?id=' . $userId);
                    exit;
                }

                if (strlen($password) < 8) {
                    $this->setFlashMessage('error', 'Password must be at least 8 characters long.');
                    header('Location: ' . \App\Helpers\Url::appUrl() . '/users/edit?id=' . $userId);
                    exit;
                }

                $updateData['password'] = $password;
            }

            if ($this->userModel->updateUser($userId, $updateData)) {
                $this->setFlashMessage('success', 'User updated successfully.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/users');
                exit;
            } else {
                $this->setFlashMessage('error', 'Failed to update user.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/users/edit?id=' . $userId);
                exit;
            }
        }

        $roles = $this->roleModel->getAllRoles();
        
        $this->render('admin/users/edit', [
            'user' => $user,
            'roles' => $roles,
            'pageTitle' => 'Edit User'
        ]);
    }

    public function delete()
    {
        PermissionHelper::requirePermission('users.delete');
        
        $userId = (int)($_GET['id'] ?? 0);
        if (!$userId) {
            $this->setFlashMessage('error', 'Invalid user ID.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/users');
            exit;
        }

        // Prevent admin from deleting themselves
        if ($userId == $_SESSION['user_id']) {
            $this->setFlashMessage('error', 'You cannot delete your own account.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/users');
            exit;
        }

        $user = $this->userModel->getUserById($userId);
        if (!$user) {
            $this->setFlashMessage('error', 'User not found.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/users');
            exit;
        }

        if ($this->userModel->deleteUser($userId)) {
            $this->setFlashMessage('success', 'User deleted successfully.');
        } else {
            $this->setFlashMessage('error', 'Failed to delete user.');
        }

        header('Location: ' . \App\Helpers\Url::appUrl() . '/users');
        exit;
    }

    public function resetPassword()
    {
        PermissionHelper::requirePermission('users.password_reset');

        $userId = (int)($_GET['id'] ?? 0);
        if (!$userId) {
            $this->setFlashMessage('error', 'Invalid user ID.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/users');
            exit;
        }

        $user = $this->userModel->getUserById($userId);
        if (!$user) {
            $this->setFlashMessage('error', 'User not found.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/users');
            exit;
        }

        // Generate a cryptographically secure temporary password
        $newPassword = $this->generateRandomPassword();

        if ($this->userModel->updatePassword($userId, $newPassword)) {
            try {
                \App\Helpers\Mailer::send(
                    $user['email'],
                    'Your GAFCONL account password has been reset',
                    '<p>Your account password was reset by an administrator.</p>' .
                    '<p>Your temporary password is: <strong>' . htmlspecialchars($newPassword) . '</strong></p>' .
                    '<p>Please log in and change your password immediately.</p>'
                );
                $this->setFlashMessage('success', 'Password reset successfully. The new password has been emailed to the user.');
            } catch (\Exception $e) {
                error_log('Password reset email failed: ' . $e->getMessage());
                // Do NOT expose the password in the UI — log it securely instead
                \App\Helpers\SecurityLogger::event('INFO', 'admin.password_reset_email_failed', [
                    'user_id'  => $userId,
                    'email'    => $user['email'],
                    'reason'   => $e->getMessage(),
                ]);
                $this->setFlashMessage('warning', 'Password was reset but the notification email could not be delivered. Please contact the user directly.');
            }
        } else {
            $this->setFlashMessage('error', 'Failed to reset password.');
        }

        header('Location: ' . \App\Helpers\Url::appUrl() . '/users');
        exit;
    }

    /**
     * Generate a cryptographically secure random password.
     * Uses random_bytes() instead of rand().
     */
    private function generateRandomPassword(int $length = 12): string
    {
        $chars    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $charLen  = strlen($chars);
        $password = '';
        $bytes    = random_bytes($length);

        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[ord($bytes[$i]) % $charLen];
        }

        return $password;
    }
} 