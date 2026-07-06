<?php
// Set page variables for the layout
$title = $topic['title'];
$pageTitle = $topic['title'];
$activePage = 'forum';

// Start output buffering to capture the content
ob_start();
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-2">
                <a href="<?= \App\Helpers\Url::appUrl() ?>/forum" class="hover:text-secondary">Forum</a>
                <span>/</span>
                <a href="<?= \App\Helpers\Url::appUrl() ?>/forum/category/<?= htmlspecialchars($topic['category_slug']) ?>" class="hover:text-secondary">
                    <?= htmlspecialchars($topic['category_name']) ?>
                </a>
                <span>/</span>
                <span class="text-gray-900"><?= htmlspecialchars($topic['title']) ?></span>
            </nav>
            <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($topic['title']) ?></h1>
            <p class="mt-2 text-gray-600">
                Started by <span class="font-medium"><?= htmlspecialchars($topic['author_name']) ?></span>
                <span class="mx-2">•</span>
                <span><?= date('M j, Y \a\t g:i A', strtotime($topic['created_at'])) ?></span>
                <span class="mx-2">•</span>
                <span><?= $topic['view_count'] ?> views</span>
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="<?= \App\Helpers\Url::appUrl() ?>/forum/category/<?= htmlspecialchars($topic['category_slug']) ?>" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg font-medium hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Category
            </a>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800"><?= htmlspecialchars($_SESSION['success']) ?></p>
            </div>
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button onclick="this.parentElement.parentElement.parentElement.parentElement.remove()" class="inline-flex rounded-md bg-green-50 p-1.5 text-green-500 hover:bg-green-100">
                        <span class="sr-only">Dismiss</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!-- Original Post -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex items-start space-x-4">
        <div class="flex-shrink-0">
            <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
                <span class="text-lg font-medium text-gray-600">
                    <?= strtoupper(substr($topic['author_name'], 0, 1)) ?>
                </span>
            </div>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center space-x-2">
                    <span class="font-medium text-gray-900"><?= htmlspecialchars($topic['author_name']) ?></span>
                    <span class="text-sm text-gray-500">•</span>
                    <span class="text-sm text-gray-500"><?= date('M j, Y \a\t g:i A', strtotime($topic['created_at'])) ?></span>
                </div>
                <div class="flex items-center space-x-2">
                    <?php 
                    // Check if current user can edit/delete this topic
                    $currentUserId = $_SESSION['user_id'] ?? null;
                    $canEdit = false;
                    $canDelete = false;
                    
                    if ($currentUserId) {
                        // Use the app singleton — never open a new PDO connection in a view
                        $isAdmin = false;
                        try {
                            $db   = \App\Models\Database::getInstance()->getConnection();
                            $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
                            $stmt->execute([$currentUserId]);
                            $user = $stmt->fetch();
                            $isAdmin = $user && in_array($user['role'], ['admin', 'super_admin']);
                        } catch (\Exception $e) {
                            // Fail safe — deny elevated permissions on error
                        }
                        
                        $canEdit   = ($currentUserId == $topic['author_id']) || $isAdmin;
                        $canDelete = ($currentUserId == $topic['author_id']) || $isAdmin;
                    }
                    ?>
                    
                    <?php if ($canEdit): ?>
                    <a href="<?= \App\Helpers\Url::appUrl() ?>/forum/edit-topic/<?= htmlspecialchars($topic['slug']) ?>" 
                       class="text-xs text-gray-500 hover:text-secondary" 
                       title="Edit Topic">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($canDelete): ?>
                    <button onclick="deleteTopic(<?= $topic['id'] ?>)" 
                            class="text-xs text-gray-500 hover:text-red-600" 
                            title="Delete Topic">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                    <?php endif; ?>
                    
                    <button onclick="markAsSolution(0)" class="text-xs text-gray-500 hover:text-green-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="prose max-w-none">
                <?= nl2br(htmlspecialchars($topic['content'])) ?>
            </div>
        </div>
    </div>
</div>

<!-- Replies -->
<?php if (!empty($posts)): ?>
<div class="space-y-6">
    <?php foreach ($posts as $post): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 <?= $post['is_solution'] ? 'border-green-200 bg-green-50' : '' ?>">
        <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                    <span class="text-sm font-medium text-gray-600">
                        <?= strtoupper(substr($post['author_name'], 0, 1)) ?>
                    </span>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <span class="font-medium text-gray-900"><?= htmlspecialchars($post['author_name']) ?></span>
                        <span class="text-sm text-gray-500">•</span>
                        <span class="text-sm text-gray-500"><?= date('M j, Y \a\t g:i A', strtotime($post['created_at'])) ?></span>
                        <?php if ($post['is_solution']): ?>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Best Answer
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="markAsSolution(<?= $post['id'] ?>)" class="text-xs text-gray-500 hover:text-green-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="prose max-w-none">
                    <?= nl2br(htmlspecialchars($post['content'])) ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Reply Form -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Your Reply</h3>
    <form method="POST" action="<?= \App\Helpers\Url::appUrl() ?>/forum/reply" class="space-y-4">
        <input type="hidden" name="topic_id" value="<?= $topic['id'] ?>">
        <div>
            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                Your Reply <span class="text-red-500">*</span>
            </label>
            <textarea id="content" 
                      name="content" 
                      rows="6" 
                      required 
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                      placeholder="Share your thoughts, answer, or additional information..."></textarea>
        </div>
        <div class="flex items-center justify-end">
            <button type="submit" 
                    class="px-4 py-2 bg-secondary text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                </svg>
                Post Reply
            </button>
        </div>
    </form>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<div class="mt-6 flex items-center justify-center">
    <div class="flex space-x-2">
        <?php if ($currentPage > 1): ?>
        <a href="?page=<?= $currentPage - 1 ?>" 
           class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
            Previous
        </a>
        <?php endif; ?>
        <?php if ($currentPage < $totalPages): ?>
        <a href="?page=<?= $currentPage + 1 ?>" 
           class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
            Next
        </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script>
function markAsSolution(postId) {
    if (confirm('Mark this post as the best answer?')) {
        fetch('<?= \App\Helpers\Url::appUrl() ?>/forum/mark-solution', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'post_id=' + postId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to mark as solution');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
}

function deleteTopic(topicId) {
    if (confirm('Are you sure you want to delete this topic? This action cannot be undone and will also delete all replies.')) {
        fetch('<?= \App\Helpers\Url::appUrl() ?>/forum/delete-topic', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'topic_id=' + topicId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Topic deleted successfully');
                window.location.href = '<?= \App\Helpers\Url::appUrl() ?>/forum/category/<?= htmlspecialchars($topic['category_slug']) ?>';
            } else {
                alert(data.error || 'Failed to delete topic');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the topic');
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-resize textarea
    const textarea = document.getElementById('content');
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });
});
</script>

<?php
// Get the buffered content
$content = ob_get_clean();

// Check if we're using admin layout or member layout
if (defined('LAYOUT_INCLUDED')) {
    // Member layout - content is already captured
    // The layout will be included by the controller
} else {
    // Admin layout - include the admin layout
    require __DIR__ . '/../layouts/admin.php';
}
?> 