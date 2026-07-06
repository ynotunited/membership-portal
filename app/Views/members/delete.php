<?php
$title = 'Delete Member';
$pageTitle = 'Delete Member';
$activePage = 'members';
ob_start();
?>

<div class="max-w-xl mx-auto mt-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="ri-alarm-warning-line text-3xl text-red-600"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Delete Member</h2>
            <p class="text-gray-500 mt-2">This action cannot be undone.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6 border border-red-200 flex items-center">
                <i class="ri-error-warning-fill mr-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <div class="mt-6 text-center">
                <a href="<?= \App\Helpers\Url::appUrl() ?>/members"
                    class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors">Return
                    to Members List</a>
            </div>
        <?php elseif (!empty($success)): ?>
            <div class="bg-green-50 text-green-600 p-4 rounded-lg mb-6 border border-green-200 flex items-center">
                <i class="ri-checkbox-circle-fill mr-2"></i>
                <?= htmlspecialchars($success) ?>
            </div>
            <div class="mt-6 text-center">
                <a href="<?= \App\Helpers\Url::appUrl() ?>/members"
                    class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors">Return
                    to Members List</a>
            </div>
        <?php elseif ($id): ?>
            <form method="post" class="mt-6">
                <div class="bg-yellow-50 text-yellow-800 p-4 rounded-lg mb-6 text-sm border border-yellow-200">
                    Are you sure you want to delete this member? All associated records will be permanently removed.
                </div>

                <div class="flex gap-4">
                    <a href="<?= \App\Helpers\Url::appUrl() ?>/members"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-center hover:bg-gray-50 font-medium transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition-colors flex items-center justify-center">
                        <i class="ri-delete-bin-line mr-2"></i> Delete Member
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="mt-6 text-center">
                <a href="<?= \App\Helpers\Url::appUrl() ?>/members"
                    class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors">Return
                    to Members List</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
// Include the admin layout which handles the sidebar and header correctly
// Path: app/Views/members/../../Views/layouts/admin.php -> simplified: app/Views/layouts/admin.php
include __DIR__ . '/../layouts/admin.php';
?>