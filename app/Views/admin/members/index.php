<?php
$title = 'Manage Members';
$pageTitle = 'Manage Members';
$activePage = 'members';
ob_start();
?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Manage Members</h1>
    <p class="mt-2 text-gray-600">View and manage all registered members</p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Members List</h2>
        <div class="flex space-x-3">
            <a href="<?= \App\Helpers\Url::appUrl() ?>/members/export<?= !empty($_GET) ? '?' . http_build_query($_GET) : '' ?>"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                Export
            </a>
            <a href="<?= \App\Helpers\Url::appUrl() ?>/members/add"
                class="inline-flex items-center px-4 py-2 bg-secondary text-white rounded-lg font-medium hover:bg-blue-700">
                Add Member
            </a>
        </div>
    </div>

    <!-- Bulk Actions -->
    <form method="POST" action="<?= \App\Helpers\Url::appUrl() ?>/members/bulk"
        class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bulk Action</label>
                <select name="action" id="bulk-action"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary">
                    <option value="">Select Action</option>
                    <option value="approve">Approve (mark as Paid)</option>
                    <option value="delete">Delete</option>
                    <option value="email">Email</option>
                    <option value="export_csv">Export CSV</option>
                    <option value="export_pdf">Export PDF</option>
                    <option value="export_xlsx">Export Excel</option>
                </select>
            </div>
            <div id="email-subject-wrap" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Subject</label>
                <input type="text" name="email_subject" class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    placeholder="Subject">
            </div>
            <div id="email-message-wrap" class="hidden md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Message</label>
                <textarea name="email_message" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    placeholder="Your message..."></textarea>
            </div>
            <div class="md:col-span-4 flex items-center justify-between">
                <div class="text-sm text-gray-500">Select members below, then choose an action.</div>
                <button type="submit"
                    class="px-4 py-2 bg-primary text-white rounded-md hover:bg-secondary">Apply</button>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const action = document.getElementById('bulk-action');
                const subj = document.getElementById('email-subject-wrap');
                const msg = document.getElementById('email-message-wrap');
                action.addEventListener('change', function () {
                    const isEmail = this.value === 'email';
                    subj.classList.toggle('hidden', !isEmail);
                    msg.classList.toggle('hidden', !isEmail);
                });
            });
        </script>

        <!-- The table with checkboxes is inside this form so selected[] posts -->



        <?php if (empty($members)): ?>
            <div class="text-center py-8 text-gray-500">
                <p>No members found.</p>
            </div>
        <?php else: ?>
            <!-- Results Count -->
            <div class="mb-4 text-sm text-gray-600">
                Showing <?= count($members) ?> of <?= $totalMembers ?? count($members) ?> members
                <?php if (!empty($search) || !empty($status) || !empty($memberType)): ?>
                    (filtered results)
                <?php endif; ?>
            </div>

            <div class="responsive-table-container">
                <table class="responsive-table">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all" class="rounded border-gray-300">
                            </th>
                            <th>Member Info</th>
                            <th>Contact</th>
                            <th>Location</th>
                            <th>Registration</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $member): ?>
                            <tr>
                                <td data-label="Select">
                                    <input type="checkbox" name="selected[]" value="<?= (int) $member['id'] ?>"
                                        class="row-check rounded border-gray-300">
                                </td>
                                <td data-label="Member">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <?php
                                            $photoRaw = $member['photo'] ?? '';
                                            $photo = (!empty($photoRaw) && strtolower($photoRaw) !== 'default.jpg') ? htmlspecialchars($photoRaw) : 'default-user.png';
                                            $photoPath = \App\Helpers\Url::base(true) . '/uploads/member_photos/' . $photo;
                                            ?>
                                            <img class="h-10 w-10 rounded-full object-cover" src="<?= $photoPath ?>"
                                                alt="Profile">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars(($member['title'] ?? '') . ' ' . ($member['firstname'] ?? '') . ' ' . ($member['surname'] ?? '')) ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?= htmlspecialchars($member['membership_number'] ?? 'N/A') ?>
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                <?= htmlspecialchars($member['gender'] ?? 'N/A') ?> •
                                                <?= htmlspecialchars($member['marital_status'] ?? 'N/A') ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Contact">
                                    <div class="text-sm text-gray-900">
                                        <?= htmlspecialchars($member['email'] ?? 'N/A') ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?= htmlspecialchars($member['contact_number'] ?? 'N/A') ?>
                                    </div>
                                    <?php if (!empty($member['whatsapp_number'])): ?>
                                        <div class="text-xs text-gray-400">
                                            WhatsApp: <?= htmlspecialchars($member['whatsapp_number']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Location">
                                    <div class="text-sm text-gray-900">
                                        <?= htmlspecialchars($member['city_town'] ?? 'N/A') ?>,
                                        <?= htmlspecialchars($member['lga'] ?? 'N/A') ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?= htmlspecialchars($member['state_district'] ?? 'N/A') ?>,
                                        <?= htmlspecialchars($member['country'] ?? 'N/A') ?>
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        <?= htmlspecialchars($member['chapter'] ?? 'N/A') ?>
                                    </div>
                                </td>
                                <td data-label="Registration">
                                    <div class="text-sm text-gray-900">
                                        <?= htmlspecialchars($member['registration_status'] ?? 'N/A') ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?= htmlspecialchars($member['member_type'] ?? 'N/A') ?>
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        <?= htmlspecialchars($member['payment_status'] ?? 'N/A') ?>
                                    </div>
                                </td>
                                <td data-label="Actions" class="actions">
                                    <div class="flex space-x-2">
                                        <a href="<?= \App\Helpers\Url::appUrl() ?>/members/edit?id=<?= $member['id'] ?>"
                                            class="text-secondary hover:text-blue-900">Edit</a>
                                        <a href="<?= \App\Helpers\Url::appUrl() ?>/members/profile?id=<?= $member['id'] ?>"
                                            class="text-green-600 hover:text-green-900">View</a>
                                        <a href="<?= \App\Helpers\Url::appUrl() ?>/members/delete?id=<?= $member['id'] ?>"
                                            class="text-red-600 hover:text-red-900">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const master = document.getElementById('select-all');
                        const rows = document.querySelectorAll('.row-check');
                        master.addEventListener('change', function () {
                            rows.forEach(c => c.checked = master.checked);
                        });
                    });
                </script>
            </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if (isset($totalPages) && $totalPages > 1): ?>
            <div class="mt-6 flex items-center justify-between flex-wrap gap-4">
                <div class="text-sm text-gray-700">
                    Showing page <?= $currentPage ?> of <?= $totalPages ?>
                </div>
                <div class="flex flex-wrap gap-2">
                    <?php
                    // Build query parameters for pagination links
                    $queryParams = [];
                    if (!empty($search))
                        $queryParams['search'] = $search;
                    if (!empty($status))
                        $queryParams['status'] = $status;
                    if (!empty($memberType))
                        $queryParams['member_type'] = $memberType;
                    if (!empty($perPage))
                        $queryParams['per_page'] = $perPage;

                    $queryString = !empty($queryParams) ? '&' . http_build_query($queryParams) : '';
                    ?>

                    <?php if ($currentPage > 1): ?>
                        <a href="?page=1<?= $queryString ?>"
                            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            First
                        </a>
                        <a href="?page=<?= $currentPage - 1 ?><?= $queryString ?>"
                            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Previous
                        </a>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);

                    for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                        <a href="?page=<?= $i ?><?= $queryString ?>"
                            class="px-3 py-2 text-sm font-medium <?= $i === $currentPage ? 'text-secondary bg-primary/5 border-primary' : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-50' ?> border rounded-md">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?= $currentPage + 1 ?><?= $queryString ?>"
                            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Next
                        </a>
                        <a href="?page=<?= $totalPages ?><?= $queryString ?>"
                            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Last
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>