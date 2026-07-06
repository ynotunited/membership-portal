<?php

namespace App\Models;

class ForumTopicModel extends BaseModel
{
    public function getTopicsByCategory($categoryId, $page = 1, $limit = 20, $search = '')
    {
        $offset = ($page - 1) * $limit;
        
        $whereClause = "WHERE ft.category_id = :category_id";
        
        if (!empty($search)) {
            $whereClause .= " AND (ft.title LIKE :search OR ft.content LIKE :search)";
        }
        
        $stmt = $this->getConnection()->prepare("
            SELECT 
                ft.*,
                CONCAT(m.firstname, ' ', m.surname) as author_name,
                m.contact_number as author_phone,
                m.photo as author_photo,
                fc.name as category_name,
                fc.slug as category_slug,
                (SELECT COUNT(*) FROM forum_posts fp WHERE fp.topic_id = ft.id) as reply_count,
                (SELECT MAX(fp.created_at) FROM forum_posts fp WHERE fp.topic_id = ft.id) as last_reply_at,
                (SELECT CONCAT(m2.firstname, ' ', m2.surname) FROM forum_posts fp2 
                 JOIN members m2 ON fp2.author_id = m2.id 
                 WHERE fp2.topic_id = ft.id 
                 ORDER BY fp2.created_at DESC LIMIT 1) as last_reply_by_name
            FROM forum_topics ft
            JOIN members m ON ft.author_id = m.id
            JOIN forum_categories fc ON ft.category_id = fc.id
            $whereClause
            ORDER BY 
                ft.is_announcement DESC,
                ft.status = 'sticky' DESC,
                ft.status = 'pinned' DESC,
                ft.last_reply_at DESC,
                ft.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        
        // Bind all parameters individually
        $stmt->bindValue(':category_id', $categoryId);
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%");
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getTopic($id)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT 
                ft.*,
                CONCAT(m.firstname, ' ', m.surname) as author_name,
                m.contact_number as author_phone,
                m.photo as author_photo,
                fc.name as category_name,
                fc.slug as category_slug
            FROM forum_topics ft
            JOIN members m ON ft.author_id = m.id
            JOIN forum_categories fc ON ft.category_id = fc.id
            WHERE ft.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getTopicBySlug($slug)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT 
                ft.*,
                CONCAT(m.firstname, ' ', m.surname) as author_name,
                m.contact_number as author_phone,
                m.photo as author_photo,
                fc.name as category_name,
                fc.slug as category_slug
            FROM forum_topics ft
            JOIN members m ON ft.author_id = m.id
            JOIN forum_categories fc ON ft.category_id = fc.id
            WHERE ft.slug = :slug
        ");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch();
    }

    public function createTopic($data)
    {
        $stmt = $this->getConnection()->prepare("
            INSERT INTO forum_topics (category_id, author_id, title, content, slug) 
            VALUES (:category_id, :author_id, :title, :content, :slug)
        ");
        return $stmt->execute($data);
    }

    /**
     * Return all topics authored by the currently authenticated member.
     * Uses the rls_forum_topics view — returns empty if no RLS context is set.
     */
    public function getMyTopics(int $limit = 50): array
    {
        \App\Models\RowPolicy::ensureDbSync();
        $stmt = $this->getConnection()->prepare("
            SELECT * FROM rls_forum_topics
            ORDER BY created_at DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Fetch a topic only if it belongs to the currently authenticated member.
     * Used for RLS-enforced ownership validation before edit/delete.
     * Returns null if the topic is not owned by the active member.
     */
    public function getTopicByMember(int $topicId): ?array
    {
        \App\Models\RowPolicy::ensureDbSync();
        $stmt = $this->getConnection()->prepare("
            SELECT * FROM rls_forum_topics WHERE id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $topicId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updateTopic($id, $data)
    {
        // Whitelist only safe user-editable fields.
        // status and is_announcement may only be set server-side, not from raw user POST data.
        $stmt = $this->getConnection()->prepare("
            UPDATE forum_topics
            SET title = :title, content = :content, slug = :slug
            WHERE id = :id
        ");
        return $stmt->execute([
            'title'   => $data['title'],
            'content' => $data['content'],
            'slug'    => $data['slug'],
            'id'      => (int)$id,
        ]);
    }

    public function deleteTopic($id)
    {
        $stmt = $this->getConnection()->prepare("DELETE FROM forum_topics WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function incrementViewCount($topicId, $userId)
    {
        // Check if user has already viewed this topic
        $stmt = $this->getConnection()->prepare("
            SELECT id FROM forum_topic_views 
            WHERE topic_id = :topic_id AND user_id = :user_id
        ");
        $stmt->execute(['topic_id' => $topicId, 'user_id' => $userId]);
        
        if (!$stmt->fetch()) {
            // Record the view
            $stmt = $this->getConnection()->prepare("
                INSERT INTO forum_topic_views (topic_id, user_id, ip_address) 
                VALUES (:topic_id, :user_id, :ip_address)
            ");
            $stmt->execute([
                'topic_id' => $topicId,
                'user_id' => $userId,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
            ]);
            
            // Increment view count
            $stmt = $this->getConnection()->prepare("
                UPDATE forum_topics SET view_count = view_count + 1 WHERE id = :id
            ");
            return $stmt->execute(['id' => $topicId]);
        }
        
        return true;
    }

    public function updateLastReply($topicId, $userId)
    {
        $stmt = $this->getConnection()->prepare("
            UPDATE forum_topics 
            SET last_reply_at = NOW(), last_reply_by = :user_id 
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $topicId, 'user_id' => $userId]);
    }

    public function getTotalTopicsByCategory($categoryId, $search = '')
    {
        $whereClause = "WHERE category_id = :category_id";
        $params = ['category_id' => $categoryId];
        
        if (!empty($search)) {
            $whereClause .= " AND (title LIKE :search OR content LIKE :search)";
            $params['search'] = "%$search%";
        }
        
        $stmt = $this->getConnection()->prepare("
            SELECT COUNT(*) as total FROM forum_topics $whereClause
        ");
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'];
    }

    public function searchTopics($search, $page = 1, $limit = 20)
    {
        $offset = ($page - 1) * $limit;
        
        $stmt = $this->getConnection()->prepare("
            SELECT 
                ft.*,
                CONCAT(m.firstname, ' ', m.surname) as author_name,
                fc.name as category_name,
                fc.slug as category_slug,
                (SELECT COUNT(*) FROM forum_posts fp WHERE fp.topic_id = ft.id) as reply_count
            FROM forum_topics ft
            JOIN members m ON ft.author_id = m.id
            JOIN forum_categories fc ON ft.category_id = fc.id
            WHERE ft.title LIKE :search OR ft.content LIKE :search
            ORDER BY ft.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        
        // Bind all parameters individually
        $stmt->bindValue(':search', "%$search%");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
} 