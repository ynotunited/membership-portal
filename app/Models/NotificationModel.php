<?php
namespace App\Models;

class NotificationModel extends BaseModel
{
    public function getUnreadCount($userId)
    {
        $stmt = $this->getConnection()->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0");
        $stmt->execute(['user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    public function getAllForUser($userId)
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function markAllRead($userId)
    {
        $stmt = $this->getConnection()->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id");
        return $stmt->execute(['user_id' => $userId]);
    }

    public function delete($notificationId, $userId)
    {
        $stmt = $this->getConnection()->prepare("DELETE FROM notifications WHERE id = :id AND user_id = :user_id");
        return $stmt->execute(['id' => $notificationId, 'user_id' => $userId]);
    }

    public function add($userId, $message)
    {
        $stmt = $this->getConnection()->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (:user_id, :message, 0, NOW())");
        return $stmt->execute(['user_id' => $userId, 'message' => $message]);
    }
} 