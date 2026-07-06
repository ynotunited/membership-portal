<?php

namespace App\Models;

class ForumPostModel extends BaseModel
{
    public function getPostsByTopic($topicId, $page = 1, $limit = 20)
    {
        $offset = ($page - 1) * $limit;
        
        $stmt = $this->getConnection()->prepare("
            SELECT 
                fp.*,
                CONCAT(m.firstname, ' ', m.surname) as author_name,
                m.contact_number as author_phone,
                m.photo as author_photo,
                (SELECT COUNT(*) FROM forum_reactions fr WHERE fr.post_id = fp.id) as reaction_count,
                (SELECT GROUP_CONCAT(fr.reaction_type) FROM forum_reactions fr WHERE fr.post_id = fp.id) as reactions
            FROM forum_posts fp
            JOIN members m ON fp.author_id = m.id
            WHERE fp.topic_id = :topic_id
            ORDER BY fp.created_at ASC
            LIMIT :limit OFFSET :offset
        ");
        
        // Bind all parameters individually
        $stmt->bindValue(':topic_id', $topicId);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getPost($id)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT 
                fp.*,
                CONCAT(m.firstname, ' ', m.surname) as author_name,
                m.contact_number as author_phone,
                m.photo as author_photo
            FROM forum_posts fp
            JOIN members m ON fp.author_id = m.id
            WHERE fp.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function createPost($data)
    {
        $stmt = $this->getConnection()->prepare("
            INSERT INTO forum_posts (topic_id, author_id, content, parent_id) 
            VALUES (:topic_id, :author_id, :content, :parent_id)
        ");
        return $stmt->execute($data);
    }

    public function updatePost($id, $data)
    {
        $data['id'] = $id;
        $stmt = $this->getConnection()->prepare("
            UPDATE forum_posts 
            SET content = :content, updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute($data);
    }

    public function deletePost($id)
    {
        $stmt = $this->getConnection()->prepare("DELETE FROM forum_posts WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Return all posts authored by the currently authenticated member.
     * Uses the rls_forum_posts view — returns empty if no RLS context is set.
     */
    public function getMyPosts(int $limit = 50): array
    {
        \App\Models\RowPolicy::ensureDbSync();
        $stmt = $this->getConnection()->prepare("
            SELECT * FROM rls_forum_posts
            ORDER BY created_at DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Fetch a post only if it belongs to the currently authenticated member.
     * Used for RLS-enforced ownership validation before edit/delete.
     * Returns null if the post is not owned by the active member.
     */
    public function getPostByMember(int $postId): ?array
    {
        \App\Models\RowPolicy::ensureDbSync();
        $stmt = $this->getConnection()->prepare("
            SELECT * FROM rls_forum_posts WHERE id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $postId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function markAsSolution($postId)
    {
        // First, unmark any existing solutions in this topic
        $stmt = $this->getConnection()->prepare("
            UPDATE forum_posts fp
            JOIN forum_topics ft ON fp.topic_id = ft.id
            SET fp.is_solution = 0
            WHERE ft.id = (SELECT topic_id FROM forum_posts WHERE id = :post_id)
        ");
        $stmt->execute(['post_id' => $postId]);
        
        // Mark this post as solution
        $stmt = $this->getConnection()->prepare("
            UPDATE forum_posts SET is_solution = 1 WHERE id = :id
        ");
        return $stmt->execute(['id' => $postId]);
    }

    public function getTotalPostsByTopic($topicId)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT COUNT(*) as total FROM forum_posts WHERE topic_id = :topic_id
        ");
        $stmt->execute(['topic_id' => $topicId]);
        $result = $stmt->fetch();
        return $result['total'];
    }

    public function addReaction($postId, $userId, $reactionType)
    {
        $stmt = $this->getConnection()->prepare("
            INSERT INTO forum_reactions (post_id, user_id, reaction_type) 
            VALUES (:post_id, :user_id, :reaction_type)
            ON DUPLICATE KEY UPDATE reaction_type = :reaction_type
        ");
        return $stmt->execute([
            'post_id' => $postId,
            'user_id' => $userId,
            'reaction_type' => $reactionType
        ]);
    }

    public function removeReaction($postId, $userId)
    {
        $stmt = $this->getConnection()->prepare("
            DELETE FROM forum_reactions 
            WHERE post_id = :post_id AND user_id = :user_id
        ");
        return $stmt->execute(['post_id' => $postId, 'user_id' => $userId]);
    }

    public function getUserReaction($postId, $userId)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT reaction_type FROM forum_reactions 
            WHERE post_id = :post_id AND user_id = :user_id
        ");
        $stmt->execute(['post_id' => $postId, 'user_id' => $userId]);
        $result = $stmt->fetch();
        return $result ? $result['reaction_type'] : null;
    }

    public function getReactionCounts($postId)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT reaction_type, COUNT(*) as count 
            FROM forum_reactions 
            WHERE post_id = :post_id 
            GROUP BY reaction_type
        ");
        $stmt->execute(['post_id' => $postId]);
        return $stmt->fetchAll();
    }

    public function getRecentPosts($limit = 10)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT 
                fp.*,
                CONCAT(m.firstname, ' ', m.surname) as author_name,
                ft.title as topic_title,
                ft.slug as topic_slug,
                fc.name as category_name,
                fc.slug as category_slug
            FROM forum_posts fp
            JOIN members m ON fp.author_id = m.id
            JOIN forum_topics ft ON fp.topic_id = ft.id
            JOIN forum_categories fc ON ft.category_id = fc.id
            ORDER BY fp.created_at DESC
            LIMIT :limit
        ");
        
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
} 