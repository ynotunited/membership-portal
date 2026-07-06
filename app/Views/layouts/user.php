<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? $title ?? 'Member Dashboard' ?> - GAFCONL</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= \App\Helpers\Url::appUrl() ?>/uploads/gafconl-favicon.png">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#408100', // Major Green
                        secondary: '#BB1F1F', // Red
                        tertiary: '#02037B', // Blue
                        'primary-light': '#86D400', // Lighter Green
                        'primary-dark': '#2d5c00'  // Darker Green
                    },
                    fontFamily: {
                        'sans': ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif']
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Remix Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">

    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .sidebar-nav-item {
            transition: all 0.2s ease;
        }

        .sidebar-nav-item:hover {
            background-color: rgba(255, 2, 49, 0.1);
            transform: translateX(2px);
        }

        .sidebar-nav-item.active {
            background-color: rgba(64, 129, 0, 0.15);
            border-right: 3px solid #408100;
        }

        /* Ensure proper icon display */
        [class^="ri-"]::before {
            font-family: 'remixicon' !important;
        }

        /* Dropdown animations */
        .pl-8 {
            transition: all 0.3s ease;
        }

        .rotate-180 {
            transform: rotate(180deg);
        }

        /* Ensure proper layout */
        .flex.h-screen {
            min-height: 100vh;
        }

        .flex-1 {
            flex: 1 1 0%;
        }

        .overflow-auto {
            overflow: auto;
        }

        /* Fix any potential rendering issues */
        * {
            box-sizing: border-box;
        }

        /* Ensure sidebar stays in place */
        aside {
            position: relative;
            z-index: 10;
        }

        /* Dropdown visibility fixes */
        .hidden {
            display: none !important;
        }

        /* Search Modal Animations */
        #searchModal {
            transition: opacity 0.3s ease;
        }

        #searchModal.hidden {
            opacity: 0;
            pointer-events: none;
        }

        #searchModal:not(.hidden) {
            opacity: 1;
        }

        #searchModal>div {
            transition: transform 0.3s ease;
        }

        #searchModal.hidden>div {
            transform: scale(0.95);
        }

        #searchModal:not(.hidden)>div {
            transform: scale(1);
        }

        /* Loading animation */
        .animate-spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }
    </style>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= \App\Helpers\Url::appUrl() ?>/css/admin.css?v=<?= time() ?>">
    <?php if (!empty($_ENV['GA_MEASUREMENT_ID'])): ?>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($_ENV['GA_MEASUREMENT_ID']) ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '<?= htmlspecialchars($_ENV['GA_MEASUREMENT_ID']) ?>');
    </script>
    <?php endif; ?>
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="flex h-screen">
        <?php include __DIR__ . '/../partials/user_sidebar.php'; ?>
        <main class="flex-1 overflow-auto">
            <div class="p-8">
                <?php include __DIR__ . '/../partials/user_header.php'; ?>
                <?= $content ?>
            </div>
        </main>
    </div>

    <!-- Include Chat Widget -->
    <?php require __DIR__ . '/../partials/chat_widget.php'; ?>

    <!-- Dues Payment Popup -->
    <?php if (isset($user) && ($user['annual_dues_status'] ?? 'unpaid') === 'unpaid'): ?>
        <div id="duesPopup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            style="display: none;">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                                <i class="ri-alert-line text-red-600 text-xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Annual Dues Payment Required</h3>
                        </div>
                        <button id="closeDuesPopup" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="ri-close-line text-xl"></i>
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="mb-6">
                        <p class="text-gray-600 mb-4">
                            Your annual membership dues are currently unpaid. To maintain full access to all membership
                            benefits and features, please complete your payment.
                        </p>

                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                            <div class="flex items-start">
                                <i class="ri-information-line text-red-600 mr-2 mt-0.5"></i>
                                <div>
                                    <h4 class="text-sm font-medium text-red-800 mb-1">Payment Details</h4>
                                    <ul class="text-sm text-red-700 space-y-1">
                                        <li>• Amount: ₦12,000.00</li>
                                        <li>• Secure online payment available</li>
                                        <li>• Instant membership activation</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="<?= \App\Helpers\Url::appUrl() ?>/member/dues/pay"
                            class="flex-1 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors font-medium text-center">
                            <i class="ri-secure-payment-line mr-2"></i>Pay Now
                        </a>
                        <button id="remindLaterBtn"
                            class="flex-1 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                            Remind Me Later
                        </button>
                    </div>

                    <!-- Don't show again option -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <label class="flex items-center text-sm text-gray-600">
                            <input type="checkbox" id="dontShowAgain" class="mr-2 rounded border-gray-300">
                            Don't show this reminder for 24 hours
                        </label>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- JavaScript -->
    <script src="https://unpkg.com/echarts@5.5.0/dist/echarts.min.js"
        onerror="console.warn('ECharts failed to load from CDN')"></script>
    <script>
        // Pass PHP data to JavaScript
        const revenueChartData = <?= json_encode($revenue_chart_data ?? ['labels' => [], 'dues' => [], 'shares' => []]) ?>;
        const membershipTypeData = <?= json_encode($membership_type_data ?? []) ?>;
        const geoDistributionData = <?= json_encode($geo_distribution ?? []) ?>;

        // Global test function for search modal
        window.testSearchModal = function () {
            console.log('Test search modal function called');
            const searchModal = document.getElementById('searchModal');
            const modalSearchInput = document.getElementById('modalSearchInput');

            if (searchModal) {
                console.log('Search modal found, showing...');
                searchModal.classList.remove('hidden');
                searchModal.style.display = 'block';
                setTimeout(() => {
                    if (modalSearchInput) {
                        modalSearchInput.focus();
                        console.log('Search input focused');
                    }
                }, 100);
            } else {
                console.log('Search modal not found');
            }
        };

        // Global function to open search modal
        window.openSearchModal = function () {
            console.log('Open search modal function called');
            const searchModal = document.getElementById('searchModal');
            const modalSearchInput = document.getElementById('modalSearchInput');
            const modalSearchResults = document.getElementById('modalSearchResults');

            if (searchModal) {
                console.log('Search modal found, showing...');
                searchModal.classList.remove('hidden');
                searchModal.style.display = 'block';

                // Set up search functionality when modal opens
                if (modalSearchInput && modalSearchResults) {
                    console.log('Setting up search functionality');
                    let searchTimeout;

                    modalSearchInput.addEventListener('input', function () {
                        clearTimeout(searchTimeout);
                        const query = this.value.trim();

                        if (query.length < 2) {
                            modalSearchResults.innerHTML = '<div class="p-4 text-center text-gray-500">Type at least 2 characters to search</div>';
                            return;
                        }

                        searchTimeout = setTimeout(() => {
                            modalSearchResults.innerHTML = '<div class="p-4 text-center"><div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mx-auto"></div><p class="mt-2 text-sm text-gray-500">Searching...</p></div>';

                            fetch('<?= \App\Helpers\Url::appUrl() ?>/search', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({ query: query })
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success && data.results.length > 0) {
                                        let resultsHtml = '';
                                        data.results.forEach(result => {
                                            resultsHtml += `
                                            <a href="${result.url}" class="block p-4 hover:bg-gray-50 border-b border-gray-100 last:border-b-0">
                                                <div class="flex items-center">
                                                    <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center mr-3">
                                                        <i class="ri-${result.icon} text-secondary"></i>
                                                    </div>
                                                    <div class="flex-1">
                                                        <h4 class="font-medium text-gray-900">${result.title}</h4>
                                                        <p class="text-sm text-gray-500">${result.description}</p>
                                                    </div>
                                                    <div class="text-xs text-gray-400">
                                                        ${result.type}
                                                    </div>
                                                </div>
                                            </a>
                                        `;
                                        });
                                        modalSearchResults.innerHTML = resultsHtml;
                                    } else {
                                        modalSearchResults.innerHTML = '<div class="p-4 text-center text-gray-500">No results found</div>';
                                    }
                                })
                                .catch(error => {
                                    console.error('Search error:', error);
                                    modalSearchResults.innerHTML = '<div class="p-4 text-center text-red-500">Error occurred while searching</div>';
                                });
                        }, 300);
                    });

                    // Focus on input when modal opens
                    setTimeout(() => {
                        modalSearchInput.focus();
                    }, 100);
                }
            } else {
                console.log('Search modal not found');
            }
        };

        // Global function to close search modal
        window.closeSearchModal = function () {
            const searchModal = document.getElementById('searchModal');
            if (searchModal) {
                searchModal.classList.add('hidden');
                setTimeout(() => {
                    searchModal.style.display = 'none';
                }, 300);
            }
        };

        // Keyboard shortcuts
        document.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                openSearchModal();
            }
            if (e.key === 'Escape') {
                closeSearchModal();
            }
        });

        // Initialize charts if they exist and ECharts is available
        document.addEventListener('DOMContentLoaded', function () {
            // Check if ECharts is available
            if (typeof echarts === 'undefined') {
                console.warn('ECharts library not available - charts will not be displayed');
                return;
            }

            // Revenue Chart
            const revenueChart = document.getElementById('revenueChart');
            if (revenueChart && revenueChartData.labels.length > 0) {
                const chart = echarts.init(revenueChart);
                const option = {
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'cross',
                            crossStyle: {
                                color: '#999'
                            }
                        }
                    },
                    legend: {
                        data: ['Dues', 'Shares']
                    },
                    xAxis: {
                        type: 'category',
                        data: revenueChartData.labels,
                        axisPointer: {
                            type: 'shadow'
                        }
                    },
                    yAxis: {
                        type: 'value'
                    },
                    series: [
                        {
                            name: 'Dues',
                            type: 'bar',
                            data: revenueChartData.dues,
                            itemStyle: {
                                color: '#3b82f6'
                            }
                        },
                        {
                            name: 'Shares',
                            type: 'bar',
                            data: revenueChartData.shares,
                            itemStyle: {
                                color: '#10b981'
                            }
                        }
                    ]
                };
                chart.setOption(option);
            }

            // Membership Type Chart
            const membershipChart = document.getElementById('membershipChart');
            if (membershipChart && membershipTypeData.length > 0) {
                const chart = echarts.init(membershipChart);
                const option = {
                    tooltip: {
                        trigger: 'item',
                        formatter: '{a} <br/>{b}: {c} ({d}%)'
                    },
                    series: [
                        {
                            name: 'Membership Types',
                            type: 'pie',
                            radius: '50%',
                            data: membershipTypeData.map(item => ({
                                value: item.count,
                                name: item.type
                            }))
                        }
                    ]
                };
                chart.setOption(option);
            }

            // Geographic Distribution Chart
            const geoChart = document.getElementById('geoChart');
            if (geoChart && geoDistributionData.length > 0) {
                const chart = echarts.init(geoChart);
                const option = {
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'shadow'
                        }
                    },
                    xAxis: {
                        type: 'category',
                        data: geoDistributionData.map(item => item.state)
                    },
                    yAxis: {
                        type: 'value'
                    },
                    series: [
                        {
                            name: 'Members',
                            type: 'bar',
                            data: geoDistributionData.map(item => item.count),
                            itemStyle: {
                                color: '#8b5cf6'
                            }
                        }
                    ]
                };
                chart.setOption(option);
            }
        });

        // Export dropdown functionality
        document.addEventListener('DOMContentLoaded', function () {
            const exportBtn = document.getElementById('exportBtn');
            const exportDropdown = document.getElementById('exportDropdown');

            if (exportBtn && exportDropdown) {
                exportBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    exportDropdown.classList.toggle('hidden');
                });

                document.addEventListener('click', function (e) {
                    if (!exportBtn.contains(e.target) && !exportDropdown.contains(e.target)) {
                        exportDropdown.classList.add('hidden');
                    }
                });
            }

            // Notification dropdown functionality
            const notificationBtn = document.getElementById('notificationBtn');
            const notificationDropdown = document.getElementById('notificationDropdown');

            if (notificationBtn && notificationDropdown) {
                notificationBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    notificationDropdown.classList.toggle('hidden');
                });

                document.addEventListener('click', function (e) {
                    if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
                        notificationDropdown.classList.add('hidden');
                    }
                });
            }
        });

        // Update current date and time
        function updateDateTime() {
            const now = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            const dateTimeString = now.toLocaleDateString('en-US', options);
            const dateTimeElement = document.getElementById('current-date-time');
            if (dateTimeElement) {
                dateTimeElement.textContent = dateTimeString;
            }
        }

        // Update time every second
        setInterval(updateDateTime, 1000);
        updateDateTime(); // Initial call

        // Dues Payment Popup Functionality
        document.addEventListener('DOMContentLoaded', function () {
            const duesPopup = document.getElementById('duesPopup');
            const closeDuesPopup = document.getElementById('closeDuesPopup');
            const remindLaterBtn = document.getElementById('remindLaterBtn');
            const dontShowAgain = document.getElementById('dontShowAgain');

            if (duesPopup) {
                // Check if popup should be shown
                const lastDismissed = localStorage.getItem('duesPopupDismissed');
                const dontShowUntil = localStorage.getItem('duesPopupDontShowUntil');
                const now = new Date().getTime();

                let shouldShow = true;

                // Check if "don't show again" is still active (24 hours)
                if (dontShowUntil && now < parseInt(dontShowUntil)) {
                    shouldShow = false;
                }

                // Check if user dismissed recently (show again after 30 minutes)
                if (lastDismissed && (now - parseInt(lastDismissed)) < (30 * 60 * 1000)) {
                    shouldShow = false;
                }

                if (shouldShow) {
                    // Show popup after a short delay
                    setTimeout(() => {
                        duesPopup.style.display = 'flex';
                        setTimeout(() => {
                            duesPopup.querySelector('div').classList.remove('scale-95');
                            duesPopup.querySelector('div').classList.add('scale-100');
                        }, 50);
                    }, 2000); // Show after 2 seconds
                }

                // Close popup function
                function closeDuesPopupFunc() {
                    const popup = duesPopup.querySelector('div');
                    popup.classList.remove('scale-100');
                    popup.classList.add('scale-95');

                    setTimeout(() => {
                        duesPopup.style.display = 'none';
                    }, 300);

                    // Store dismissal time
                    localStorage.setItem('duesPopupDismissed', new Date().getTime().toString());

                    // Check if "don't show again" is checked
                    if (dontShowAgain && dontShowAgain.checked) {
                        const twentyFourHoursFromNow = new Date().getTime() + (24 * 60 * 60 * 1000);
                        localStorage.setItem('duesPopupDontShowUntil', twentyFourHoursFromNow.toString());
                    }
                }

                // Event listeners
                if (closeDuesPopup) {
                    closeDuesPopup.addEventListener('click', closeDuesPopupFunc);
                }

                if (remindLaterBtn) {
                    remindLaterBtn.addEventListener('click', closeDuesPopupFunc);
                }

                // Close on outside click
                duesPopup.addEventListener('click', function (e) {
                    if (e.target === duesPopup) {
                        closeDuesPopupFunc();
                    }
                });

                // Close on Escape key
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && duesPopup.style.display === 'flex') {
                        closeDuesPopupFunc();
                    }
                });
            }
        });
    </script>
</body>

</html>