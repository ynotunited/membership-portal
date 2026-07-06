document.addEventListener('DOMContentLoaded', function () {
    // Chart Initialization
    const chartDom = document.getElementById('activityChart');
    if (chartDom) {
        const myChart = echarts.init(chartDom);
        const option = {
            animation: true,
            grid: { left: 0, right: 10, top: 20, bottom: 0, containLabel: true },
            xAxis: {
                type: 'category',
                data: revenueChartData.labels,
                axisLine: { lineStyle: { color: '#e5e7eb' } },
                axisTick: { show: false },
                axisLabel: { color: '#6b7280' }
            },
            yAxis: {
                type: 'value',
                axisLine: { show: false },
                axisTick: { show: false },
                axisLabel: { color: '#6b7280', formatter: '${value}' },
                splitLine: { lineStyle: { color: '#f3f4f6' } }
            },
            tooltip: {
                trigger: 'axis',
                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                borderColor: '#e5e7eb',
                textStyle: { color: '#1f2937' }
            },
            series: [
                {
                    name: 'Dues Revenue',
                    type: 'line',
                    data: revenueChartData.dues,
                    smooth: true,
                    lineStyle: { color: 'rgba(64, 129, 0, 1)', width: 3 },
                    itemStyle: { color: 'rgba(64, 129, 0, 1)' },
                    areaStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                            { offset: 0, color: 'rgba(64, 129, 0, 0.1)' },
                            { offset: 1, color: 'rgba(64, 129, 0, 0.01)' }
                        ])
                    },
                    showSymbol: false
                },
                {
                    name: 'Shares Revenue',
                    type: 'line',
                    data: revenueChartData.shares,
                    smooth: true,
                    lineStyle: { color: 'rgba(187, 31, 31, 1)', width: 3 },
                    itemStyle: { color: 'rgba(187, 31, 31, 1)' },
                    areaStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                            { offset: 0, color: 'rgba(187, 31, 31, 0.1)' },
                            { offset: 1, color: 'rgba(187, 31, 31, 0.01)' }
                        ])
                    },
                    showSymbol: false
                }
            ]
        };
        myChart.setOption(option);
        window.addEventListener('resize', () => myChart.resize());
    }

    // Membership Type Chart
    const membershipChartDom = document.getElementById('membershipTypeChart');
    if (membershipChartDom && typeof membershipTypeData !== 'undefined' && membershipTypeData.length > 0) {
        const membershipChart = echarts.init(membershipChartDom);
        const chartData = membershipTypeData.map(item => ({
            name: item.type,
            value: item.count
        }));
        const option = {
            tooltip: { trigger: 'item' },
            legend: {
                orient: 'vertical',
                left: 'left',
                textStyle: { color: '#6b7280' }
            },
            series: [
                {
                    name: 'Membership Types',
                    type: 'pie',
                    radius: '70%',
                    data: chartData,
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }
            ]
        };
        membershipChart.setOption(option);
        window.addEventListener('resize', () => membershipChart.resize());
    }

    // Sidebar Navigation
    const navItems = document.querySelectorAll('.sidebar-nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', function (e) {
            if (this.id !== 'resourcesDropdown') {
                // The active class is handled by the server-side rendering now
            }
        });
    });

    // Logout Confirmation
    const logoutButton = document.getElementById('logout-button');
    if (logoutButton) {
        logoutButton.addEventListener('click', function (e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = this.href;
            }
        });
    }

    // Current Date and Time
    const dateTimeElement = document.getElementById('current-date-time');
    if (dateTimeElement) {
        function updateDateTime() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
            dateTimeElement.textContent = now.toLocaleDateString('en-US', options);
        }
        updateDateTime();
        setInterval(updateDateTime, 60000); // Update every minute
    }

    // Dropdown Toggles
    const setupDropdown = (buttonId, dropdownId) => {
        const button = document.getElementById(buttonId);
        const dropdown = document.getElementById(dropdownId);
        if (button && dropdown) {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                dropdown.classList.toggle('hidden');
            });
        }
    };

    setupDropdown('exportBtn', 'exportDropdown');

    // Close dropdowns if clicked outside
    document.addEventListener('click', (event) => {
        const exportDropdown = document.getElementById('exportDropdown');
        if (exportDropdown && !exportDropdown.contains(event.target) && !document.getElementById('exportBtn').contains(event.target)) {
            exportDropdown.classList.add('hidden');
        }
    });

    // Geographical Map Widget
    const geoMapDom = document.getElementById('geoMapChart');
    if (geoMapDom && typeof geoDistributionData !== 'undefined' && geoDistributionData.length > 0) {
        echarts.registerMap('world', {}); // ECharts world map
        const geoData = geoDistributionData.map(item => ({ name: item.country, value: item.count }));
        const geoChart = echarts.init(geoMapDom);
        geoChart.setOption({
            tooltip: { trigger: 'item' },
            visualMap: {
                min: 0,
                max: Math.max(...geoData.map(d => d.value)),
                left: 'left',
                top: 'bottom',
                text: ['High', 'Low'],
                calculable: true
            },
            series: [
                {
                    name: 'Members',
                    type: 'map',
                    map: 'world',
                    roam: true,
                    emphasis: { label: { show: true } },
                    data: geoData
                }
            ]
        });
        window.addEventListener('resize', () => geoChart.resize());
    }

    // Membership Types Dropdown
    const mtBtn = document.getElementById('membershipTypesDropdownBtn');
    const mtDropdown = document.getElementById('membershipTypesDropdown');
    if (mtBtn && mtDropdown) {
        mtBtn.addEventListener('click', function (e) {
            e.preventDefault();
            mtDropdown.classList.toggle('hidden');
        });
        document.addEventListener('click', function (e) {
            if (!mtBtn.contains(e.target) && !mtDropdown.contains(e.target)) {
                mtDropdown.classList.add('hidden');
            }
        });
    }

    // Members Dropdown
    const membersBtn = document.getElementById('membersDropdownBtn');
    const membersDropdown = document.getElementById('membersDropdown');
    if (membersBtn && membersDropdown) {
        membersBtn.addEventListener('click', function (e) {
            e.preventDefault();
            membersDropdown.classList.toggle('hidden');
        });
        document.addEventListener('click', function (e) {
            if (!membersBtn.contains(e.target) && !membersDropdown.contains(e.target)) {
                membersDropdown.classList.add('hidden');
            }
        });
    }

    // Lists Dropdown
    const listsBtn = document.getElementById('listsDropdownBtn');
    const listsDropdown = document.getElementById('listsDropdown');
    if (listsBtn && listsDropdown) {
        listsBtn.addEventListener('click', function (e) {
            e.preventDefault();
            listsDropdown.classList.toggle('hidden');
        });
        document.addEventListener('click', function (e) {
            if (!listsBtn.contains(e.target) && !listsDropdown.contains(e.target)) {
                listsDropdown.classList.add('hidden');
            }
        });
    }

    // Reports Dropdown
    const reportsBtn = document.getElementById('reportsDropdownBtn');
    const reportsDropdown = document.getElementById('reportsDropdown');
    if (reportsBtn && reportsDropdown) {
        reportsBtn.addEventListener('click', function (e) {
            e.preventDefault();
            reportsDropdown.classList.toggle('hidden');
        });
        document.addEventListener('click', function (e) {
            if (!reportsBtn.contains(e.target) && !reportsDropdown.contains(e.target)) {
                reportsDropdown.classList.add('hidden');
            }
        });
    }

    // Search Modal Functionality
    const searchBtn = document.getElementById('searchBtn');
    const searchModal = document.getElementById('searchModal');
    const closeSearchModal = document.getElementById('closeSearchModal');
    const modalSearchInput = document.getElementById('modalSearchInput');
    const modalSearchResults = document.getElementById('modalSearchResults');
    let modalSearchTimeout;

    // Debug logging
    console.log('Search elements found:', {
        searchBtn: !!searchBtn,
        searchModal: !!searchModal,
        closeSearchModal: !!closeSearchModal,
        modalSearchInput: !!modalSearchInput,
        modalSearchResults: !!modalSearchResults
    });

    // Open search modal
    if (searchBtn && searchModal) {
        console.log('Setting up search button click handler');
        searchBtn.addEventListener('click', function (e) {
            e.preventDefault();
            console.log('Search button clicked!');
            searchModal.classList.remove('hidden');
            searchModal.style.display = 'block';
            // Focus on search input after modal opens
            setTimeout(() => {
                if (modalSearchInput) {
                    modalSearchInput.focus();
                    console.log('Search input focused');
                }
            }, 100);
        });
    } else {
        console.log('Search button or modal not found:', { searchBtn: !!searchBtn, searchModal: !!searchModal });
    }

    // Close search modal
    if (closeSearchModal && searchModal) {
        closeSearchModal.addEventListener('click', function () {
            console.log('Close search modal clicked');
            searchModal.classList.add('hidden');
            searchModal.style.display = 'none';
            modalSearchInput.value = '';
            modalSearchResults.innerHTML = `
                <div class="text-center text-gray-500 py-8">
                    <i class="ri-search-line text-4xl mb-4"></i>
                    <p>Start typing to search...</p>
                </div>
            `;
        });
    } else {
        console.log('Close button or modal not found:', { closeSearchModal: !!closeSearchModal, searchModal: !!searchModal });
    }

    // Close modal when clicking outside
    if (searchModal) {
        searchModal.addEventListener('click', function (e) {
            if (e.target === searchModal) {
                console.log('Clicked outside modal, closing');
                searchModal.classList.add('hidden');
                searchModal.style.display = 'none';
                modalSearchInput.value = '';
                modalSearchResults.innerHTML = `
                    <div class="text-center text-gray-500 py-8">
                        <i class="ri-search-line text-4xl mb-4"></i>
                        <p>Start typing to search...</p>
                    </div>
                `;
            }
        });
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function (e) {
        // Ctrl+K or Cmd+K to open search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            if (typeof openSearchModal === 'function') {
                openSearchModal();
            }
        }

        // Escape to close search modal
        if (e.key === 'Escape') {
            const searchModal = document.getElementById('searchModal');
            if (searchModal && !searchModal.classList.contains('hidden')) {
                if (typeof closeSearchModal === 'function') {
                    closeSearchModal();
                }
            }
        }
    });

    // Test function for debugging
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
});