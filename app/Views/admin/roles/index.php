<?php
$title = 'Manage Roles';
$pageTitle = 'Manage Roles';
$activePage = 'roles';
ob_start();
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Manage Roles</h1>
        <?php if (\App\Helpers\PermissionHelper::hasPermission('roles.create')): ?>
        <a href="<?= \App\Helpers\Url::appUrl() ?>/roles/add" 
           class="bg-secondary hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Add New Role
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

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($roles as $role): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            <?= htmlspecialchars($role['name']) ?>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">
                            <?= htmlspecialchars($role['description']) ?>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">
                            <?php 
                            $roleModel = new \App\Models\RoleModel();
                            $permissions = $roleModel->getRolePermissions($role['id']);
                            $permissionNames = array_column($permissions, 'name');
                            echo count($permissionNames) . ' permissions';
                            ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <?php if (\App\Helpers\PermissionHelper::hasPermission('roles.edit')): ?>
                            <a href="<?= \App\Helpers\Url::appUrl() ?>/roles/edit?id=<?= $role['id'] ?>" 
                               class="text-indigo-600 hover:text-indigo-900">Edit</a>
                            <?php endif; ?>
                            
                            <?php if (\App\Helpers\PermissionHelper::hasPermission('roles.delete') && !in_array($role['name'], ['admin', 'secretary', 'financial_secretary', 'treasurer'])): ?>
                            <a href="<?= \App\Helpers\Url::appUrl() ?>/roles/delete?id=<?= $role['id'] ?>" 
                               class="text-red-600 hover:text-red-900"
                               onclick="return confirm('Are you sure you want to delete this role?')">Delete</a>
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