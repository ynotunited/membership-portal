<?php
// Set page variables for the layout
$title = $category['name'];
$pageTitle = $category['name'];
$activePage = 'forum';

// Start output buffering to capture the content
ob_start();
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-3" style="background-color: <?= htmlspecialchars($category['color']) ?>20;">
                    <i class="<?= htmlspecialchars($category['icon']) ?> text-2xl" style="color: <?= htmlspecialchars($category['color']) ?>;"></i>
                </div>
                <?= htmlspecialchars($category['name']) ?>
            </h1>
            <p class="mt-2 text-gray-600"><?= htmlspecialchars($category['description']) ?></p>
        </div>
        <div class="flex space-x-3">
            <a href="<?= \App\Helpers\Url::appUrl() ?>/forum/new-topic?category_id=<?= $category['id'] ?>" 
               class="inline-flex items-center px-4 py-2 bg-secondary text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                New Topic
            </a>
            <a href="<?= \App\Helpers\Url::appUrl() ?>/forum" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg font-medium hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Forum
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

<!-- Search Bar -->
<div class="mb-6">
    <form action="<?= \App\Helpers\Url::appUrl() ?>/forum/category/<?= htmlspecialchars($category['slug']) ?>" method="GET" class="max-w-md">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input type="text" 
                   name="search" 
                   placeholder="Search topics in this category..." 
                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                   value="<?= htmlspecialchars($search) ?>">
        </div>
    </form>
</div>

<!-- Topics List -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <?php if (empty($topics)): ?>
    <div class="p-8 text-center">
        <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No topics yet</h3>
        <p class="text-gray-600 mb-4">Be the first to start a discussion in this category!</p>
        <a href="<?= \App\Helpers\Url::appUrl() ?>/forum/new-topic?category_id=<?= $category['id'] ?>" 
           class="inline-flex items-center px-4 py-2 bg-secondary text-white rounded-lg font-medium hover:bg-blue-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Start First Topic
        </a>
    </div>
    <?php else: ?>
    <div class="divide-y divide-gray-200">
        <?php foreach ($topics as $topic): ?>
        <div class="p-6 hover:bg-gray-50 transition-colors">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                        <span class="text-sm font-medium text-gray-600">
                            <?= strtoupper(substr($topic['author_name'], 0, 1)) ?>
                        </span>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-2 mb-2">
                        <?php if ($topic['is_announcement']): ?>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            Announcement
                        </span>
                        <?php endif; ?>
                        <?php if ($topic['status'] === 'sticky'): ?>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            Sticky
                        </span>
                        <?php endif; ?>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">
                        <a href="<?= \App\Helpers\Url::appUrl() ?>/forum/topic/<?= htmlspecialchars($topic['slug']) ?>" 
                           class="hover:text-secondary transition-colors">
                            <?= htmlspecialchars($topic['title']) ?>
                        </a>
                    </h3>
                    <p class="text-sm text-gray-600 mb-3">
                        Started by <span class="font-medium"><?= htmlspecialchars($topic['author_name']) ?></span>
                        <span class="mx-2">•</span>
                        <span><?= date('M j, Y', strtotime($topic['created_at'])) ?></span>
                        <?php if ($topic['last_reply_at']): ?>
                        <span class="mx-2">•</span>
                        <span>Last reply <?= date('M j, Y', strtotime($topic['last_reply_at'])) ?></span>
                        <?php endif; ?>
                    </p>
                    <div class="flex items-center text-xs text-gray-500 space-x-4">
                        <span><?= $topic['view_count'] ?> views</span>
                        <span><?= $topic['reply_count'] ?> replies</span>
                        <?php if ($topic['last_reply_by_name']): ?>
                        <span>by <?= htmlspecialchars($topic['last_reply_by_name']) ?></span>
                        <?php endif; ?>
                        
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
                           class="text-gray-400 hover:text-secondary" 
                           title="Edit Topic">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($canDelete): ?>
                        <button onclick="deleteTopic(<?= $topic['id'] ?>)" 
                                class="text-gray-400 hover:text-red-600" 
                                title="Delete Topic">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Page <?= $currentPage ?> of <?= $totalPages ?>
            </div>
            <div class="flex space-x-2">
                <?php if ($currentPage > 1): ?>
                <a href="?page=<?= $currentPage - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                   class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    Previous
                </a>
                <?php endif; ?>
                <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?= $currentPage + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                   class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    Next
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<script>
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
                location.reload();
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