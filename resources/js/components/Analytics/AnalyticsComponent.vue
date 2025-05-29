<template>
  <div class="space-y-8">
    <!-- Analytics Overview Card -->
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
      <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
          <svg class="h-5 w-5 mr-2 text-indigo-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
            <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
          </svg>
          Analytics Overview
        </h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">View your business performance metrics.</p>
      </div>
      <div class="p-6 bg-white dark:bg-gray-800">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-4 shadow-sm">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-blue-200 dark:bg-blue-800 text-blue-600 dark:text-blue-200 mr-4">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                  <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-blue-600 dark:text-blue-200">Total Revenue</p>
                <p class="text-2xl font-semibold text-gray-700 dark:text-white">{{ formatCurrency(totalRevenue) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  <span :class="revenueChange >= 0 ? 'text-green-500' : 'text-red-500'">
                    {{ revenueChange >= 0 ? '+' : '' }}{{ revenueChange }}%
                  </span> 
                  from last period
                </p>
              </div>
            </div>
          </div>

          <!-- Earnings Card -->
          <div class="bg-emerald-50 dark:bg-emerald-900 rounded-lg p-4 shadow-sm">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-emerald-200 dark:bg-emerald-800 text-emerald-600 dark:text-emerald-200 mr-4">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd"></path>
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-emerald-600 dark:text-emerald-200">Total Earnings</p>
                <p class="text-2xl font-semibold text-gray-700 dark:text-white">{{ formatCurrency(totalEarnings) }}</p>
              </div>
            </div>
          </div>

          <!-- Expenses Card -->
          <div class="bg-rose-50 dark:bg-rose-900 rounded-lg p-4 shadow-sm">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-rose-200 dark:bg-rose-800 text-rose-600 dark:text-rose-200 mr-4">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-rose-600 dark:text-rose-200">Total Expenses</p>
                <p class="text-2xl font-semibold text-gray-700 dark:text-white">{{ formatCurrency(totalExpenses) }}</p>
              </div>
            </div>
          </div>

          <!-- Balance Card -->
          <div class="bg-purple-50 dark:bg-purple-900 rounded-lg p-4 shadow-sm">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-purple-200 dark:bg-purple-800 text-purple-600 dark:text-purple-200 mr-4">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path>
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-purple-600 dark:text-purple-200">Balance</p>
                <p class="text-2xl font-semibold text-gray-700 dark:text-white">{{ formatCurrency(totalBalance) }}</p>
              </div>
            </div>
          </div>

          <div class="bg-green-50 dark:bg-green-900 rounded-lg p-4 shadow-sm">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-green-200 dark:bg-green-800 text-green-600 dark:text-green-200 mr-4">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-green-600 dark:text-green-200">Invoices Paid</p>
                <p class="text-2xl font-semibold text-gray-700 dark:text-white">{{ invoicesPaid }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  <span :class="invoicesPaidChange >= 0 ? 'text-green-500' : 'text-red-500'">
                    {{ invoicesPaidChange >= 0 ? '+' : '' }}{{ invoicesPaidChange }}%
                  </span> 
                  from last period
                </p>
              </div>
            </div>
          </div>

          <div class="bg-yellow-50 dark:bg-yellow-900 rounded-lg p-4 shadow-sm">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-yellow-200 dark:bg-yellow-800 text-yellow-600 dark:text-yellow-200 mr-4">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-yellow-600 dark:text-yellow-200">Pending Invoices</p>
                <p class="text-2xl font-semibold text-gray-700 dark:text-white">{{ invoicesPending }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  <span :class="invoicesPendingChange <= 0 ? 'text-green-500' : 'text-red-500'">
                    {{ invoicesPendingChange >= 0 ? '+' : '' }}{{ invoicesPendingChange }}%
                  </span> 
                  from last period
                </p>
              </div>
            </div>
          </div>

          <div class="bg-red-50 dark:bg-red-900 rounded-lg p-4 shadow-sm">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-red-200 dark:bg-red-800 text-red-600 dark:text-red-200 mr-4">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-red-600 dark:text-red-200">Overdue Invoices</p>
                <p class="text-2xl font-semibold text-gray-700 dark:text-white">{{ invoicesOverdue }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  <span :class="invoicesOverdueChange <= 0 ? 'text-green-500' : 'text-red-500'">
                    {{ invoicesOverdueChange >= 0 ? '+' : '' }}{{ invoicesOverdueChange }}%
                  </span> 
                  from last period
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Chart Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
          <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Income & Expenses Trend</h4>
          <div class="relative" style="height: 300px;">
            <canvas ref="revenueChart" class="w-full h-full"></canvas>
          </div>
        </div>

        <!-- Top Clients Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
          <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Top Clients</h4>
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
              <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                  <th scope="col" class="px-6 py-3">Client</th>
                  <th scope="col" class="px-6 py-3">Invoices</th>
                  <th scope="col" class="px-6 py-3">Total Revenue</th>
                  <th scope="col" class="px-6 py-3">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(client, index) in topClients" :key="index" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                  <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    {{ client.name }}
                  </th>
                  <td class="px-6 py-4">{{ client.invoices }}</td>
                  <td class="px-6 py-4">{{ formatCurrency(client.revenue) }}</td>
                  <td class="px-6 py-4">
                    <span :class="getStatusClass(client.status)">
                      {{ client.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Chart from 'chart.js/auto';

export default {
  props: {
    analyticsData: {
      type: Object,
      default: () => ({
        totalRevenue: 0,
        revenueChange: 0,
        totalEarnings: 0,
        totalExpenses: 0,
        totalBalance: 0,
        invoicesPaid: 0,
        invoicesPaidChange: 0,
        invoicesPending: 0,
        invoicesPendingChange: 0,
        invoicesOverdue: 0,
        invoicesOverdueChange: 0,
        revenueData: {
          labels: [],
          values: []
        },
        expenseData: {
          labels: [],
          values: []
        },
        topClients: []
      })
    }
  },
  data() {
    return {
      chart: null
    }
  },
  computed: {
    totalRevenue() {
      return this.analyticsData.totalRevenue || 0;
    },
    revenueChange() {
      return this.analyticsData.revenueChange || 0;
    },
    totalEarnings() {
      return this.analyticsData.totalEarnings || 0;
    },
    totalExpenses() {
      return this.analyticsData.totalExpenses || 0;
    },
    totalBalance() {
      return this.analyticsData.totalBalance || 0;
    },
    invoicesPaid() {
      return this.analyticsData.invoicesPaid || 0;
    },
    invoicesPaidChange() {
      return this.analyticsData.invoicesPaidChange || 0;
    },
    invoicesPending() {
      return this.analyticsData.invoicesPending || 0;
    },
    invoicesPendingChange() {
      return this.analyticsData.invoicesPendingChange || 0;
    },
    invoicesOverdue() {
      return this.analyticsData.invoicesOverdue || 0;
    },
    invoicesOverdueChange() {
      return this.analyticsData.invoicesOverdueChange || 0;
    },
    topClients() {
      return this.analyticsData.topClients || [];
    }
  },
  mounted() {
    this.initChart();
  },
  methods: {
    formatCurrency(value) {
      return new Intl.NumberFormat('sk-SK', { 
        style: 'currency', 
        currency: 'EUR' 
      }).format(value);
    },
    getStatusClass(status) {
      switch(status.toLowerCase()) {
        case 'active':
          return 'px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-300';
        case 'pending':
          return 'px-2 py-1 text-xs font-medium text-yellow-700 bg-yellow-100 rounded-full dark:bg-yellow-900 dark:text-yellow-300';
        case 'inactive':
          return 'px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-full dark:bg-red-900 dark:text-red-300';
        default:
          return 'px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-300';
      }
    },
    initChart() {
      const ctx = this.$refs.revenueChart.getContext('2d');

      // Destroy existing chart if it exists
      if (this.chart) {
        this.chart.destroy();
      }

      const labels = this.analyticsData.revenueData?.labels || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
      const incomeValues = this.analyticsData.revenueData?.values || [1500, 2500, 2000, 3000, 2800, 3500];
      const expenseValues = this.analyticsData.expenseData?.values || [1000, 1800, 1500, 2200, 2000, 2800];

      // Determine if dark mode is active
      const isDarkMode = document.documentElement.classList.contains('dark');
      const textColor = isDarkMode ? '#e5e7eb' : '#374151';
      const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

      this.chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [
            {
              label: 'Income',
              data: incomeValues,
              fill: true,
              backgroundColor: 'rgba(16, 185, 129, 0.2)',  // Emerald color for income
              borderColor: 'rgba(16, 185, 129, 1)',
              tension: 0.4,
              pointBackgroundColor: 'rgba(16, 185, 129, 1)',
              pointBorderColor: '#fff',
              pointHoverBackgroundColor: '#fff',
              pointHoverBorderColor: 'rgba(16, 185, 129, 1)'
            },
            {
              label: 'Expenses',
              data: expenseValues,
              fill: true,
              backgroundColor: 'rgba(244, 63, 94, 0.2)',  // Rose color for expenses
              borderColor: 'rgba(244, 63, 94, 1)',
              tension: 0.4,
              pointBackgroundColor: 'rgba(244, 63, 94, 1)',
              pointBorderColor: '#fff',
              pointHoverBackgroundColor: '#fff',
              pointHoverBorderColor: 'rgba(244, 63, 94, 1)'
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              grid: {
                color: gridColor
              },
              ticks: {
                color: textColor
              }
            },
            x: {
              grid: {
                color: gridColor
              },
              ticks: {
                color: textColor
              }
            }
          },
          plugins: {
            legend: {
              labels: {
                color: textColor
              }
            }
          }
        }
      });
    }
  },
  watch: {
    analyticsData: {
      handler() {
        this.$nextTick(() => {
          this.initChart();
        });
      },
      deep: true
    }
  }
}
</script>
