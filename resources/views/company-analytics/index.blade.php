<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Company Analytics') }}
            </h2>
        </div>
    </x-slot>

    <!-- Add Chart.js and Chart.js plugins from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-gradient@0.5.1"></script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">

                    @if(isset($statistics['current_company_income']))
                    <!-- Current Company Financial Summary -->
                    <div class="bg-white dark:bg-gray-700 overflow-hidden shadow rounded-lg">
                        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-600">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-200">{{ __('Current Company Financial Summary') }}</h3>
                        </div>
                        <div class="p-5">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <!-- Income -->
                                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                                    <div class="p-5">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div class="ml-5 w-0 flex-1">
                                                <dl>
                                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                                        {{ __('Total Income') }}
                                                    </dt>
                                                    <dd>
                                                        <div class="text-lg font-medium text-green-600 dark:text-green-400">
                                                            {{ number_format($statistics['current_company_income'], 2) }} €
                                                        </div>
                                                    </dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Expenses -->
                                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                                    <div class="p-5">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div class="ml-5 w-0 flex-1">
                                                <dl>
                                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                                        {{ __('Total Expenses') }}
                                                    </dt>
                                                    <dd>
                                                        <div class="text-lg font-medium text-red-600 dark:text-red-400">
                                                            {{ number_format($statistics['current_company_expenses'], 2) }} €
                                                        </div>
                                                    </dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Balance -->
                                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                                    <div class="p-5">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                                                </svg>
                                            </div>
                                            <div class="ml-5 w-0 flex-1">
                                                <dl>
                                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                                        {{ __('Balance') }}
                                                    </dt>
                                                    <dd>
                                                        <div class="text-lg font-medium {{ $statistics['current_company_balance'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                            {{ number_format($statistics['current_company_balance'], 2) }} €
                                                        </div>
                                                    </dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Financial Chart -->
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg p-4">
                                <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('Income vs Expenses') }}</h4>
                                <div class="w-full h-80">
                                    <canvas id="financialChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="bg-white dark:bg-gray-700 overflow-hidden shadow rounded-lg p-6 text-center">
                        <p class="text-gray-500 dark:text-gray-400">{{ __('No financial data available for the current company.') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Initialization Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Register the plugins
            Chart.register(ChartDataLabels);

            // Set default Chart.js colors that work with dark mode
            const getChartColors = () => {
                const isDarkMode = document.documentElement.classList.contains('dark');
                return {
                    textColor: isDarkMode ? '#e5e7eb' : '#374151',
                    gridColor: isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)',
                    gradients: {
                        income: {
                            start: isDarkMode ? 'rgba(16, 185, 129, 0.8)' : 'rgba(16, 185, 129, 0.8)',
                            end: isDarkMode ? 'rgba(16, 185, 129, 0.2)' : 'rgba(16, 185, 129, 0.2)'
                        },
                        expenses: {
                            start: isDarkMode ? 'rgba(239, 68, 68, 0.8)' : 'rgba(239, 68, 68, 0.8)',
                            end: isDarkMode ? 'rgba(239, 68, 68, 0.2)' : 'rgba(239, 68, 68, 0.2)'
                        }
                    }
                };
            };

            // Common chart options
            const chartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1500,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: {
                        labels: {
                            color: getChartColors().textColor,
                            font: {
                                weight: 'bold'
                            },
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 15,
                        cornerRadius: 8,
                        displayColors: true,
                        usePointStyle: true,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed.y !== undefined 
                                    ? '€' + context.parsed.y.toLocaleString() 
                                    : context.parsed;
                                return label;
                            }
                        }
                    },
                    datalabels: {
                        color: '#fff',
                        anchor: 'center',
                        align: 'center',
                        font: {
                            weight: 'bold',
                            size: 14
                        },
                        formatter: (value) => {
                            return '€' + value.toLocaleString();
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: getChartColors().textColor,
                            font: {
                                weight: 'bold'
                            }
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        ticks: {
                            color: getChartColors().textColor,
                            font: {
                                weight: 'bold'
                            },
                            callback: function(value) {
                                return '€' + value.toLocaleString();
                            }
                        },
                        grid: {
                            color: getChartColors().gridColor,
                            drawBorder: false
                        },
                        beginAtZero: true
                    }
                }
            };

            // Initialize Financial Chart if it exists
            @if(isset($statistics['current_company_income']))
            const financialChartEl = document.getElementById('financialChart');
            if (financialChartEl) {
                const ctx = financialChartEl.getContext('2d');

                // Create gradients
                const incomeGradient = ctx.createLinearGradient(0, 0, 0, 400);
                incomeGradient.addColorStop(0, getChartColors().gradients.income.start);
                incomeGradient.addColorStop(1, getChartColors().gradients.income.end);

                const expensesGradient = ctx.createLinearGradient(0, 0, 0, 400);
                expensesGradient.addColorStop(0, getChartColors().gradients.expenses.start);
                expensesGradient.addColorStop(1, getChartColors().gradients.expenses.end);

                const financialData = {
                    labels: ['Financial Summary'],
                    datasets: [
                        {
                            label: 'Income',
                            data: [{{ $statistics['current_company_income'] }}],
                            backgroundColor: incomeGradient,
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false,
                            hoverBackgroundColor: 'rgba(16, 185, 129, 0.9)',
                            hoverBorderColor: 'rgba(16, 185, 129, 1)',
                            hoverBorderWidth: 3
                        },
                        {
                            label: 'Expenses',
                            data: [{{ $statistics['current_company_expenses'] }}],
                            backgroundColor: expensesGradient,
                            borderColor: 'rgba(239, 68, 68, 1)',
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false,
                            hoverBackgroundColor: 'rgba(239, 68, 68, 0.9)',
                            hoverBorderColor: 'rgba(239, 68, 68, 1)',
                            hoverBorderWidth: 3
                        }
                    ]
                };

                new Chart(financialChartEl, {
                    type: 'bar',
                    data: financialData,
                    options: chartOptions
                });
            }
            @endif
        });
    </script>
</x-app-layout>
