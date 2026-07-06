<?php
$title = 'Manage Users';
$pageTitle = 'Manage Users';
$activePage = 'users';
ob_start();
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Manage Users</h1>
        <?php if (\App\Helpers\PermissionHelper::hasPermission('users.create')): ?>
        <a href="<?= \App\Helpers\Url::appUrl() ?>/users/add" 
           class="bg-secondary hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Add New User
        </a>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['flash_messages'])): ?>
        <?php foreach ($_SESSION['flash_messages'] as $type => $message): ?>
            <div class="mb-4 p-4 rounded <?= $type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endforeach; ?>
        <?php unset($_SESSION['flash_messages']); ?>
    <?php endif; ?>

    <!-- User Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-primary/10 text-secondary">
                    <i class="ri-user-line text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Users</p>
                    <p class="text-2xl font-semibold text-gray-900"><?= count($users) ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="ri-shield-user-line text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Admins</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        <?= count(array_filter($users, function($user) { return $user['role_name'] === 'admin'; })) ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="ri-file-list-line text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Secretaries</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        <?= count(array_filter($users, function($user) { return $user['role_name'] === 'secretary'; })) ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="ri-money-dollar-circle-line text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Financial</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        <?= count(array_filter($users, function($user) { 
                            return in_array($user['role_name'], ['financial_secretary', 'treasurer']); 
                        })) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($users as $user): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                    <span class="text-sm font-medium text-gray-700">
                                        <?= strtoupper(substr($user['email'] ?? 'U', 0, 1)) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($user['email']) ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    ID: <?= $user['id'] ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">
                            <?= htmlspecialchars($user['email']) ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <?php
                            $roleColors = [
                                'admin' => 'bg-red-100 text-red-800',
                                'secretary' => 'bg-primary/10 text-blue-800',
                                'financial_secretary' => 'bg-green-100 text-green-800',
                                'treasurer' => 'bg-purple-100 text-purple-800'
                            ];
                            $roleColor = $roleColors[$user['role_name']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $roleColor ?>">
                                <?= ucfirst(str_replace('_', ' ', $user['role_name'] ?? 'Unknown')) ?>
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= date('M j, Y', strtotime($user['created_at'] ?? 'now')) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <?php if (\App\Helpers\PermissionHelper::hasPermission('users.edit')): ?>
                            <a href="<?= \App\Helpers\Url::appUrl() ?>/users/edit?id=<?= $user['id'] ?>" 
                               class="text-indigo-600 hover:text-indigo-900">Edit</a>
                            <?php endif; ?>
                            
                            <?php if (\App\Helpers\PermissionHelper::hasPermission('users.password_reset')): ?>
                            <a href="<?= \App\Helpers\Url::appUrl() ?>/users/reset-password?id=<?= $user['id'] ?>" 
                               class="text-yellow-600 hover:text-yellow-900"
                               onclick="return confirm('Are you sure you want to reset this user\'s password?')">Reset Password</a>
                            <?php endif; ?>
                            
                            <?php if (\App\Helpers\PermissionHelper::hasPermission('users.delete') && $user['id'] != $_SESSION['user_id']): ?>
                            <a href="<?= \App\Helpers\Url::appUrl() ?>/users/delete?id=<?= $user['id'] ?>" 
                               class="text-red-600 hover:text-red-900"
                               onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?> 