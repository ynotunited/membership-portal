<?php
// Set page variables for the layout
$title = 'Community Forum';
$pageTitle = 'Community Forum';
$activePage = 'forum';

// Start output buffering to capture the content
ob_start();
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                <svg class="w-8 h-8 text-primary/50 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                </svg>
                Community Forum
            </h1>
            <p class="mt-2 text-gray-600">Connect, share, and learn with fellow farmers</p>
        </div>
        <a href="<?= \App\Helpers\Url::appUrl() ?>/forum/new-topic" 
           class="inline-flex items-center px-4 py-2 bg-secondary text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Start New Topic
        </a>
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

<?php if (isset($_SESSION['error'])): ?>
    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-red-800"><?= htmlspecialchars($_SESSION['error']) ?></p>
            </div>
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button onclick="this.parentElement.parentElement.parentElement.parentElement.remove()" class="inline-flex rounded-md bg-red-50 p-1.5 text-red-500 hover:bg-red-100">
                        <span class="sr-only">Dismiss</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Search Bar -->
<div class="mb-8">
    <form action="<?= \App\Helpers\Url::appUrl() ?>/forum/search" method="GET" class="max-w-2xl">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input type="text" 
                   name="q" 
                   placeholder="Search topics and discussions..." 
                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                   value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <button type="submit" class="text-secondary hover:text-primary-dark">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Forum Categories -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <?php foreach ($categories as $category): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: <?= htmlspecialchars($category['color']) ?>20;">
                    <i class="<?= htmlspecialchars($category['icon']) ?> text-xl" style="color: <?= htmlspecialchars($category['color']) ?>;"></i>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">
                    <a href="<?= \App\Helpers\Url::appUrl() ?>/forum/category/<?= htmlspecialchars($category['slug']) ?>" 
                       class="hover:text-secondary transition-colors">
                        <?= htmlspecialchars($category['name']) ?>
                    </a>
                </h3>
                <p class="text-sm text-gray-600 mb-3"><?= htmlspecialchars($category['description']) ?></p>
                <div class="flex items-center text-xs text-gray-500 space-x-4">
                    <span><?= $category['topic_count'] ?> topics</span>
                    <span><?= $category['unique_authors'] ?> authors</span>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Recent Activity -->
<?php if (!empty($recentPosts)): ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Recent Activity</h2>
    <div class="space-y-4">
        <?php foreach ($recentPosts as $post): ?>
        <div class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                    <span class="text-sm font-medium text-gray-600">
                        <?= strtoupper(substr($post['author_name'], 0, 1)) ?>
                    </span>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-900 mb-1">
                    <a href="<?= \App\Helpers\Url::appUrl() ?>/forum/topic/<?= htmlspecialchars($post['topic_slug']) ?>" 
                       class="font-medium hover:text-secondary">
                        <?= htmlspecialchars($post['topic_title']) ?>
                    </a>
                </p>
                <p class="text-xs text-gray-500">
                    <span class="font-medium"><?= htmlspecialchars($post['author_name']) ?></span> 
                    replied in 
                    <a href="<?= \App\Helpers\Url::appUrl() ?>/forum/category/<?= htmlspecialchars($post['category_slug']) ?>" 
                       class="text-secondary hover:underline">
                        <?= htmlspecialchars($post['category_name']) ?>
                    </a>
                    <span class="ml-2"><?= date('M j, Y', strtotime($post['created_at'])) ?></span>
                </p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

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