<?php

namespace App\Models;

class UserModel extends BaseModel
{
    public function getUserById($id)
    {
        $stmt = $this->getConnection()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getUserByEmail($email)
    {
        $stmt = $this->getConnection()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function createUser($data)
    {
        $stmt = $this->getConnection()->prepare('
            INSERT INTO users (email, password, role_id, registration_date, updated_date) 
            VALUES (:email, :password, :role_id, NOW(), NOW())
        ');
        
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        return $stmt->execute([
            'email' => $data['email'],
            'password' => $data['password'],
            'role_id' => $data['role_id']
        ]);
    }

    public function updateUser($id, $data)
    {
        $sql = 'UPDATE users SET email = :email, role_id = :role_id, updated_date = NOW()';
        $params = [
            'email' => $data['email'],
            'role_id' => $data['role_id'],
            'id' => $id
        ];

        // If password is provided, include it in the update
        if (isset($data['password'])) {
            $sql .= ', password = :password';
            $params['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql .= ' WHERE id = :id';
        
        $stmt = $this->getConnection()->prepare($sql);
        return $stmt->execute($params);
    }

    public function updatePassword($id, $password)
    {
        $stmt = $this->getConnection()->prepare('UPDATE users SET password = :password, updated_date = NOW() WHERE id = :id');
        return $stmt->execute([
            'id' => $id,
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);
    }

    public function deleteUser($id)
    {
        $stmt = $this->getConnection()->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getAllUsers()
    {
        $stmt = $this->getConnection()->prepare("
            SELECT u.*, r.name as role_name, r.description as role_description 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            ORDER BY u.registration_date DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function verifyPassword($userId, $currentPassword)
    {
        $stmt = $this->getConnection()->prepare('SELECT password FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($currentPassword, $user['password'])) {
            return true;
        }
        return false;
    }

    public function updatePasswordByEmail($email, $password)
    {
        $stmt = $this->getConnection()->prepare('UPDATE users SET password = :password, updated_date = NOW() WHERE email = :email');
        return $stmt->execute([
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);
    }

    public function getUserWithRole($userId)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT u.*, r.name as role_name, r.description as role_description 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch();
    }

    public function getUsersByRole($roleId)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT u.*, r.name as role_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.role_id = :role_id
            ORDER BY u.registration_date DESC
        ");
        $stmt->execute(['role_id' => $roleId]);
        return $stmt->fetchAll();
    }

    public function countUsersByRole($roleId)
    {
        $stmt = $this->getConnection()->prepare("SELECT COUNT(*) as count FROM users WHERE role_id = :role_id");
        $stmt->execute(['role_id' => $roleId]);
        $result = $stmt->fetch();
        return $result['count'];
    }

    public function getTotalUsers()
    {
        $stmt = $this->getConnection()->prepare("SELECT COUNT(*) as count FROM users");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }
} 