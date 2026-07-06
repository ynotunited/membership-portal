<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? $title ?? 'Admin Dashboard' ?> - GAFCONL</title>

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

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* Tom Select Custom Styling */
        .ts-control {
            border-radius: 0.5rem !important;
            padding: 0.5rem 0.75rem !important;
            border-color: #d1d5db !important;
            font-size: 0.875rem !important;
            line-height: 1.25rem !important;
        }
        
        .ts-wrapper.focus .ts-control {
            box-shadow: 0 0 0 2px #fff, 0 0 0 4px #408100 !important;
            border-color: #408100 !important;
        }
        
        .ts-dropdown {
            border-radius: 0.5rem !important;
            margin-top: 0.25rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
            border-color: #e5e7eb !important;
        }
        
        .ts-dropdown .active {
            background-color: #408100 !important;
            color: white !important;
        }
        
        .ts-dropdown .option:hover {
            background-color: rgba(64, 129, 0, 0.1) !important;
        }
    </style>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= \App\Helpers\Url::appUrl() ?>/css/admin.css?v=<?= time() ?>">

    <!-- Tom Select -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
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
        <?php include __DIR__ . '/../partials/admin_sidebar.php'; ?>
        <main class="flex-1 overflow-auto w-full">
            <div class="p-4 lg:p-8">
                <?php include __DIR__ . '/../partials/admin_header.php'; ?>
                <?= $content ?>
            </div>
        </main>
    </div>

    <!-- Include Chat Widget -->
    <?php require __DIR__ . '/../partials/chat_widget.php'; ?>

    <!-- JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
    <script>
        // Pass PHP data to JavaScript
        const APP_URL = "<?= \App\Helpers\Url::appUrl() ?>";
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

                // Initialize search results with default message
                if (modalSearchResults) {
                    modalSearchResults.innerHTML = `
                        <div class="text-center text-gray-500 py-8">
                            <i class="ri-search-line text-4xl mb-4"></i>
                            <p>Start typing to search...</p>
                        </div>
                    `;
                }

                // Set up search functionality when modal opens
                if (modalSearchInput && modalSearchResults) {
                    console.log('Setting up search functionality');
                    let searchTimeout;

                    modalSearchInput.addEventListener('input', function () {
                        console.log('Search input event triggered, query:', this.value);
                        clearTimeout(searchTimeout);
                        const query = this.value.trim();

                        if (!query) {
                            console.log('Empty query, showing default message');
                            modalSearchResults.innerHTML = `
                                <div class="text-center text-gray-500 py-8">
                                    <i class="ri-search-line text-4xl mb-4"></i>
                                    <p>Start typing to search...</p>
                                </div>
                            `;
                            return;
                        }

                        console.log('Starting search for:', query);
                        searchTimeout = setTimeout(() => {
                            // Show loading state
                            console.log('Showing loading state');
                            modalSearchResults.innerHTML = `
                                <div class="text-center text-gray-500 py-8">
                                    <i class="ri-loader-4-line text-4xl mb-4 animate-spin"></i>
                                    <p>Searching...</p>
                                </div>
                            `;

                            console.log('Making fetch request to:', `${APP_URL}/search?q=${encodeURIComponent(query)}`);

                            const searchUrl = `${APP_URL}/search?q=${encodeURIComponent(query)}`;

                            console.log('Using search URL:', searchUrl);
                            fetch(searchUrl)
                                .then(res => {
                                    console.log('Fetch response status:', res.status);
                                    if (!res.ok) {
                                        throw new Error(`HTTP error! status: ${res.status}`);
                                    }
                                    return res;
                                })
                                .then(res => {
                                    if (!res.ok) {
                                        throw new Error(`HTTP error! status: ${res.status}`);
                                    }
                                    return res.json();
                                })
                                .then(data => {
                                    console.log('Search results received:', data);
                                    if (data && data.results && data.results.length > 0) {
                                        console.log('Displaying', data.results.length, 'results');
                                        modalSearchResults.innerHTML = data.results.map(item => `
                                            <a href="${item.url}" class="block p-4 hover:bg-gray-50 border-b border-gray-100 last:border-b-0 transition-colors">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex-1">
                                                        <div class="font-medium text-gray-900">${item.label}</div>
                                                        <div class="text-sm text-gray-500 mt-1">${item.type}</div>
                                                    </div>
                                                    <div class="text-gray-400">
                                                        <i class="ri-arrow-right-s-line"></i>
                                                    </div>
                                                </div>
                                            </a>
                                        `).join('');
                                    } else {
                                        console.log('No results found');
                                        modalSearchResults.innerHTML = `
                                            <div class="text-center text-gray-500 py-8">
                                                <i class="ri-search-line text-4xl mb-4"></i>
                                                <p>No results found for "${query}"</p>
                                                <p class="text-sm mt-2">Try different keywords</p>
                                            </div>
                                        `;
                                    }
                                })
                                .catch(error => {
                                    console.error('Search error:', error);
                                    modalSearchResults.innerHTML = `
                                        <div class="text-center text-red-500 py-8">
                                            <i class="ri-error-warning-line text-4xl mb-4"></i>
                                            <p>Error occurred while searching</p>
                                            <p class="text-sm mt-2">Please try again</p>
                                            <p class="text-xs mt-1">${error.message}</p>
                                        </div>
                                    `;
                                });
                        }, 300);
                    });

                    // Handle Enter key to navigate to first result
                    modalSearchInput.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter') {
                            const firstResult = modalSearchResults.querySelector('a');
                            if (firstResult) {
                                window.location.href = firstResult.href;
                            }
                        }
                    });
                } else {
                    console.log('Search elements not found:', {
                        modalSearchInput: !!modalSearchInput,
                        modalSearchResults: !!modalSearchResults
                    });
                }

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

        // Global function to close search modal
        window.closeSearchModal = function () {
            console.log('Close search modal function called');
            const searchModal = document.getElementById('searchModal');
            const modalSearchInput = document.getElementById('modalSearchInput');
            const modalSearchResults = document.getElementById('modalSearchResults');

            if (searchModal) {
                searchModal.classList.add('hidden');
                searchModal.style.display = 'none';
                if (modalSearchInput) {
                    modalSearchInput.value = '';
                }
                if (modalSearchResults) {
                    modalSearchResults.innerHTML = `
                        <div class="text-center text-gray-500 py-8">
                            <i class="ri-search-line text-4xl mb-4"></i>
                            <p>Start typing to search...</p>
                        </div>
                    `;
                }
            }
        };

        // Debug search elements on page load
        document.addEventListener('DOMContentLoaded', function () {
            console.log('=== SEARCH ELEMENTS DEBUG ===');
            console.log('Search button:', document.getElementById('searchBtn'));
            console.log('Search modal:', document.getElementById('searchModal'));
            console.log('Modal search input:', document.getElementById('modalSearchInput'));
            console.log('Modal search results:', document.getElementById('modalSearchResults'));
            console.log('Close search modal:', document.getElementById('closeSearchModal'));
            console.log('=== END SEARCH DEBUG ===');
        });
    </script>
    <script src="<?= \App\Helpers\Url::appUrl() ?>/js/admin.js"></script>

    <!-- Additional JavaScript for sidebar functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            console.log('Admin layout JavaScript loaded');

            // Test dropdown functionality
            console.log('Testing dropdown elements...');
            const testDropdowns = ['membershipTypesDropdown', 'membersDropdown', 'listsDropdown', 'reportsDropdown'];
            testDropdowns.forEach(id => {
                const element = document.getElementById(id);
                console.log(`Dropdown ${id}:`, element ? 'Found' : 'Not found');
                if (element) {
                    console.log(`- Classes:`, element.className);
                    console.log(`- Visibility:`, window.getComputedStyle(element).visibility);
                    console.log(`- Opacity:`, window.getComputedStyle(element).opacity);
                    console.log(`- MaxHeight:`, window.getComputedStyle(element).maxHeight);
                }
            });

            // Add a test function to manually toggle dropdowns
            window.testDropdown = function (dropdownId) {
                const dropdown = document.getElementById(dropdownId);
                if (dropdown) {
                    const isHidden = dropdown.classList.contains('hidden');
                    console.log(`Manually testing ${dropdownId}, currently hidden:`, isHidden);

                    if (isHidden) {
                        dropdown.classList.remove('hidden');
                        dropdown.style.visibility = 'visible';
                        dropdown.style.opacity = '1';
                        dropdown.style.maxHeight = '200px';
                        console.log(`${dropdownId} manually shown`);
                    } else {
                        dropdown.classList.add('hidden');
                        dropdown.style.visibility = 'hidden';
                        dropdown.style.opacity = '0';
                        dropdown.style.maxHeight = '0';
                        console.log(`${dropdownId} manually hidden`);
                    }
                } else {
                    console.log(`Dropdown ${dropdownId} not found`);
                }
            };

            // Sidebar dropdown functionality
            const dropdownButtons = [
                'membershipTypesDropdownBtn',
                'membersDropdownBtn',
                'listsDropdownBtn',
                'eventsDropdownBtn',
                'reportsDropdownBtn',
                'rolesDropdownBtn'
            ];

            console.log('Setting up dropdown functionality for buttons:', dropdownButtons);

            dropdownButtons.forEach(buttonId => {
                const button = document.getElementById(buttonId);
                if (button) {
                    console.log('Found dropdown button:', buttonId);

                    // Remove any existing event listeners
                    const newButton = button.cloneNode(true);
                    button.parentNode.replaceChild(newButton, button);

                    // Add click event listener
                    newButton.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();

                        console.log('Button clicked:', buttonId);

                        const dropdownId = buttonId.replace('Btn', '');
                        const dropdown = document.getElementById(dropdownId);

                        console.log('Looking for dropdown:', dropdownId);
                        console.log('Found dropdown:', dropdown);

                        if (dropdown) {
                            // Toggle the dropdown
                            const isHidden = dropdown.classList.contains('hidden');
                            console.log('Dropdown hidden:', isHidden);

                            if (isHidden) {
                                dropdown.classList.remove('hidden');
                                dropdown.style.visibility = 'visible';
                                dropdown.style.opacity = '1';
                                dropdown.style.maxHeight = '200px';
                                console.log('Dropdown shown');
                            } else {
                                dropdown.classList.add('hidden');
                                dropdown.style.visibility = 'hidden';
                                dropdown.style.opacity = '0';
                                dropdown.style.maxHeight = '0';
                                console.log('Dropdown hidden');
                            }

                            // Toggle arrow icon
                            const arrowIcon = this.querySelector('.ri-arrow-down-s-line');
                            if (arrowIcon) {
                                arrowIcon.classList.toggle('rotate-180');
                                console.log('Arrow toggled');
                            } else {
                                console.log('Arrow icon not found');
                            }
                        } else {
                            console.log('Dropdown not found:', dropdownId);
                        }
                    });

                    console.log('Event listener added to:', buttonId);
                } else {
                    console.log('Dropdown button not found:', buttonId);
                }
            });

            // Update current date and time
            function updateDateTime() {
                const now = new Date();
                const dateTimeElement = document.getElementById('current-date-time');
                if (dateTimeElement) {
                    dateTimeElement.textContent = now.toLocaleDateString('en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
            }

            updateDateTime();
            setInterval(updateDateTime, 60000); // Update every minute

            // Export dropdown functionality
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

            // Keyboard shortcuts
            document.addEventListener('keydown', function (e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    openSearchModal();
                }
                if (e.key === 'Escape') {
                    closeSearchModal();
                    // Also close other dropdowns on Escape
                    if (exportDropdown) exportDropdown.classList.add('hidden');
                }
            });
        });
    </script>
</body>

</html>