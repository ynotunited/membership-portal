<?php

namespace App\Models;

use App\Helpers\Cache;

class CachedForumModel extends BaseModel
{
    private $cache;
    private $cacheTTL = 1200; // 20 minutes
    private $cachePrefix = 'forum_';

    public function __construct()
    {
        parent::__construct();
        $this->cache = Cache::getInstance();
    }

    /**
     * Get forum categories with caching
     */
    public function getCategories()
    {
        $cacheKey = $this->cachePrefix . 'categories';
        
        return $this->cache->remember($cacheKey, function() {
            $sql = "SELECT * FROM forum_categories 
                    WHERE active = 1 
                    ORDER BY sort_order ASC, name ASC";
            
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll();
        }, $this->cacheTTL);
    }

    /**
     * Get category by slug with caching
     */
    public function getCategoryBySlug($slug)
    {
        $cacheKey = $this->cachePrefix . 'category_' . $slug;
        
        return $this->cache->remember($cacheKey, function() use ($slug) {
            $sql = "SELECT * FROM forum_categories WHERE slug = :slug AND active = 1";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute(['slug' => $slug]);
            return $stmt->fetch();
        }, $this->cacheTTL);
    }

    /**
     * Get topics by category with caching
     */
    public function getTopicsByCategory($categorySlug, $limit = 20, $offset = 0)
    {
        $cacheKey = $this->cachePrefix . 'topics_' . $categorySlug . '_' . $limit . '_' . $offset;
        
        return $this->cache->remember($cacheKey, function() use ($categorySlug, $limit, $offset) {
            $sql = "SELECT t.*, m.name as author_name, m.email as author_email,
                           (SELECT COUNT(*) FROM forum_posts WHERE topic_id = t.id) as reply_count,
                           (SELECT MAX(created_at) FROM forum_posts WHERE topic_id = t.id) as last_reply_at
                    FROM forum_topics t
                    LEFT JOIN members m ON t.author_id = m.id
                    LEFT JOIN forum_categories c ON t.category_id = c.id
                    WHERE c.slug = :category_slug AND t.active = 1
                    ORDER BY t.is_pinned DESC, t.last_activity DESC
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue(':category_slug', $categorySlug);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        }, $this->cacheTTL);
    }

    /**
     * Get topic by slug with caching
     */
    public function getTopicBySlug($slug)
    {
        $cacheKey = $this->cachePrefix . 'topic_' . $slug;
        
        return $this->cache->remember($cacheKey, function() use ($slug) {
            $sql = "SELECT t.*, m.name as author_name, m.email as author_email,
                           c.name as category_name, c.slug as category_slug
                    FROM forum_topics t
                    LEFT JOIN members m ON t.author_id = m.id
                    LEFT JOIN forum_categories c ON t.category_id = c.id
                    WHERE t.slug = :slug AND t.active = 1";
            
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute(['slug' => $slug]);
            return $stmt->fetch();
        }, $this->cacheTTL);
    }

    /**
     * Get posts by topic with caching
     */
    public function getPostsByTopic($topicSlug, $limit = 50, $offset = 0)
    {
        $cacheKey = $this->cachePrefix . 'posts_' . $topicSlug . '_' . $limit . '_' . $offset;
        
        return $this->cache->remember($cacheKey, function() use ($topicSlug, $limit, $offset) {
            $sql = "SELECT p.*, m.name as author_name, m.email as author_email, m.avatar
                    FROM forum_posts p
                    LEFT JOIN members m ON p.author_id = m.id
                    LEFT JOIN forum_topics t ON p.topic_id = t.id
                    WHERE t.slug = :topic_slug AND p.active = 1
                    ORDER BY p.created_at ASC
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue(':topic_slug', $topicSlug);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        }, $this->cacheTTL);
    }

    /**
     * Get recent topics with caching
     */
    public function getRecentTopics($limit = 10)
    {
        $cacheKey = $this->cachePrefix . 'recent_topics_' . $limit;
        
        return $this->cache->remember($cacheKey, function() use ($limit) {
            $sql = "SELECT t.*, m.name as author_name, c.name as category_name, c.slug as category_slug,
                           (SELECT COUNT(*) FROM forum_posts WHERE topic_id = t.id) as reply_count
                    FROM forum_topics t
                    LEFT JOIN members m ON t.author_id = m.id
                    LEFT JOIN forum_categories c ON t.category_id = c.id
                    WHERE t.active = 1
                    ORDER BY t.last_activity DESC
                    LIMIT :limit";
            
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        }, 600); // 10 minutes for recent topics
    }

    /**
     * Get popular topics with caching
     */
    public function getPopularTopics($limit = 10)
    {
        $cacheKey = $this->cachePrefix . 'popular_topics_' . $limit;
        
        return $this->cache->remember($cacheKey, function() use ($limit) {
            $sql = "SELECT t.*, m.name as author_name, c.name as category_name, c.slug as category_slug,
                           (SELECT COUNT(*) FROM forum_posts WHERE topic_id = t.id) as reply_count
                    FROM forum_topics t
                    LEFT JOIN members m ON t.author_id = m.id
                    LEFT JOIN forum_categories c ON t.category_id = c.id
                    WHERE t.active = 1
                    ORDER BY (SELECT COUNT(*) FROM forum_posts WHERE topic_id = t.id) DESC, t.last_activity DESC
                    LIMIT :limit";
            
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        }, 1800); // 30 minutes for popular topics
    }

    /**
     * Get forum statistics with caching
     */
    public function getForumStats()
    {
        $cacheKey = $this->cachePrefix . 'stats';
        
        return $this->cache->remember($cacheKey, function() {
            $sql = "SELECT 
                        (SELECT COUNT(*) FROM forum_categories WHERE active = 1) as total_categories,
                        (SELECT COUNT(*) FROM forum_topics WHERE active = 1) as total_topics,
                        (SELECT COUNT(*) FROM forum_posts WHERE active = 1) as total_posts,
                        (SELECT COUNT(DISTINCT author_id) FROM forum_topics WHERE active = 1) as unique_authors,
                        (SELECT COUNT(*) FROM forum_topics WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as topics_this_week,
                        (SELECT COUNT(*) FROM forum_posts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as posts_this_week";
            
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetch();
        }, 3600); // 1 hour for stats
    }

    /**
     * Search topics with caching
     */
    public function searchTopics($query, $limit = 20, $offset = 0)
    {
        $cacheKey = $this->cachePrefix . 'search_' . md5($query) . '_' . $limit . '_' . $offset;
        
        return $this->cache->remember($cacheKey, function() use ($query, $limit, $offset) {
            $sql = "SELECT t.*, m.name as author_name, c.name as category_name, c.slug as category_slug,
                           (SELECT COUNT(*) FROM forum_posts WHERE topic_id = t.id) as reply_count
                    FROM forum_topics t
                    LEFT JOIN members m ON t.author_id = m.id
                    LEFT JOIN forum_categories c ON t.category_id = c.id
                    WHERE t.active = 1 AND (t.title LIKE :query OR t.content LIKE :query)
                    ORDER BY t.last_activity DESC
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bindValue(':query', '%' . $query . '%');
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        }, 900); // 15 minutes for search results
    }

    /**
     * Create topic and clear related cache
     */
    public function createTopic($data)
    {
        $sql = "INSERT INTO forum_topics (
                    category_id, author_id, title, slug, content, 
                    is_pinned, active, created_at, last_activity
                ) VALUES (
                    :category_id, :author_id, :title, :slug, :content,
                    :is_pinned, 1, NOW(), NOW()
                )";
        
        $stmt = $this->getConnection()->prepare($sql);
        $result = $stmt->execute($data);
        
        if ($result) {
            $this->clearForumCache();
        }
        
        return $result;
    }

    /**
     * Create post and clear related cache
     */
    public function createPost($data)
    {
        $sql = "INSERT INTO forum_posts (
                    topic_id, author_id, content, active, created_at
                ) VALUES (
                    :topic_id, :author_id, :content, 1, NOW()
                )";
        
        $stmt = $this->getConnection()->prepare($sql);
        $result = $stmt->execute($data);
        
        if ($result) {
            $this->clearForumCache();
        }
        
        return $result;
    }

    /**
     * Clear forum cache
     */
    private function clearForumCache()
    {
        // Clear list caches
        $this->cache->clearPattern($this->cachePrefix . 'categories');
        $this->cache->clearPattern($this->cachePrefix . 'topics_*');
        $this->cache->clearPattern($this->cachePrefix . 'posts_*');
        $this->cache->clearPattern($this->cachePrefix . 'recent_topics_*');
        $this->cache->clearPattern($this->cachePrefix . 'popular_topics_*');
        $this->cache->clearPattern($this->cachePrefix . 'search_*');
        
        // Clear specific caches
        $this->cache->clearPattern($this->cachePrefix . 'category_*');
        $this->cache->clearPattern($this->cachePrefix . 'topic_*');
        
        // Clear stats
        $this->cache->delete($this->cachePrefix . 'stats');
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats()
    {
        $keys = $this->cache->getKeys($this->cachePrefix . '*');
        $stats = $this->cache->getStats();
        
        return [
            'forum_cache_keys' => count($keys),
            'cache_stats' => $stats,
            'cache_keys' => array_slice($keys, 0, 20)
        ];
    }

    /**
     * Clear all forum cache
     */
    public function clearAllForumCache()
    {
        return $this->cache->clearPattern($this->cachePrefix . '*');
    }
} 