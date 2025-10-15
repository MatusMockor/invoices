<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <!-- Calendar Header -->
    <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
      <button
        @click="previousMonth"
        type="button"
        class="text-gray-500 bg-white hover:bg-gray-100 hover:text-gray-900 rounded-lg text-sm p-2.5 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-600"
      >
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
        </svg>
      </button>

      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
        {{ monthName }} {{ currentYear }}
      </h2>

      <button
        @click="nextMonth"
        type="button"
        class="text-gray-500 bg-white hover:bg-gray-100 hover:text-gray-900 rounded-lg text-sm p-2.5 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-600"
      >
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
        </svg>
      </button>
    </div>

    <!-- Calendar Grid -->
    <div class="p-4">
      <!-- Day Headers -->
      <div class="grid grid-cols-7 gap-1 mb-2">
        <div
          v-for="day in dayHeaders"
          :key="day"
          class="text-center text-xs font-medium text-gray-500 dark:text-gray-400 py-2"
        >
          {{ day }}
        </div>
      </div>

      <!-- Calendar Days -->
      <div class="grid grid-cols-7 gap-1">
        <div
          v-for="(day, index) in calendarDays"
          :key="index"
          :class="[
            'min-h-[100px] p-2 rounded-lg border transition-colors',
            day.isCurrentMonth
              ? 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600'
              : 'bg-gray-50 dark:bg-gray-900 border-gray-100 dark:border-gray-700',
            day.isToday
              ? 'ring-2 ring-blue-500 dark:ring-blue-400'
              : '',
            day.tasks.length > 0
              ? 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700'
              : ''
          ]"
          @click="day.tasks.length > 0 && showTasksForDay(day)"
        >
          <!-- Day Number -->
          <div class="flex items-center justify-between mb-1">
            <span
              :class="[
                'text-sm font-medium',
                day.isCurrentMonth
                  ? 'text-gray-900 dark:text-gray-100'
                  : 'text-gray-400 dark:text-gray-600',
                day.isToday
                  ? 'flex items-center justify-center w-6 h-6 bg-blue-600 dark:bg-blue-500 text-white rounded-full'
                  : ''
              ]"
            >
              {{ day.date.getDate() }}
            </span>
            <span
              v-if="day.tasks.length > 0"
              class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 rounded-full"
            >
              {{ day.tasks.length }}
            </span>
          </div>

          <!-- Tasks Preview -->
          <div class="space-y-1">
            <div
              v-for="task in day.tasks.slice(0, 3)"
              :key="task.id"
              :class="[
                'text-xs px-2 py-1 rounded truncate font-medium',
                getPriorityClasses(task.priority)
              ]"
              :title="task.title"
            >
              {{ task.title }}
            </div>
            <div
              v-if="day.tasks.length > 3"
              class="text-xs text-gray-500 dark:text-gray-400 px-2 font-medium"
            >
              +{{ day.tasks.length - 3 }} more
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Day Tasks Modal -->
    <div
      v-if="selectedDay"
      class="fixed inset-0 bg-gray-900 bg-opacity-50 dark:bg-opacity-80 flex items-center justify-center z-50"
      @click.self="selectedDay = null"
    >
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-hidden">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Tasks for {{ formatDate(selectedDay.date) }}
          </h3>
          <button
            @click="selectedDay = null"
            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center dark:hover:bg-gray-700 dark:hover:text-white"
          >
            <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
          </button>
        </div>

        <!-- Modal Body -->
        <div class="p-4 overflow-y-auto max-h-[calc(80vh-8rem)]">
          <div class="space-y-3">
            <div
              v-for="task in selectedDay.tasks"
              :key="task.id"
              class="block p-4 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600"
              :class="getPriorityBorderClass(task.priority)"
            >
              <div class="flex items-start justify-between mb-2">
                <h4 class="text-base font-semibold text-gray-900 dark:text-white">
                  {{ task.title }}
                </h4>
                <span
                  :class="[
                    'text-xs font-medium px-2.5 py-0.5 rounded',
                    getStatusClasses(task.status)
                  ]"
                >
                  {{ task.status_label }}
                </span>
              </div>

              <p v-if="task.description" class="text-sm text-gray-700 dark:text-gray-300 mb-2">
                {{ task.description }}
              </p>

              <div class="flex items-center flex-wrap gap-2 text-xs">
                <span :class="[getPriorityClasses(task.priority), 'px-2.5 py-0.5 rounded font-medium']">
                  {{ task.priority_label }}
                </span>
                <span v-if="task.assigned_user" class="text-gray-500 dark:text-gray-400">
                  Assigned to: {{ task.assigned_user.name }}
                </span>
                <span v-if="task.is_overdue" class="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 px-2.5 py-0.5 rounded font-medium">
                  Overdue
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex items-center justify-end p-4 border-t border-gray-200 dark:border-gray-700">
          <button
            @click="selectedDay = null"
            type="button"
            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TaskCalendar',
  props: {
    tasks: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      currentMonth: new Date().getMonth(),
      currentYear: new Date().getFullYear(),
      selectedDay: null,
      dayHeaders: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
    }
  },
  computed: {
    monthName() {
      const months = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
      ]
      return months[this.currentMonth]
    },
    calendarDays() {
      const firstDay = new Date(this.currentYear, this.currentMonth, 1)
      const lastDay = new Date(this.currentYear, this.currentMonth + 1, 0)

      // Get the day of week (0 = Sunday, 1 = Monday, etc.)
      // Adjust so Monday is 0
      let firstDayOfWeek = firstDay.getDay()
      firstDayOfWeek = firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1

      const daysInMonth = lastDay.getDate()
      const days = []

      // Previous month days
      const prevMonthLastDay = new Date(this.currentYear, this.currentMonth, 0).getDate()
      for (let i = firstDayOfWeek - 1; i >= 0; i--) {
        const date = new Date(this.currentYear, this.currentMonth - 1, prevMonthLastDay - i)
        days.push({
          date,
          isCurrentMonth: false,
          isToday: this.isToday(date),
          tasks: this.getTasksForDate(date)
        })
      }

      // Current month days
      for (let i = 1; i <= daysInMonth; i++) {
        const date = new Date(this.currentYear, this.currentMonth, i)
        days.push({
          date,
          isCurrentMonth: true,
          isToday: this.isToday(date),
          tasks: this.getTasksForDate(date)
        })
      }

      // Next month days to fill the grid
      const remainingDays = 42 - days.length // 6 weeks * 7 days
      for (let i = 1; i <= remainingDays; i++) {
        const date = new Date(this.currentYear, this.currentMonth + 1, i)
        days.push({
          date,
          isCurrentMonth: false,
          isToday: this.isToday(date),
          tasks: this.getTasksForDate(date)
        })
      }

      return days
    }
  },
  methods: {
    previousMonth() {
      if (this.currentMonth === 0) {
        this.currentMonth = 11
        this.currentYear--
      } else {
        this.currentMonth--
      }
    },
    nextMonth() {
      if (this.currentMonth === 11) {
        this.currentMonth = 0
        this.currentYear++
      } else {
        this.currentMonth++
      }
    },
    isToday(date) {
      const today = new Date()
      return (
        date.getDate() === today.getDate() &&
        date.getMonth() === today.getMonth() &&
        date.getFullYear() === today.getFullYear()
      )
    },
    getTasksForDate(date) {
      return this.tasks.filter(task => {
        if (!task.due_date) return false
        const taskDate = new Date(task.due_date)
        return (
          taskDate.getDate() === date.getDate() &&
          taskDate.getMonth() === date.getMonth() &&
          taskDate.getFullYear() === date.getFullYear()
        )
      })
    },
    showTasksForDay(day) {
      this.selectedDay = day
    },
    formatDate(date) {
      const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
      return date.toLocaleDateString('en-US', options)
    },
    getPriorityClasses(priority) {
      const classes = {
        urgent: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        high: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
        medium: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        low: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
      }
      return classes[priority] || classes.medium
    },
    getPriorityBorderClass(priority) {
      const classes = {
        urgent: 'border-l-4 !border-l-red-600 dark:!border-l-red-400',
        high: 'border-l-4 !border-l-purple-600 dark:!border-l-purple-400',
        medium: 'border-l-4 !border-l-blue-500 dark:!border-l-blue-400',
        low: 'border-l-4 !border-l-green-500 dark:!border-l-green-400'
      }
      return classes[priority] || ''
    },
    getStatusClasses(status) {
      const classes = {
        pending: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        in_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
      }
      return classes[status] || classes.pending
    }
  }
}
</script>
