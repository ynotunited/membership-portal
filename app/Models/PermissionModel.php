<?php

namespace App\Models;

class PermissionModel extends BaseModel
{
    public function getAllPermissions()
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM permissions ORDER BY module, action");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPermissionsByModule($module)
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM permissions WHERE module = :module ORDER BY action");
        $stmt->execute(['module' => $module]);
        return $stmt->fetchAll();
    }

    public function getPermission($id)
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM permissions WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getUserPermissions($userId)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT DISTINCT p.* 
            FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            JOIN users u ON rp.role_id = u.role_id
            WHERE u.id = :user_id
            ORDER BY p.module, p.action
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function hasPermission($userId, $permissionName)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT COUNT(*) as count
            FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            JOIN users u ON rp.role_id = u.role_id
            WHERE u.id = :user_id AND p.name = :permission_name
        ");
        $stmt->execute([
            'user_id' => $userId,
            'permission_name' => $permissionName
        ]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    public function hasAnyPermission($userId, $permissionNames)
    {
        if (empty($permissionNames)) {
            return false;
        }

        $placeholders = str_repeat('?,', count($permissionNames) - 1) . '?';
        $stmt = $this->getConnection()->prepare("
            SELECT COUNT(*) as count
            FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            JOIN users u ON rp.role_id = u.role_id
            WHERE u.id = ? AND p.name IN ($placeholders)
        ");
        
        $params = array_merge([$userId], $permissionNames);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    public function getUserPermissionsByModule($userId, $module)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT DISTINCT p.* 
            FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            JOIN users u ON rp.role_id = u.role_id
            WHERE u.id = :user_id AND p.module = :module
            ORDER BY p.action
        ");
        $stmt->execute([
            'user_id' => $userId,
            'module' => $module
        ]);
        return $stmt->fetchAll();
    }

    public function getPermissionsGroupedByModule()
    {
        $stmt = $this->getConnection()->prepare("
            SELECT module, GROUP_CONCAT(name) as permissions
            FROM permissions 
            GROUP BY module 
            ORDER BY module
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
} 