<?php

namespace App\Helpers;

use App\Models\PermissionModel;

class PermissionHelper
{
    private static $permissionModel = null;
    private static $userPermissions = null;

    private static function getPermissionModel()
    {
        if (self::$permissionModel === null) {
            self::$permissionModel = new PermissionModel();
        }
        return self::$permissionModel;
    }

    private static function getUserPermissions($userId)
    {
        if (self::$userPermissions === null) {
            self::$userPermissions = self::getPermissionModel()->getUserPermissions($userId);
        }
        return self::$userPermissions;
    }

    public static function hasPermission($permissionName)
    {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        return self::getPermissionModel()->hasPermission($_SESSION['user_id'], $permissionName);
    }

    public static function hasAnyPermission($permissionNames)
    {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        return self::getPermissionModel()->hasAnyPermission($_SESSION['user_id'], $permissionNames);
    }

    public static function hasAllPermissions($permissionNames)
    {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        foreach ($permissionNames as $permission) {
            if (!self::getPermissionModel()->hasPermission($_SESSION['user_id'], $permission)) {
                return false;
            }
        }
        return true;
    }

    public static function canView($module)
    {
        return self::hasPermission($module . '.view');
    }

    public static function canCreate($module)
    {
        return self::hasPermission($module . '.create');
    }

    public static function canEdit($module)
    {
        return self::hasPermission($module . '.edit');
    }

    public static function canDelete($module)
    {
        return self::hasPermission($module . '.delete');
    }

    public static function canExport($module)
    {
        return self::hasPermission($module . '.export');
    }

    public static function canResetPassword()
    {
        return self::hasPermission('members.password_reset');
    }

    public static function canManageRoles()
    {
        return self::hasAnyPermission(['roles.view', 'roles.create', 'roles.edit', 'roles.delete']);
    }

    public static function isAdmin()
    {
        return self::hasPermission('roles.view'); // Only admin has role management permissions
    }

    public static function isSecretary()
    {
        return self::hasPermission('members.password_reset') && !self::hasPermission('shares.edit');
    }

    public static function isFinancialSecretary()
    {
        return self::hasPermission('shares.edit') && !self::hasPermission('members.password_reset');
    }

    public static function isTreasurer()
    {
        return self::hasPermission('shares.edit') && !self::hasPermission('members.password_reset');
    }

    public static function requirePermission($permissionName)
    {
        if (!self::hasPermission($permissionName)) {
            http_response_code(403);
            echo "Access Denied: You don't have permission to access this resource.";
            exit;
        }
    }

    public static function requireAnyPermission($permissionNames)
    {
        if (!self::hasAnyPermission($permissionNames)) {
            http_response_code(403);
            echo "Access Denied: You don't have permission to access this resource.";
            exit;
        }
    }

    public static function requireAllPermissions($permissionNames)
    {
        if (!self::hasAllPermissions($permissionNames)) {
            http_response_code(403);
            echo "Access Denied: You don't have permission to access this resource.";
            exit;
        }
    }

    public static function clearCache()
    {
        self::$userPermissions = null;
    }

    public static function getUserRoleName()
    {
        if (!isset($_SESSION['user_id'])) {
            return 'guest';
        }

        $roleModel = new \App\Models\RoleModel();
        $role = $roleModel->getUserRole($_SESSION['user_id']);
        return $role ? $role['name'] : 'guest';
    }

    public static function getRoleDisplayName($roleName)
    {
        $displayNames = [
            'admin' => 'Administrator',
            'secretary' => 'Secretary',
            'financial_secretary' => 'Financial Secretary',
            'treasurer' => 'Treasurer',
            'guest' => 'Guest'
        ];

        return $displayNames[$roleName] ?? ucfirst(str_replace('_', ' ', $roleName));
    }
} 