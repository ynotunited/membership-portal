<?php
namespace App\Models;

class SupportTicketModel extends BaseModel
{
    public function addTicket($userId, $message)
    {
        $stmt = $this->getConnection()->prepare("INSERT INTO support_tickets (user_id, message, created_at) VALUES (:user_id, :message, NOW())");
        return $stmt->execute(['user_id' => $userId, 'message' => $message]);
    }

    public function getTicketsByUser($userId)
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM support_tickets WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getAllTickets()
    {
        $stmt = $this->getConnection()->query("SELECT * FROM support_tickets ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
} 