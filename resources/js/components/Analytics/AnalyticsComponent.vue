<template>
  <div class="space-y-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Total Revenue Card -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center justify-center w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg">
            <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="currentColor" viewBox="0 0 20 20">
              <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
            </svg>
          </div>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ formatCurrency(totalRevenue) }}</p>
          <p class="text-sm mt-2">
            <span :class="revenueChange >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" class="font-medium">
              {{ revenueChange >= 0 ? '+' : '' }}{{ revenueChange }}%
            </span>
            <span class="text-gray-500 dark:text-gray-400 ml-1">from last period</span>
          </p>
        </div>
      </div>

      <!-- Total Earnings Card -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center justify-center w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg">
            <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd"></path>
            </svg>
          </div>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Earnings</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ formatCurrency(totalEarnings) }}</p>
        </div>
      </div>

      <!-- Total Expenses Card -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center justify-center w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg">
            <svg class="w-6 h-6 text-red-600 dark:text-red-300" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
            </svg>
          </div>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Expenses</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ formatCurrency(totalExpenses) }}</p>
        </div>
      </div>

      <!-- Balance Card -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center justify-center w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg">
            <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path>
            </svg>
          </div>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Balance</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ formatCurrency(totalBalance) }}</p>
        </div>
      </div>
    </div>

    <!-- Secondary Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <!-- Invoices Paid -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center justify-center w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg">
            <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
            </svg>
          </div>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Invoices Paid</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ invoicesPaid }}</p>
          <p class="text-sm mt-2">
            <span :class="invoicesPaidChange >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" class="font-medium">
              {{ invoicesPaidChange >= 0 ? '+' : '' }}{{ invoicesPaidChange }}%
            </span>
            <span class="text-gray-500 dark:text-gray-400 ml-1">from last period</span>
          </p>
        </div>
      </div>

      <!-- Pending Invoices -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center justify-center w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
            </svg>
          </div>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Invoices</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ invoicesPending }}</p>
          <p class="text-sm mt-2">
            <span :class="invoicesPendingChange <= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" class="font-medium">
              {{ invoicesPendingChange >= 0 ? '+' : '' }}{{ invoicesPendingChange }}%
            </span>
            <span class="text-gray-500 dark:text-gray-400 ml-1">from last period</span>
          </p>
        </div>
      </div>

      <!-- Overdue Invoices -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center justify-center w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg">
            <svg class="w-6 h-6 text-red-600 dark:text-red-300" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
          </div>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Overdue Invoices</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ invoicesOverdue }}</p>
          <p class="text-sm mt-2">
            <span :class="invoicesOverdueChange <= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" class="font-medium">
              {{ invoicesOverdueChange >= 0 ? '+' : '' }}{{ invoicesOverdueChange }}%
            </span>
            <span class="text-gray-500 dark:text-gray-400 ml-1">from last period</span>
          </p>
        </div>
      </div>
    </div>

    <!-- Chart Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Income & Expenses Trend</h3>
        <span class="text-sm text-gray-500 dark:text-gray-400">Monthly Overview</span>
      </div>
      <div ref="revenueChart"></div>
    </div>

    <!-- Top Clients Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
      <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Clients</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Your highest revenue generating clients</p>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
            <tr>
              <th scope="col" class="px-6 py-3">Client</th>
              <th scope="col" class="px-6 py-3">Invoices</th>
              <th scope="col" class="px-6 py-3">Total Revenue</th>
              <th scope="col" class="px-6 py-3">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(client, index) in topClients"
              :key="index"
              class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50"
            >
              <th scope="row" class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                {{ client.name }}
              </th>
              <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ client.invoices }}</td>
              <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ formatCurrency(client.revenue) }}</td>
              <td class="px-6 py-4">
                <span :class="getStatusClass(client.status)">
                  {{ client.status }}
                </span>
              </td>
            </tr>
            <tr v-if="topClients.length === 0">
              <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                No client data available
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import ApexCharts from 'apexcharts';

export default {
  name: 'AnalyticsComponent',
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
      chart: null,
      isDarkMode: document.documentElement.classList.contains('dark'),
      isInitialized: false
    }
  },
  computed: {
    totalRevenue() {
      return this.analyticsData.totalRevenue || 0
    },
    revenueChange() {
      return this.analyticsData.revenueChange || 0
    },
    totalEarnings() {
      return this.analyticsData.totalEarnings || 0
    },
    totalExpenses() {
      return this.analyticsData.totalExpenses || 0
    },
    totalBalance() {
      return this.analyticsData.totalBalance || 0
    },
    invoicesPaid() {
      return this.analyticsData.invoicesPaid || 0
    },
    invoicesPaidChange() {
      return this.analyticsData.invoicesPaidChange || 0
    },
    invoicesPending() {
      return this.analyticsData.invoicesPending || 0
    },
    invoicesPendingChange() {
      return this.analyticsData.invoicesPendingChange || 0
    },
    invoicesOverdue() {
      return this.analyticsData.invoicesOverdue || 0
    },
    invoicesOverdueChange() {
      return this.analyticsData.invoicesOverdueChange || 0
    },
    topClients() {
      return this.analyticsData.topClients || []
    }
  },
  mounted() {
    this.initChart()
    this.observeThemeChanges()
  },
  beforeUnmount() {
    if (this.observer) {
      this.observer.disconnect()
    }
    if (this.chart) {
      this.chart.destroy()
      this.chart = null
    }
  },
  methods: {
    formatCurrency(value) {
      return new Intl.NumberFormat('sk-SK', {
        style: 'currency',
        currency: 'EUR'
      }).format(value)
    },
    getStatusClass(status) {
      const statusLower = status?.toLowerCase() || ''
      const classes = {
        active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        inactive: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
      }
      return `px-2.5 py-0.5 text-xs font-medium rounded ${classes[statusLower] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'}`
    },
    getChartOptions() {
      this.isDarkMode = document.documentElement.classList.contains('dark')
      const textColor = this.isDarkMode ? '#e5e7eb' : '#374151'
      const gridColor = this.isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'

      const labels = this.analyticsData.revenueData?.labels || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']
      const incomeValues = this.analyticsData.revenueData?.values || [1500, 2500, 2000, 3000, 2800, 3500]
      const expenseValues = this.analyticsData.expenseData?.values || [1000, 1800, 1500, 2200, 2000, 2800]

      return {
        series: [
          {
            name: 'Income',
            data: incomeValues,
            color: '#10b981'
          },
          {
            name: 'Expenses',
            data: expenseValues,
            color: '#ef4444'
          }
        ],
        chart: {
          type: 'area',
          height: 350,
          fontFamily: 'Inter, sans-serif',
          toolbar: {
            show: false
          },
          zoom: {
            enabled: false
          },
          background: 'transparent'
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: 'smooth',
          width: 2
        },
        fill: {
          type: 'gradient',
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.4,
            opacityTo: 0.1,
            stops: [0, 90, 100]
          }
        },
        grid: {
          borderColor: gridColor,
          strokeDashArray: 4,
          xaxis: {
            lines: {
              show: false
            }
          },
          yaxis: {
            lines: {
              show: true
            }
          },
          padding: {
            top: 0,
            right: 0,
            bottom: 0,
            left: 0
          }
        },
        xaxis: {
          categories: labels,
          labels: {
            style: {
              colors: textColor,
              fontSize: '12px',
              fontWeight: 500
            }
          },
          axisBorder: {
            show: false
          },
          axisTicks: {
            show: false
          }
        },
        yaxis: {
          labels: {
            style: {
              colors: textColor,
              fontSize: '12px',
              fontWeight: 500
            },
            formatter: (value) => {
              return new Intl.NumberFormat('sk-SK', {
                style: 'currency',
                currency: 'EUR',
                minimumFractionDigits: 0
              }).format(value)
            }
          }
        },
        legend: {
          position: 'top',
          horizontalAlign: 'left',
          fontSize: '14px',
          fontWeight: 500,
          labels: {
            colors: textColor
          },
          markers: {
            width: 12,
            height: 12,
            radius: 3
          },
          itemMargin: {
            horizontal: 12,
            vertical: 0
          }
        },
        tooltip: {
          enabled: true,
          shared: true,
          intersect: false,
          custom: ({ series, seriesIndex, dataPointIndex, w }) => {
            const isDark = this.isDarkMode
            const bgColor = isDark ? '#1f2937' : '#ffffff'
            const textColor = isDark ? '#f3f4f6' : '#111827'
            const borderColor = isDark ? '#374151' : '#e5e7eb'

            const month = w.globals.labels[dataPointIndex]
            const income = series[0][dataPointIndex]
            const expenses = series[1][dataPointIndex]

            const formatValue = (val) => {
              return new Intl.NumberFormat('sk-SK', {
                style: 'currency',
                currency: 'EUR',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
              }).format(val)
            }

            return `
              <div style="
                background: ${bgColor};
                border: 1px solid ${borderColor};
                border-radius: 6px;
                padding: 12px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                min-width: 180px;
              ">
                <div style="
                  color: ${textColor};
                  font-weight: 600;
                  font-size: 13px;
                  margin-bottom: 8px;
                  font-family: Inter, sans-serif;
                ">${month}</div>
                <div style="margin-bottom: 6px;">
                  <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="
                      width: 10px;
                      height: 10px;
                      background: #10b981;
                      border-radius: 2px;
                      display: inline-block;
                    "></span>
                    <span style="
                      color: ${textColor};
                      font-size: 13px;
                      font-family: Inter, sans-serif;
                    ">Income:</span>
                    <span style="
                      color: ${textColor};
                      font-weight: 600;
                      font-size: 13px;
                      margin-left: auto;
                      font-family: Inter, sans-serif;
                    ">${formatValue(income)}</span>
                  </div>
                </div>
                <div>
                  <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="
                      width: 10px;
                      height: 10px;
                      background: #ef4444;
                      border-radius: 2px;
                      display: inline-block;
                    "></span>
                    <span style="
                      color: ${textColor};
                      font-size: 13px;
                      font-family: Inter, sans-serif;
                    ">Expenses:</span>
                    <span style="
                      color: ${textColor};
                      font-weight: 600;
                      font-size: 13px;
                      margin-left: auto;
                      font-family: Inter, sans-serif;
                    ">${formatValue(expenses)}</span>
                  </div>
                </div>
              </div>
            `
          }
        }
      }
    },
    observeThemeChanges() {
      const observer = new MutationObserver((mutations) => {
        for (const mutation of mutations) {
          if (mutation.attributeName === 'class') {
            const newIsDarkMode = document.documentElement.classList.contains('dark')
            if (newIsDarkMode !== this.isDarkMode) {
              this.isDarkMode = newIsDarkMode
              this.updateChartTheme()
            }
          }
        }
      })
      observer.observe(document.documentElement, { attributes: true })
      this.observer = observer
    },
    updateChartTheme() {
      if (!this.chart || !this.isInitialized) {
        return
      }

      const options = this.getChartOptions()

      // Update only theme-related options without recreating the chart
      this.chart.updateOptions({
        grid: options.grid,
        xaxis: options.xaxis,
        yaxis: options.yaxis,
        legend: options.legend,
        tooltip: options.tooltip
      }, false, false)
    },
    initChart() {
      if (!this.$refs.revenueChart) {
        return
      }

      // Destroy existing chart if any
      if (this.chart) {
        this.chart.destroy()
        this.chart = null
        this.isInitialized = false
      }

      try {
        const options = this.getChartOptions()
        this.chart = new ApexCharts(this.$refs.revenueChart, options)
        this.chart.render()
        this.isInitialized = true
      } catch (e) {
        console.error('Error creating chart:', e)
        this.chart = null
        this.isInitialized = false
      }
    }
  },
  watch: {
    analyticsData: {
      handler(newData) {
        if (!this.chart || !this.isInitialized) {
          this.$nextTick(() => {
            this.initChart()
          })
          return
        }

        // Update data without recreating the chart
        const incomeValues = newData.revenueData?.values || []
        const expenseValues = newData.expenseData?.values || []
        const labels = newData.revenueData?.labels || []

        if (incomeValues.length > 0 && expenseValues.length > 0) {
          this.chart.updateSeries([
            { name: 'Income', data: incomeValues },
            { name: 'Expenses', data: expenseValues }
          ], false)

          this.chart.updateOptions({
            xaxis: {
              categories: labels
            }
          }, false, false)
        }
      },
      deep: true
    }
  }
}
</script>
