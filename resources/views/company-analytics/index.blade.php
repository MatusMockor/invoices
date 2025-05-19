<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-white">
                {{ __('Company Analytics') }}
            </h2>
        </div>
    </x-slot>

    <!-- Add Chart.js for Flowbite charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="p-6">

                    @if(isset($statistics['current_company_income']))
                    <!-- Current Company Financial Summary -->
                    <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-6">
                        <h3 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">{{ __('Current Company Financial Summary') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <!-- Income -->
                            <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-6">
                                <div class="flex items-center">
                                    <div class="inline-flex items-center justify-center flex-shrink-0 w-12 h-12 text-green-500 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ms-4">
                                        <h3 class="mb-1 text-lg font-medium text-gray-900 dark:text-white">{{ __('Total Income') }}</h3>
                                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                            {{ number_format($statistics['current_company_income'], 2) }} €
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Expenses -->
                            <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-6">
                                <div class="flex items-center">
                                    <div class="inline-flex items-center justify-center flex-shrink-0 w-12 h-12 text-red-500 bg-red-100 rounded-lg dark:bg-red-800 dark:text-red-200">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ms-4">
                                        <h3 class="mb-1 text-lg font-medium text-gray-900 dark:text-white">{{ __('Total Expenses') }}</h3>
                                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                                            {{ number_format($statistics['current_company_expenses'], 2) }} €
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Balance -->
                            <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-6">
                                <div class="flex items-center">
                                    <div class="inline-flex items-center justify-center flex-shrink-0 w-12 h-12 text-blue-500 bg-blue-100 rounded-lg dark:bg-blue-800 dark:text-blue-200">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                                        </svg>
                                    </div>
                                    <div class="ms-4">
                                        <h3 class="mb-1 text-lg font-medium text-gray-900 dark:text-white">{{ __('Balance') }}</h3>
                                        <div class="text-2xl font-bold {{ $statistics['current_company_balance'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ number_format($statistics['current_company_balance'], 2) }} €
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Chart -->
                        <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <h4 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">{{ __('Monthly Income vs Expenses') }} ({{ $currentYear }})</h4>
                            <div class="w-full h-80">
                                <canvas id="financialChart"></canvas>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-6 text-center">
                        <div class="flex items-center justify-center">
                            <svg class="w-10 h-10 text-gray-400 dark:text-gray-500 mb-3.5 mx-auto" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12zm11-4a1 1 0 1 0-2 0v4a1 1 0 0 0 .293.707l2.828 2.829a1 1 0 1 0 1.415-1.415L13 11.586V8z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <p class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{{ __('No Data Available') }}</p>
                        <p class="text-gray-500 dark:text-gray-400">{{ __('No financial data available for the current company.') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Initialization Scripts - Flowbite Style -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Register the plugins
            Chart.register(ChartDataLabels);

            // Set Flowbite chart colors that work with dark mode
            const getFlowbiteChartColors = () => {
                const isDarkMode = document.documentElement.classList.contains('dark');
                return {
                    textColor: isDarkMode ? '#f3f4f6' : '#1f2937', // Flowbite text colors
                    gridColor: isDarkMode ? 'rgba(243, 244, 246, 0.1)' : 'rgba(17, 24, 39, 0.1)', // Flowbite grid colors
                    backgroundColor: isDarkMode ? '#374151' : '#ffffff', // Flowbite background colors
                    greenColor: isDarkMode ? 'rgba(34, 197, 94, 0.7)' : 'rgba(22, 163, 74, 0.7)', // Green with dark mode adaptation
                    greenBorderColor: isDarkMode ? 'rgb(34, 197, 94)' : 'rgb(22, 163, 74)',
                    redColor: isDarkMode ? 'rgba(239, 68, 68, 0.7)' : 'rgba(220, 38, 38, 0.7)', // Red with dark mode adaptation
                    redBorderColor: isDarkMode ? 'rgb(239, 68, 68)' : 'rgb(220, 38, 38)',
                    tooltipBackground: isDarkMode ? '#374151' : '#ffffff',
                    tooltipBorderColor: isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)',
                    dataLabelColor: isDarkMode ? '#ffffff' : '#000000'
                };
            };

            // Function to update chart colors on theme change
            const updateChartColors = (chart) => {
                if (!chart) return;
                
                const colors = getFlowbiteChartColors();
                
                // Update dataset colors
                chart.data.datasets[0].backgroundColor = colors.greenColor;
                chart.data.datasets[0].borderColor = colors.greenBorderColor;
                chart.data.datasets[0].hoverBackgroundColor = colors.greenBorderColor;
                
                chart.data.datasets[1].backgroundColor = colors.redColor;
                chart.data.datasets[1].borderColor = colors.redBorderColor;
                chart.data.datasets[1].hoverBackgroundColor = colors.redBorderColor;
                
                // Update scales
                chart.options.scales.x.ticks.color = colors.textColor;
                chart.options.scales.y.ticks.color = colors.textColor;
                chart.options.scales.y.grid.color = colors.gridColor;
                
                // Update legend
                chart.options.plugins.legend.labels.color = colors.textColor;
                
                // Update tooltip
                chart.options.plugins.tooltip.backgroundColor = colors.tooltipBackground;
                chart.options.plugins.tooltip.titleColor = colors.textColor;
                chart.options.plugins.tooltip.bodyColor = colors.textColor;
                chart.options.plugins.tooltip.borderColor = colors.tooltipBorderColor;
                
                chart.update();
            };

            // Initialize Financial Chart if it exists - using Flowbite column chart style
            @if(isset($statistics['current_company_income']))
            let financialChart;
            const financialChartEl = document.getElementById('financialChart');
            if (financialChartEl) {
                const ctx = financialChartEl.getContext('2d');
                const colors = getFlowbiteChartColors();

                // Set up the chart context
                ctx.canvas.height = 400;

                // Prepare data for Flowbite column chart
                const financialData = {
                    labels: {!! isset($monthlyData) ? json_encode($monthlyData['labels']) : '["Income", "Expenses"]' !!},
                    datasets: [
                        {
                            label: 'Income (€)',
                            data: {!! isset($monthlyData) ? json_encode($monthlyData['income']) : '[' . $statistics['current_company_income'] . ']' !!},
                            backgroundColor: colors.greenColor,
                            borderColor: colors.greenBorderColor,
                            borderWidth: 1,
                            borderRadius: 6,
                            borderSkipped: false,
                            hoverBackgroundColor: colors.greenBorderColor,
                            barPercentage: 0.5,
                            categoryPercentage: 0.7
                        },
                        {
                            label: 'Expenses (€)',
                            data: {!! isset($monthlyData) ? json_encode($monthlyData['expenses']) : '[' . $statistics['current_company_expenses'] . ']' !!},
                            backgroundColor: colors.redColor,
                            borderColor: colors.redBorderColor,
                            borderWidth: 1,
                            borderRadius: 6,
                            borderSkipped: false,
                            hoverBackgroundColor: colors.redBorderColor,
                            barPercentage: 0.5,
                            categoryPercentage: 0.7
                        }
                    ]
                };

                // Create the Flowbite column chart
                financialChart = new Chart(financialChartEl, {
                    type: 'bar',  // Column chart in Flowbite is a bar chart with vertical orientation
                    data: financialData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'x',  // Vertical bars (columns)
                        plugins: {
                            legend: {
                                position: 'top',
                                align: 'start',
                                labels: {
                                    color: colors.textColor,
                                    usePointStyle: true,
                                    pointStyleWidth: 10,
                                    boxWidth: 10,
                                    boxHeight: 10,
                                    padding: 20,
                                    font: {
                                        size: 14
                                    }
                                }
                            },
                            tooltip: {
                                enabled: true,
                                backgroundColor: colors.tooltipBackground,
                                titleColor: colors.textColor,
                                bodyColor: colors.textColor,
                                titleFont: {
                                    size: 14
                                },
                                bodyFont: {
                                    size: 14
                                },
                                padding: 12,
                                cornerRadius: 4,
                                displayColors: true,
                                usePointStyle: true,
                                borderColor: colors.tooltipBorderColor,
                                borderWidth: 1,
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': €' + context.parsed.y.toLocaleString();
                                    }
                                }
                            },
                            datalabels: {
                                color: colors.dataLabelColor,
                                anchor: 'center',
                                align: 'center',
                                font: {
                                    weight: 'bold',
                                    size: 12
                                },
                                formatter: (value) => {
                                    return '€' + value.toLocaleString();
                                },
                                display: function(context) {
                                    return context.dataset.data[context.dataIndex] > 0;
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false,
                                    drawBorder: false
                                },
                                ticks: {
                                    color: colors.textColor,
                                    font: {
                                        size: 12
                                    },
                                    padding: 10
                                }
                            },
                            y: {
                                grid: {
                                    color: colors.gridColor,
                                    borderDash: [4],
                                    drawBorder: false
                                },
                                ticks: {
                                    color: colors.textColor,
                                    font: {
                                        size: 12
                                    },
                                    padding: 10,
                                    callback: function(value) {
                                        return '€' + value.toLocaleString();
                                    }
                                },
                                beginAtZero: true
                            }
                        }
                    }
                });
                
                // Listen for theme changes
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.attributeName === 'class' && 
                            mutation.target === document.documentElement) {
                            updateChartColors(financialChart);
                        }
                    });
                });
                
                observer.observe(document.documentElement, { attributes: true });
            }
            @endif
        });
    </script>
</x-app-layout>
