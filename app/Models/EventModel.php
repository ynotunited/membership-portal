<?php

namespace App\Models;

class EventModel extends BaseModel
{
    public function getAllEvents()
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM events ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getEvent($id)
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM events WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function addEvent($data)
    {
        $stmt = $this->getConnection()->prepare("
            INSERT INTO events (title, description, start_date, end_date, status, created_at) 
            VALUES (:title, :description, :start_date, :end_date, :status, NOW())
        ");
        return $stmt->execute($data);
    }

    public function updateEvent($id, $data)
    {
        $stmt = $this->getConnection()->prepare("
            UPDATE events 
            SET title = :title, description = :description, start_date = :start_date, 
                end_date = :end_date, status = :status 
            WHERE id = :id
        ");
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function deleteEvent($id)
    {
        $stmt = $this->getConnection()->prepare("DELETE FROM events WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getUpcomingEvents($days = 7)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT * FROM events 
            WHERE start_date >= CURDATE() 
            AND start_date <= DATE_ADD(CURDATE(), INTERVAL :days DAY)
            AND status = 'active'
            ORDER BY start_date ASC
        ");
        $stmt->bindValue(':days', $days, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getActiveEvents()
    {
        $stmt = $this->getConnection()->prepare("
            SELECT * FROM events 
            WHERE status = 'active' 
            ORDER BY start_date ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getEventsInDateRange($dateFrom, $dateTo)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT * FROM events 
            WHERE start_date BETWEEN :date_from AND :date_to
            ORDER BY start_date ASC
        ");
        $stmt->execute([
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        return $stmt->fetchAll();
    }

    public function getUpcomingEventsCount()
    {
        $stmt = $this->getConnection()->prepare("
            SELECT COUNT(*) as count 
            FROM events 
            WHERE start_date >= CURDATE() AND status = 'active'
        ");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    public function getPastEvents($limit = 10)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT * FROM events 
            WHERE end_date < CURDATE() 
            AND status = 'active'
            ORDER BY end_date DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function userHasRSVP($eventId, $userId)
    {
        $stmt = $this->getConnection()->prepare("SELECT COUNT(*) FROM event_rsvps WHERE event_id = :event_id AND user_id = :user_id");
        $stmt->execute(['event_id' => $eventId, 'user_id' => $userId]);
        return $stmt->fetchColumn() > 0;
    }

    public function rsvp($eventId, $userId)
    {
        $stmt = $this->getConnection()->prepare("INSERT IGNORE INTO event_rsvps (event_id, user_id, created_at) VALUES (:event_id, :user_id, NOW())");
        return $stmt->execute(['event_id' => $eventId, 'user_id' => $userId]);
    }

    public function cancelRSVP($eventId, $userId)
    {
        $stmt = $this->getConnection()->prepare("DELETE FROM event_rsvps WHERE event_id = :event_id AND user_id = :user_id");
        return $stmt->execute(['event_id' => $eventId, 'user_id' => $userId]);
    }
} 