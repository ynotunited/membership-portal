<?php

namespace App\Controllers;

use App\Models\ForumCategoryModel;
use App\Models\ForumTopicModel;
use App\Models\ForumPostModel;

class ForumController extends BaseController
{
    private $categoryModel;
    private $topicModel;
    private $postModel;

    public function __construct()
    {
        // Ensure user is authenticated (both admin and members can access forum)
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . \App\Helpers\Url::appUrl() . '/login');
            exit;
        }
        
        // Set up user session data for the sidebar (only for admin users)
        if (isset($_SESSION['user_id']) && !isset($_SESSION['user'])) {
            // Check if user is an admin user (has role in users table)
            $userModel = new \App\Models\UserModel();
            $user = $userModel->getUserById($_SESSION['user_id']);
            if ($user) {
                $_SESSION['user'] = $user;
            }
        }
        
        $this->categoryModel = new ForumCategoryModel();
        $this->topicModel = new ForumTopicModel();
        $this->postModel = new ForumPostModel();
    }

    public function index()
    {
        $categories = $this->categoryModel->getAllCategories();
        $recentPosts = $this->postModel->getRecentPosts(5);
        
        $title = 'Community Forum';
        $data = [
            'categories' => $categories,
            'recentPosts' => $recentPosts,
            'title' => $title,
            'pageTitle' => $title,
            'activePage' => 'forum'
        ];
        
        // Check if user is admin or member and use appropriate layout
        $userModel = new \App\Models\UserModel();
        $adminUser = $userModel->getUserById($_SESSION['user_id']);
        
        if ($adminUser && isset($adminUser['role'])) {
            // Admin user - use admin layout
            $this->render('forum/index', $data);
        } else {
            // Member user - use member layout
            $this->renderMemberForum($data);
        }
    }
    
    private function renderMemberForum($data)
    {
        // Get user data for the member layout
        $userId = $_SESSION['user_id'];
        $memberModel = new \App\Models\MemberModel();
        $user = $memberModel->getMemberById($userId);
        
        extract($data);
        
        // Define constant to prevent 404 error in view
        if (!defined('LAYOUT_INCLUDED')) {
            define('LAYOUT_INCLUDED', true);
        }
        
        // Include the forum view (it handles its own output buffering)
        require_once __DIR__ . '/../Views/forum/index.php';
        
        // The view will handle ob_get_clean() and set $content
        // We just need to include the member layout
        include __DIR__ . '/../Views/layouts/user.php';
    }

    public function category()
    {
        // Extract slug from URL path
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $slug = '';
        
        // Extract slug from /forum/category/{slug} pattern
        if (preg_match('/\/forum\/category\/([^\/\?]+)/', $uri, $matches)) {
            $slug = $matches[1];
        }
        
        $page = (int)($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        
        $category = $this->categoryModel->getCategoryBySlug($slug);
        if (!$category) {
            $this->setFlashMessage('error', 'Category not found.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/forum');
            exit;
        }
        
        $topics = $this->topicModel->getTopicsByCategory($category['id'], $page, 20, $search);
        $totalTopics = $this->topicModel->getTotalTopicsByCategory($category['id'], $search);
        $totalPages = ceil($totalTopics / 20);
        
        $data = [
            'category' => $category,
            'topics' => $topics,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'title' => $category['name'],
            'pageTitle' => $category['name']
        ];
        
        // Check if user is admin or member and use appropriate layout
        $userModel = new \App\Models\UserModel();
        $adminUser = $userModel->getUserById($_SESSION['user_id']);
        
        if ($adminUser && isset($adminUser['role'])) {
            // Admin user - use admin layout
            $this->render('forum/category', $data);
        } else {
            // Member user - use member layout
            $this->renderMemberForumCategory($data);
        }
    }
    
    private function renderMemberForumCategory($data)
    {
        // Get user data for the member layout
        $userId = $_SESSION['user_id'];
        $memberModel = new \App\Models\MemberModel();
        $user = $memberModel->getMemberById($userId);
        
        extract($data);
        
        // Define constant to prevent 404 error in view
        if (!defined('LAYOUT_INCLUDED')) {
            define('LAYOUT_INCLUDED', true);
        }
        
        // Include the forum category view (it handles its own output buffering)
        require_once __DIR__ . '/../Views/forum/category.php';
        
        // The view will handle ob_get_clean() and set $content
        // We just need to include the member layout
        include __DIR__ . '/../Views/layouts/user.php';
    }

    public function topic()
    {
        // Extract slug from URL path
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $slug = '';
        
        // Extract slug from /forum/topic/{slug} pattern
        if (preg_match('/\/forum\/topic\/([^\/\?]+)/', $uri, $matches)) {
            $slug = $matches[1];
        }
        
        $page = (int)($_GET['page'] ?? 1);
        
        $topic = $this->topicModel->getTopicBySlug($slug);
        if (!$topic) {
            $this->setFlashMessage('error', 'Topic not found.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/forum');
            exit;
        }
        
        // Increment view count
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            $memberId = $this->getMemberId($userId);
            if ($memberId) {
                $this->topicModel->incrementViewCount($topic['id'], $memberId);
            }
        }
        
        $posts = $this->postModel->getPostsByTopic($topic['id'], $page, 20);
        $totalPosts = $this->postModel->getTotalPostsByTopic($topic['id']);
        $totalPages = ceil($totalPosts / 20);
        
        $data = [
            'topic' => $topic,
            'posts' => $posts,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'title' => $topic['title'],
            'pageTitle' => $topic['title']
        ];
        
        // Check if user is admin or member and use appropriate layout
        $userModel = new \App\Models\UserModel();
        $adminUser = $userModel->getUserById($_SESSION['user_id']);
        
        if ($adminUser && isset($adminUser['role'])) {
            // Admin user - use admin layout
            $this->render('forum/topic', $data);
        } else {
            // Member user - use member layout
            $this->renderMemberForumTopic($data);
        }
    }
    
    private function renderMemberForumTopic($data)
    {
        // Get user data for the member layout
        $userId = $_SESSION['user_id'];
        $memberModel = new \App\Models\MemberModel();
        $user = $memberModel->getMemberById($userId);
        
        extract($data);
        
        // Define constant to prevent 404 error in view
        if (!defined('LAYOUT_INCLUDED')) {
            define('LAYOUT_INCLUDED', true);
        }
        
        // Include the forum topic view (it handles its own output buffering)
        require_once __DIR__ . '/../Views/forum/topic.php';
        
        // The view will handle ob_get_clean() and set $content
        // We just need to include the member layout
        include __DIR__ . '/../Views/layouts/user.php';
    }

    public function newTopic()
    {
        $categoryId = $_GET['category_id'] ?? null;
        $categories = $this->categoryModel->getAllCategories();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Rate-limit topic creation: 5 per 5 min per user
            [$max, $win] = \App\Helpers\RateLimiter::limitsFor('forum_new_topic');
            \App\Helpers\RateLimiter::enforceForHtml(
                'forum_new_topic',
                'user_' . ($_SESSION['user_id'] ?? \App\Helpers\RateLimiter::clientIp()),
                $max, $win,
                \App\Helpers\Url::appUrl() . '/forum/new-topic',
                'error'
            );
            $title      = \App\Helpers\SecurityHelper::sanitizeString($_POST['title']      ?? '');
            $content    = \App\Helpers\SecurityHelper::sanitizeString($_POST['content']     ?? '');
            $categoryId = (int)($_POST['category_id'] ?? 0);
            
            if (empty($title) || empty($content) || empty($categoryId)) {
                $this->setFlashMessage('error', 'All fields are required.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/forum/new-topic');
                exit;
            }
            
            $slug = $this->createSlug($title);
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                $this->setFlashMessage('error', 'You must be logged in to create a topic.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/login');
                exit;
            }
            
            // Check if user is a member (for forum participation)
            $memberId = $this->getMemberId($userId);
            if (!$memberId) {
                $this->setFlashMessage('error', 'Only registered members can create forum topics. Please register as a member first.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/login');
                exit;
            }
            
            $data = [
                'category_id' => $categoryId,
                'author_id' => $memberId,
                'title' => $title,
                'content' => $content,
                'slug' => $slug
            ];
            
            if ($this->topicModel->createTopic($data)) {
                $this->setFlashMessage('success', 'Topic created successfully!');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/forum/topic/' . $slug);
                exit;
            } else {
                $this->setFlashMessage('error', 'Failed to create topic.');
            }
        }
        
        $data = [
            'categories' => $categories,
            'selectedCategory' => $categoryId,
            'title' => 'Create New Topic',
            'pageTitle' => 'Create New Topic'
        ];
        $this->render('forum/new-topic', $data);
    }

    public function reply()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \App\Helpers\Url::appUrl() . '/forum');
            exit;
        }

        // Rate-limit replies: 10 per minute per user
        [$max, $win] = \App\Helpers\RateLimiter::limitsFor('forum_reply');
        \App\Helpers\RateLimiter::enforceForHtml(
            'forum_reply',
            'user_' . ($_SESSION['user_id'] ?? \App\Helpers\RateLimiter::clientIp()),
            $max, $win,
            \App\Helpers\Url::appUrl() . '/forum',
            'error'
        );
        $topicId  = (int)($_POST['topic_id'] ?? 0);
        $content  = \App\Helpers\SecurityHelper::sanitizeString($_POST['content'] ?? '');
        $parentId = (int)($_POST['parent_id'] ?? 0);
        
        if (empty($content) || empty($topicId)) {
            $this->setFlashMessage('error', 'Content is required.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/forum/topic/' . $topicId);
            exit;
        }
        
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $this->setFlashMessage('error', 'You must be logged in to reply.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/login');
            exit;
        }
        
        // Check if user is a member (for forum participation)
        $memberId = $this->getMemberId($userId);
        if (!$memberId) {
            $this->setFlashMessage('error', 'Only registered members can reply to forum topics. Please register as a member first.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/login');
            exit;
        }
        
        $data = [
            'topic_id' => $topicId,
            'author_id' => $memberId,
            'content' => $content,
            'parent_id' => $parentId ?: null
        ];
        
        if ($this->postModel->createPost($data)) {
            // Update topic's last reply
            $this->topicModel->updateLastReply($topicId, $memberId);
            
            $this->setFlashMessage('success', 'Reply posted successfully!');
        } else {
            $this->setFlashMessage('error', 'Failed to post reply.');
        }
        
        $topic = $this->topicModel->getTopic($topicId);
        header('Location: ' . \App\Helpers\Url::appUrl() . '/forum/topic/' . $topic['slug']);
        exit;
    }

    public function search()
    {
        $search = $_GET['q'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        
        if (empty($search)) {
            header('Location: ' . \App\Helpers\Url::appUrl() . '/forum');
            exit;
        }
        
        $topics = $this->topicModel->searchTopics($search, $page, 20);
        
        $data = [
            'search' => $search,
            'topics' => $topics,
            'currentPage' => $page,
            'title' => 'Search Results',
            'pageTitle' => 'Search Results for "' . htmlspecialchars($search) . '"'
        ];
        $this->render('forum/search', $data);
    }

    public function reaction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        // Rate-limit reactions: 30 per minute per user
        [$max, $win] = \App\Helpers\RateLimiter::limitsFor('forum_reaction');
        \App\Helpers\RateLimiter::enforceForApi(
            'forum_reaction',
            'user_' . ($_SESSION['user_id'] ?? \App\Helpers\RateLimiter::clientIp()),
            $max,
            $win
        );
        $postId       = (int)($_POST['post_id'] ?? 0);
        $reactionType = \App\Helpers\SecurityHelper::sanitizeString($_POST['reaction_type'] ?? '');
        $action       = \App\Helpers\SecurityHelper::sanitizeString($_POST['action'] ?? 'add');
        
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        }
        
        // Get member ID for forum participation
        $memberId = $this->getMemberId($userId);
        if (!$memberId) {
            http_response_code(403);
            echo json_encode(['error' => 'Only registered members can react to posts']);
            exit;
        }
        
        if ($action === 'remove') {
            $result = $this->postModel->removeReaction($postId, $memberId);
        } else {
            $result = $this->postModel->addReaction($postId, $memberId, $reactionType);
        }
        
        if ($result) {
            $reactions = $this->postModel->getReactionCounts($postId);
            echo json_encode(['success' => true, 'reactions' => $reactions]);
        } else {
            echo json_encode(['error' => 'Failed to update reaction']);
        }
        exit;
    }

    public function markSolution()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $postId = (int)($_POST['post_id'] ?? 0);
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        }

        $memberId = $this->getMemberId($userId);
        if (!$memberId) {
            http_response_code(403);
            echo json_encode(['error' => 'Only registered members can mark solutions']);
            exit;
        }

        // Load the post so we can find its parent topic
        $post = $this->postModel->getPost($postId);
        if (!$post) {
            http_response_code(404);
            echo json_encode(['error' => 'Post not found']);
            exit;
        }

        // Load the topic so we can verify the current user is its author or an admin
        $topic = $this->topicModel->getTopic($post['topic_id']);
        if (!$topic) {
            http_response_code(404);
            echo json_encode(['error' => 'Topic not found']);
            exit;
        }

        // Ownership check: only the topic author or an admin may mark a solution
        if ($topic['author_id'] != $memberId && !$this->checkUserIsAdmin($userId)) {
            http_response_code(403);
            echo json_encode(['error' => 'Only the topic author can mark a solution']);
            exit;
        }

        if ($this->postModel->markAsSolution($postId)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to mark as solution']);
        }
        exit;
    }

    public function editTopic()
    {
        // Extract slug from URL path
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $slug = '';
        
        // Extract slug from /forum/edit-topic/{slug} pattern
        if (preg_match('/\/forum\/edit-topic\/([^\/\?]+)/', $uri, $matches)) {
            $slug = $matches[1];
        }
        
        $topic = $this->topicModel->getTopicBySlug($slug);
        if (!$topic) {
            $this->setFlashMessage('error', 'Topic not found.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/forum');
            exit;
        }
        
        // Check if user can edit this topic (author or admin)
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $this->setFlashMessage('error', 'You must be logged in to edit topics.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/login');
            exit;
        }
        
        $memberId = $this->getMemberId($userId);
        if (!$memberId) {
            $this->setFlashMessage('error', 'You can only edit your own topics.');
            header('Location: ' . \App\Helpers\Url::appUrl() . '/forum/topic/' . $slug);
            exit;
        }

        $isAdmin = $this->checkUserIsAdmin($userId);
        if (!$isAdmin) {
            // DB-layer RLS ownership check — returns null if topic is not owned by this member
            $ownedTopic = $this->topicModel->getTopicByMember((int)$topic['id']);
            if (!$ownedTopic) {
                $this->setFlashMessage('error', 'You can only edit your own topics.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/forum/topic/' . $slug);
                exit;
            }
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title   = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            
            if (empty($title) || empty($content)) {
                $this->setFlashMessage('error', 'Title and content are required.');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/forum/edit-topic/' . $slug);
                exit;
            }
            
            $newSlug = $this->createSlug($title);
            
            $data = [
                'title' => $title,
                'content' => $content,
                'slug' => $newSlug
            ];
            
            if ($this->topicModel->updateTopic($topic['id'], $data)) {
                $this->setFlashMessage('success', 'Topic updated successfully!');
                header('Location: ' . \App\Helpers\Url::appUrl() . '/forum/topic/' . $newSlug);
                exit;
            } else {
                $this->setFlashMessage('error', 'Failed to update topic.');
            }
        }
        
        $categories = $this->categoryModel->getAllCategories();
        
        $data = [
            'topic' => $topic,
            'categories' => $categories,
            'title' => 'Edit Topic',
            'pageTitle' => 'Edit Topic'
        ];
        $this->render('forum/edit-topic', $data);
    }

    public function deleteTopic()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
        
        $topicId  = (int)($_POST['topic_id'] ?? 0);
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        }
        
        // Get topic details
        $topic = $this->topicModel->getTopic($topicId);
        if (!$topic) {
            http_response_code(404);
            echo json_encode(['error' => 'Topic not found']);
            exit;
        }
        
        // Check if user can delete this topic (author or admin)
        $memberId = $this->getMemberId($userId);
        if (!$memberId) {
            http_response_code(403);
            echo json_encode(['error' => 'You can only delete your own topics']);
            exit;
        }

        $isAdmin = $this->checkUserIsAdmin($userId);
        if (!$isAdmin) {
            // DB-layer RLS ownership check — returns null if topic is not owned by this member
            $ownedTopic = $this->topicModel->getTopicByMember($topicId);
            if (!$ownedTopic) {
                http_response_code(403);
                echo json_encode(['error' => 'You can only delete your own topics']);
                exit;
            }
        }
        
        if ($this->topicModel->deleteTopic($topicId)) {
            echo json_encode(['success' => true, 'message' => 'Topic deleted successfully']);
        } else {
            echo json_encode(['error' => 'Failed to delete topic']);
        }
        exit;
    }

    private function createSlug($title)
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Ensure uniqueness
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->topicModel->getTopicBySlug($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    private function getMemberId($userId)
    {
        // First check if the user is already a member
        $stmt = $this->categoryModel->getConnection()->prepare("SELECT id FROM members WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $member = $stmt->fetch();
        
        if ($member) {
            return $member['id'];
        }
        
        // If not found, check if this is an admin user and find their member record by email
        $stmt = $this->categoryModel->getConnection()->prepare("
            SELECT m.id 
            FROM members m 
            JOIN users u ON m.email = u.email 
            WHERE u.id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);
        $member = $stmt->fetch();
        
        return $member ? $member['id'] : null;
    }

    protected function checkUserIsAdmin($userId)
    {
        // Check if user is admin
        $stmt = $this->categoryModel->getConnection()->prepare("
            SELECT role FROM users WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        return $user && in_array($user['role'], ['admin', 'super_admin']);
    }
} 