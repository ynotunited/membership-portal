<?php
// Set page variables for the layout
$title = 'Search Results';
$pageTitle = 'Search Results for "' . htmlspecialchars($search) . '"';
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Search Results
            </h1>
            <p class="mt-2 text-gray-600">Results for "<span class="font-medium"><?= htmlspecialchars($search) ?></span>"</p>
        </div>
        <a href="<?= \App\Helpers\Url::appUrl() ?>/forum" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg font-medium hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Forum
        </a>
    </div>
</div>

<!-- Search Results -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <?php if (empty($topics)): ?>
    <div class="p-8 text-center">
        <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No results found</h3>
        <p class="text-gray-600 mb-4">Try different keywords or browse the categories</p>
        <a href="<?= \App\Helpers\Url::appUrl() ?>/forum" 
           class="inline-flex items-center px-4 py-2 bg-secondary text-white rounded-lg font-medium hover:bg-blue-700">
            Browse Categories
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
                        <span class="mx-2">•</span>
                        <span>in <a href="<?= \App\Helpers\Url::appUrl() ?>/forum/category/<?= htmlspecialchars($topic['category_slug']) ?>" 
                                   class="text-secondary hover:underline"><?= htmlspecialchars($topic['category_name']) ?></a></span>
                    </p>
                    <div class="flex items-center text-xs text-gray-500 space-x-4">
                        <span><?= $topic['reply_count'] ?> replies</span>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the layout
require __DIR__ . '/../layouts/admin.php';
?> 