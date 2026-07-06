<?php
$title = 'Manage Thrift Savings';
$pageTitle = 'Manage Thrift Savings';
$activePage = 'thrift';
ob_start();
?>

<div class="mb-8">
    <div class="flex items-center">
        <a href="<?= \App\Helpers\Url::appUrl() ?>/dashboard" class="text-secondary hover:text-blue-800">
            <i class="ri-arrow-left-line text-xl"></i>
        </a>
        <h3 class="ml-3 text-lg font-semibold text-gray-900">Thrift Savings</h3>
    </div>

    <div class="mt-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manage Thrift Savings</h1>
            <p class="mt-2 text-gray-600">View and manage all thrift savings contributions</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="<?= \App\Helpers\Url::appUrl() ?>/thrift/export<?= !empty($_GET) ? '?' . http_build_query($_GET) : '' ?>"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700">
                <i class="ri-download-line mr-2"></i> Export
            </a>
            <a href="<?= \App\Helpers\Url::appUrl() ?>/thrift/add"
                class="inline-flex items-center px-4 py-2 bg-secondary text-white rounded-lg font-medium hover:bg-blue-700">
                <i class="ri-add-line mr-2"></i> Add Savings
            </a>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Savings List</h2>

        <!-- Search Form -->
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <div>
                <input type="text" id="search" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                    placeholder="Search by member..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
            </div>
            <button type="submit"
                class="px-4 py-2 bg-secondary text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                Search
            </button>
        </form>
    </div>

    <!-- RESULTS_START -->
    <div id="thrift-results">
        <?php if (empty($savings)): ?>
            <div class="text-center py-12">
                <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                    <i class="ri-safe-2-line text-4xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No savings records found</h3>
            </div>
        <?php else: ?>
            <!-- Results Count -->
            <div class="mb-4 text-sm text-gray-600">
                Showing
                <?= count($savings) ?> results
                <?php if (!empty($search)): ?>
                    (filtered)
                <?php endif; ?>
            </div>

            <div class="responsive-table-container">
                <table class="responsive-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Member</th>
                            <th>Membership No.</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($savings as $saving): ?>
                            <tr>
                                <td data-label="Date">
                                    <?= date('M d, Y', strtotime($saving['payment_date'])) ?>
                                </td>
                                <td data-label="Member">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                                                <span class="text-sm font-medium text-secondary">
                                                    <?= strtoupper(substr(($saving['firstname'] ?? ''), 0, 1) . substr(($saving['surname'] ?? ''), 0, 1)) ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars(($saving['firstname'] ?? '') . ' ' . ($saving['surname'] ?? '')) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Membership No.">
                                    <?= htmlspecialchars($saving['membership_number'] ?? 'N/A') ?>
                                </td>
                                <td data-label="Amount">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        ₦
                                        <?= number_format($saving['amount'] ?? 0, 2) ?>
                                    </span>
                                </td>
                                <td data-label="Actions" class="actions">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="<?= \App\Helpers\Url::appUrl() ?>/thrift/edit?id=<?= $saving['id'] ?>"
                                            class="inline-flex items-center px-3 py-1 bg-yellow-100 text-yellow-800 rounded-md hover:bg-yellow-200 transition-colors text-sm">
                                            <i class="ri-edit-line mr-1"></i> Edit
                                        </a>
                                        <a href="<?= \App\Helpers\Url::appUrl() ?>/thrift/delete?id=<?= $saving['id'] ?>"
                                            class="inline-flex items-center px-3 py-1 bg-red-100 text-red-800 rounded-md hover:bg-red-200 transition-colors text-sm"
                                            onclick="return confirm('Are you sure you want to delete this savings record?')">
                                            <i class="ri-delete-bin-line mr-1"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if (isset($totalPages) && $totalPages > 1): ?>
                <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-gray-700">
                        Showing page
                        <?= $currentPage ?> of
                        <?= $totalPages ?>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <?php
                        $queryParams = [];
                        if (!empty($search))
                            $queryParams['search'] = $search;
                        $queryParams['per_page'] = $perPage;
                        $queryString = !empty($queryParams) ? '&' . http_build_query($queryParams) : '';
                        ?>

                        <?php if ($currentPage > 1): ?>
                            <a href="?page=<?= $currentPage - 1 ?><?= $queryString ?>"
                                class="pagination-link px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                                data-page="<?= $currentPage - 1 ?>">
                                Previous
                            </a>
                        <?php endif; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?page=<?= $currentPage + 1 ?><?= $queryString ?>"
                                class="pagination-link px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                                data-page="<?= $currentPage + 1 ?>">
                                Next
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <!-- RESULTS_END -->

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('search');
            const resultsContainer = document.getElementById('thrift-results');
            const exportLink = document.querySelector('a[href*="/thrift/export"]');
            let debounceTimer;

            // Handle search input
            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetchResults(1, this.value);
                }, 500);
            });

            // Handle pagination clicks
            resultsContainer.addEventListener('click', function (e) {
                if (e.target.closest('.pagination-link')) {
                    e.preventDefault();
                    const link = e.target.closest('.pagination-link');
                    const page = link.dataset.page;
                    const searchTerm = searchInput.value;
                    fetchResults(page, searchTerm);
                }
            });

            function fetchResults(page, search) {
                const url = new URL(window.location.href);
                url.searchParams.set('page', page);
                if (search) {
                    url.searchParams.set('search', search);
                } else {
                    url.searchParams.delete('search');
                }

                // Update URL without reload
                window.history.pushState({}, '', url);

                // Update export link
                if (exportLink) {
                    const exportUrl = new URL(exportLink.href);
                    if (search) {
                        exportUrl.searchParams.set('search', search);
                    } else {
                        exportUrl.searchParams.delete('search');
                    }
                    exportLink.href = exportUrl.toString();
                }

                // Show loading state
                resultsContainer.style.opacity = '0.5';

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.text())
                    .then(html => {
                        resultsContainer.innerHTML = html;
                        resultsContainer.style.opacity = '1';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        resultsContainer.style.opacity = '1';
                    });
            }
        });
    </script>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>