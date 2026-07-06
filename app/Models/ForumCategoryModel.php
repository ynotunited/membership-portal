<?php

namespace App\Models;

class ForumCategoryModel extends BaseModel
{
    public function getAllCategories()
    {
        $stmt = $this->getConnection()->prepare("
            SELECT 
                fc.*,
                COUNT(ft.id) as topic_count,
                COUNT(DISTINCT ft.author_id) as unique_authors
            FROM forum_categories fc
            LEFT JOIN forum_topics ft ON fc.id = ft.category_id AND ft.status = 'active'
            WHERE fc.is_active = 1
            GROUP BY fc.id
            ORDER BY fc.order_index ASC, fc.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getCategory($id)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT * FROM forum_categories 
            WHERE id = :id AND is_active = 1
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getCategoryBySlug($slug)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT * FROM forum_categories 
            WHERE slug = :slug AND is_active = 1
        ");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch();
    }

    public function createCategory($data)
    {
        $stmt = $this->getConnection()->prepare("
            INSERT INTO forum_categories (name, description, slug, icon, color, order_index) 
            VALUES (:name, :description, :slug, :icon, :color, :order_index)
        ");
        return $stmt->execute($data);
    }

    public function updateCategory($id, $data)
    {
        $data['id'] = $id;
        $stmt = $this->getConnection()->prepare("
            UPDATE forum_categories 
            SET name = :name, description = :description, slug = :slug, 
                icon = :icon, color = :color, order_index = :order_index, 
                is_active = :is_active
            WHERE id = :id
        ");
        return $stmt->execute($data);
    }

    public function deleteCategory($id)
    {
        $stmt = $this->getConnection()->prepare("
            UPDATE forum_categories SET is_active = 0 WHERE id = :id
        ");
        return $stmt->execute(['id' => $id]);
    }

    public function getCategoryStats($categoryId)
    {
        $stmt = $this->getConnection()->prepare("
            SELECT 
                COUNT(ft.id) as total_topics,
                COUNT(fp.id) as total_posts,
                COUNT(DISTINCT ft.author_id) as unique_authors,
                MAX(ft.created_at) as last_activity
            FROM forum_categories fc
            LEFT JOIN forum_topics ft ON fc.id = ft.category_id
            LEFT JOIN forum_posts fp ON ft.id = fp.topic_id
            WHERE fc.id = :category_id
        ");
        $stmt->execute(['category_id' => $categoryId]);
        return $stmt->fetch();
    }
} 