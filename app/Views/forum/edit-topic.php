<?php
// Set page variables for the layout
$title = 'Edit Topic';
$pageTitle = 'Edit Topic';
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
                <a href="<?= \App\Helpers\Url::appUrl() ?>/forum/topic/<?= htmlspecialchars($topic['slug']) ?>" class="hover:text-secondary">
                    <?= htmlspecialchars($topic['title']) ?>
                </a>
                <span>/</span>
                <span class="text-gray-900">Edit</span>
            </nav>
            <h1 class="text-3xl font-bold text-gray-900">Edit Topic</h1>
            <p class="mt-2 text-gray-600">Update your topic title and content</p>
        </div>
        <div class="flex space-x-3">
            <a href="<?= \App\Helpers\Url::appUrl() ?>/forum/topic/<?= htmlspecialchars($topic['slug']) ?>" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg font-medium hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Cancel
            </a>
        </div>
    </div>
</div>

<!-- Flash Messages -->
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

<!-- Edit Form -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <form action="<?= \App\Helpers\Url::appUrl() ?>/forum/edit-topic/<?= htmlspecialchars($topic['slug']) ?>" method="POST">
        <div class="space-y-6">
            <!-- Category Selection (Read-only) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <div class="relative">
                    <select disabled class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed">
                        <option value="<?= $topic['category_id'] ?>"><?= htmlspecialchars($topic['category_name']) ?></option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
                <p class="mt-1 text-sm text-gray-500">Category cannot be changed after creation</p>
            </div>

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Topic Title</label>
                <input type="text" 
                       id="title" 
                       name="title" 
                       value="<?= htmlspecialchars($topic['title']) ?>"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                       placeholder="Enter your topic title..."
                       required>
            </div>

            <!-- Content -->
            <div>
                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                <textarea id="content" 
                          name="content" 
                          rows="12"
                          class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary resize-none"
                          placeholder="Write your topic content here..."
                          required><?= htmlspecialchars($topic['content']) ?></textarea>
                <p class="mt-1 text-sm text-gray-500">You can use basic formatting. Be clear and helpful!</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <div class="text-sm text-gray-500">
                    <span class="font-medium">Author:</span> <?= htmlspecialchars($topic['author_name']) ?>
                    <span class="mx-2">•</span>
                    <span>Created: <?= date('M j, Y', strtotime($topic['created_at'])) ?></span>
                </div>
                <div class="flex space-x-3">
                    <a href="<?= \App\Helpers\Url::appUrl() ?>/forum/topic/<?= htmlspecialchars($topic['slug']) ?>" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-secondary text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Update Topic
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Auto-resize textarea
document.getElementById('content').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
});

// Set initial height
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('content');
    textarea.style.height = textarea.scrollHeight + 'px';
});
</script>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the layout
require __DIR__ . '/../layouts/admin.php';
?> 