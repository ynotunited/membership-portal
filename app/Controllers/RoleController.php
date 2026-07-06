<?php

namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\PermissionModel;
use App\Helpers\PermissionHelper;

class RoleController extends BaseController
{
    private $roleModel;
    private $permissionModel;

    public function __construct()
    {
        $this->requireAdmin();
        $this->roleModel = new RoleModel();
        $this->permissionModel = new PermissionModel();
    }

    public function index()
    {
        PermissionHelper::requirePermission('roles.view');
        
        $roles = $this->roleModel->getAllRoles();
        $this->render('admin/roles/index', [
            'roles' => $roles,
            'pageTitle' => 'Manage Roles'
        ]);
    }

    public function add()
    {
        PermissionHelper::requirePermission('roles.create');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $permissions = $_POST['permissions'] ?? [];

            if (empty($name)) {
                $this->setFlashMessage('error', 'Role name is required.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/roles/add');
                exit;
            }

            // Check if role name already exists
            $existingRole = $this->roleModel->getRoleByName($name);
            if ($existingRole) {
                $this->setFlashMessage('error', 'Role name already exists.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/roles/add');
                exit;
            }

            $roleData = [
                'name' => $name,
                'description' => $description
            ];

            if ($this->roleModel->createRole($roleData)) {
                $roleId = $this->roleModel->getConnection()->lastInsertId();
                $this->roleModel->assignPermissionsToRole($roleId, $permissions);
                
                $this->setFlashMessage('success', 'Role created successfully.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/roles');
                exit;
            } else {
                $this->setFlashMessage('error', 'Failed to create role.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/roles/add');
                exit;
            }
        }

        $permissions = $this->permissionModel->getAllPermissions();
        $this->render('admin/roles/add', [
            'permissions' => $permissions,
            'pageTitle' => 'Add New Role'
        ]);
    }

    public function edit()
    {
        PermissionHelper::requirePermission('roles.edit');
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->setFlashMessage('error', 'Role ID is required.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/roles');
            exit;
        }

        $role = $this->roleModel->getRole($id);
        if (!$role) {
            $this->setFlashMessage('error', 'Role not found.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/roles');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $permissions = $_POST['permissions'] ?? [];

            if (empty($name)) {
                $this->setFlashMessage('error', 'Role name is required.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/roles/edit?id=' . $id);
                exit;
            }

            // Check if role name already exists (excluding current role)
            $existingRole = $this->roleModel->getRoleByName($name);
            if ($existingRole && $existingRole['id'] != $id) {
                $this->setFlashMessage('error', 'Role name already exists.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/roles/edit?id=' . $id);
                exit;
            }

            $roleData = [
                'name' => $name,
                'description' => $description
            ];

            if ($this->roleModel->updateRole($id, $roleData)) {
                $this->roleModel->assignPermissionsToRole($id, $permissions);
                
                $this->setFlashMessage('success', 'Role updated successfully.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/roles');
                exit;
            } else {
                $this->setFlashMessage('error', 'Failed to update role.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/roles/edit?id=' . $id);
                exit;
            }
        }

        $permissions = $this->permissionModel->getAllPermissions();
        $rolePermissions = $this->roleModel->getRolePermissions($id);
        $rolePermissionIds = array_column($rolePermissions, 'id');

        $this->render('admin/roles/edit', [
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissionIds' => $rolePermissionIds,
            'pageTitle' => 'Edit Role'
        ]);
    }

    public function delete()
    {
        PermissionHelper::requirePermission('roles.delete');
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->setFlashMessage('error', 'Role ID is required.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/roles');
            exit;
        }

        $role = $this->roleModel->getRole($id);
        if (!$role) {
            $this->setFlashMessage('error', 'Role not found.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/roles');
            exit;
        }

        // Prevent deletion of default roles
        if (in_array($role['name'], ['admin', 'secretary', 'financial_secretary', 'treasurer'])) {
            $this->setFlashMessage('error', 'Cannot delete default roles.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/roles');
            exit;
        }

        if ($this->roleModel->deleteRole($id)) {
            $this->setFlashMessage('success', 'Role deleted successfully.');
        } else {
            $this->setFlashMessage('error', 'Failed to delete role.');
        }

        header('Location: ' . \App\Helpers\Url::appUrl() . '/roles');
        exit;
    }
} 