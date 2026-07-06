<?php

namespace App\Models;

class RoleModel extends BaseModel
{
    public function getAllRoles()
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM roles ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRole($id)
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM roles WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getRoleByName($name)
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM roles WHERE name = :name");
        $stmt->execute(['name' => $name]);
        return $stmt->fetch();
    }

    public function createRole($data)
    {
        $stmt = $this->getConnection()->prepare("
            INSERT INTO roles (name, description) 
            VALUES (:name, :description)
        ");
        return $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description']
        ]);
    }

    public function updateRole($id, $data)
    {
        $stmt = $this->getConnection()->prepare("
            UPDATE roles 
            SET name = :name, description = :description 
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description']
        ]);
    }

    public function deleteRole($id)
    {
        $stmt = $this->getConnection()->prepare("DELETE FROM roles WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getRolePermissions($roleId)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT p.* 
            FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = :role_id
            ORDER BY p.module, p.action
        ");
        $stmt->execute(['role_id' => $roleId]);
        return $stmt->fetchAll();
    }

    public function assignPermissionsToRole($roleId, $permissionIds)
    {
        // First, remove all existing permissions for this role
        $stmt = $this->getConnection()->prepare("DELETE FROM role_permissions WHERE role_id = :role_id");
        $stmt->execute(['role_id' => $roleId]);

        // Then add the new permissions
        if (!empty($permissionIds)) {
            $stmt = $this->getConnection()->prepare("
                INSERT INTO role_permissions (role_id, permission_id) 
                VALUES (:role_id, :permission_id)
            ");
            
            foreach ($permissionIds as $permissionId) {
                $stmt->execute([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId
                ]);
            }
        }
        
        return true;
    }

    public function getUserRole($userId)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT r.* 
            FROM roles r
            JOIN users u ON r.id = u.role_id
            WHERE u.id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch();
    }

    public function assignRoleToUser($userId, $roleId)
    {
        // Update the user's role_id directly in the users table
        $stmt = $this->getConnection()->prepare("
            UPDATE users SET role_id = :role_id WHERE id = :user_id
        ");
        return $stmt->execute([
            'user_id' => $userId,
            'role_id' => $roleId
        ]);
    }
} 