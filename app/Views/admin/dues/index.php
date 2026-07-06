<?php
$title = 'Manage Dues';
$pageTitle = 'Manage Dues';
$activePage = 'dues';
ob_start();
?>

<div class="mb-8">
    <div class="flex items-center">
        <a href="<?= \App\Helpers\Url::appUrl() ?>/dues" class="text-secondary hover:text-blue-800">
            <i class="ri-arrow-left-line text-xl"></i>
        </a>
        <h3 class="ml-3 text-lg font-semibold text-gray-900">Dues Payments</h3>
    </div>

    <div class="mt-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manage Dues</h1>
            <p class="mt-2 text-gray-600">View and manage all dues payments</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="<?= \App\Helpers\Url::appUrl() ?>/dues/export<?= !empty($_GET) ? '?' . http_build_query($_GET) : '' ?>"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700">
                <i class="ri-download-line mr-2"></i> Export
            </a>
            <a href="<?= \App\Helpers\Url::appUrl() ?>/dues/add"
                class="inline-flex items-center px-4 py-2 bg-secondary text-white rounded-lg font-medium hover:bg-blue-700">
                <i class="ri-add-line mr-2"></i> Add Dues Payment
            </a>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Dues Payments List</h2>

        <!-- Search and Filter Form -->
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <div>
                <input type="text" id="search" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                    placeholder="Search by member name..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
            </div>
            <div>
                <select id="status" name="status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    <option value="">All Status</option>
                    <option value="paid" <?= ($_GET['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="failed" <?= ($_GET['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                </select>
            </div>
            <button type="submit"
                class="px-4 py-2 bg-secondary text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                Search
            </button>
        </form>
    </div>

    <!-- Results Container -->
    <div id="dues-results">
        <!-- RESULTS_START -->
        <?php if (empty($dues)): ?>
            <div class="text-center py-12">
                <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                    <i class="ri-wallet-3-line text-4xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No dues payments found</h3>
                <p class="text-gray-500 mb-6">Get started by adding a new dues payment.</p>
                <a href="<?= \App\Helpers\Url::appUrl() ?>/dues/add"
                    class="inline-flex items-center px-4 py-2 bg-secondary text-white rounded-md hover:bg-blue-700">
                    <i class="ri-add-line mr-2"></i> Add Dues Payment
                </a>
            </div>
        <?php else: ?>
            <!-- Results Count -->
            <div class="mb-4 text-sm text-gray-600">
                Showing <?= count($dues) ?> of <?= $totalDues ?? count($dues) ?> dues payments
                <?php if (!empty($search) || !empty($status)): ?>
                    (filtered results)
                <?php endif; ?>
            </div>

            <div class="responsive-table-container">
                <table class="responsive-table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Chapter</th>
                            <th>Amount</th>
                            <th>Year</th>
                            <th>Payment Method</th>
                            <th>Payment Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dues as $due): ?>
                            <tr>
                                <td data-label="Member">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                                                <span class="text-sm font-medium text-secondary">
                                                    <?= strtoupper(substr(($due['firstname'] ?? ''), 0, 1) . substr(($due['surname'] ?? ''), 0, 1)) ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars(($due['firstname'] ?? '') . ' ' . ($due['surname'] ?? '')) ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?= htmlspecialchars($due['membership_number'] ?? 'N/A') ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Chapter">
                                    <?= htmlspecialchars($due['chapter'] ?? 'N/A') ?>
                                </td>
                                <td data-label="Amount">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        ₦<?= number_format($due['amount'] ?? 0, 2) ?>
                                    </span>
                                </td>
                                <td data-label="Year">
                                    <?= htmlspecialchars($due['year'] ?? 'N/A') ?>
                                </td>
                                <td data-label="Payment Method">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= ($due['payment_method'] ?? '') === 'paystack' ? 'bg-primary/10 text-blue-800' : 'bg-gray-100 text-gray-800' ?>">
                                        <?= ucfirst(htmlspecialchars($due['payment_method'] ?? 'manual')) ?>
                                    </span>
                                </td>
                                <td data-label="Payment Date">
                                    <?= date('M d, Y H:i', strtotime($due['paid_at'] ?? $due['payment_date'] ?? '')) ?>
                                </td>
                                <td data-label="Actions" class="actions">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="<?= \App\Helpers\Url::appUrl() ?>/dues/edit?id=<?= $due['id'] ?>"
                                            class="inline-flex items-center px-3 py-1 bg-yellow-100 text-yellow-800 rounded-md hover:bg-yellow-200 transition-colors text-sm">
                                            <i class="ri-edit-line mr-1"></i> Edit
                                        </a>
                                        <a href="<?= \App\Helpers\Url::appUrl() ?>/dues/delete?id=<?= $due['id'] ?>"
                                            class="inline-flex items-center px-3 py-1 bg-red-100 text-red-800 rounded-md hover:bg-red-200 transition-colors text-sm"
                                            onclick="return confirm('Are you sure you want to delete this dues payment?')">
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
            <!-- RESULTS_END -->
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search');
        const statusSelect = document.getElementById('status');
        const resultsContainer = document.getElementById('dues-results');
        let debounceTimer;

        function updateResults() {
            const search = searchInput.value;
            const status = statusSelect.value;
            const url = new URL(window.location.href);
            url.searchParams.set('search', search);
            url.searchParams.set('status', status);
            url.searchParams.set('page', 1); // Reset to page 1 on new search

            // Update URL without reload
            window.history.pushState({}, '', url);

            // Update Export link
            const exportLink = document.querySelector('a[href*="/dues/export"]');
            if (exportLink) {
                exportLink.href = '<?= \App\Helpers\Url::appUrl() ?>/dues/export?' + url.searchParams.toString();
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

                    // Re-attach pagination clicks if they are inside resultsContainer
                    attachPaginationLinks();
                })
                .catch(error => {
                    console.error('Error fetching dues:', error);
                    resultsContainer.style.opacity = '1';
                });
        }

        function attachPaginationLinks() {
            const links = resultsContainer.querySelectorAll('a[href*="page="]');
            links.forEach(link => {
                link.addEventListener('click', function (e) {
                    if (this.getAttribute('href').startsWith('?')) {
                        e.preventDefault();
                        const url = new URL(this.href, window.location.origin + window.location.pathname);
                        fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                            .then(response => response.text())
                            .then(html => {
                                resultsContainer.innerHTML = html;
                                window.history.pushState({}, '', url);
                                attachPaginationLinks();
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                            });
                    }
                });
            });
        }

        const searchForm = document.querySelector('form');
        if (searchForm) {
            searchForm.addEventListener('submit', function (e) {
                e.preventDefault();
                updateResults();
            });
        }

        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(updateResults, 500);
        });

        statusSelect.addEventListener('change', updateResults);

        // Initial attachment for page load results
        attachPaginationLinks();
    });
</script>

<?php
// Capture the content and include the layout
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>