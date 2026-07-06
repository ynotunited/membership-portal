<?php
$title = 'Add New Role';
$pageTitle = 'Add New Role';
$activePage = 'roles';
ob_start();
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Add New Role</h1>
            <a href="<?= \App\Helpers\Url::appUrl() ?>/roles" 
               class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Roles
            </a>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
            <form method="POST" action="<?= \App\Helpers\Url::appUrl() ?>/roles/add">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Role Name</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                               placeholder="Enter role name">
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                  placeholder="Enter role description"></textarea>
                    </div>
                </div>

                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Permissions</h3>
                    
                    <?php
                    $permissionsByModule = [];
                    foreach ($permissions as $permission) {
                        $permissionsByModule[$permission['module']][] = $permission;
                    }
                    ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($permissionsByModule as $module => $modulePermissions): ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-3 capitalize"><?= str_replace('_', ' ', $module) ?></h4>
                            
                            <?php foreach ($modulePermissions as $permission): ?>
                            <div class="flex items-center mb-2">
                                <input type="checkbox" 
                                       id="permission_<?= $permission['id'] ?>" 
                                       name="permissions[]" 
                                       value="<?= $permission['id'] ?>"
                                       class="h-4 w-4 text-secondary focus:ring-primary border-gray-300 rounded">
                                <label for="permission_<?= $permission['id'] ?>" 
                                       class="ml-2 text-sm text-gray-700">
                                    <?= ucfirst($permission['action']) ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-4">
                    <a href="<?= \App\Helpers\Url::appUrl() ?>/roles" 
                       class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="bg-secondary hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Create Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add select all functionality for each module
    const moduleDivs = document.querySelectorAll('.border.border-gray-200');
    
    moduleDivs.forEach(moduleDiv => {
        const checkboxes = moduleDiv.querySelectorAll('input[type="checkbox"]');
        const moduleTitle = moduleDiv.querySelector('h4');
        
        // Add select all checkbox
        const selectAllDiv = document.createElement('div');
        selectAllDiv.className = 'flex items-center mb-3 pb-2 border-b border-gray-200';
        selectAllDiv.innerHTML = `
            <input type="checkbox" class="select-all h-4 w-4 text-secondary focus:ring-primary border-gray-300 rounded">
            <label class="ml-2 text-sm font-medium text-gray-900">Select All</label>
        `;
        
        moduleDiv.insertBefore(selectAllDiv, moduleTitle.nextSibling);
        
        const selectAllCheckbox = selectAllDiv.querySelector('.select-all');
        
        // Handle select all functionality
        selectAllCheckbox.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        // Update select all when individual checkboxes change
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = anyChecked && !allChecked;
            });
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?> 