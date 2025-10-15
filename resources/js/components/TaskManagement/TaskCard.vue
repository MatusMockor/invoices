<template>
  <div class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700" :class="priorityBorderClass">
    <div>
      <div class="flex justify-between items-start mb-4">
        <div class="flex-1">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ task.title }}</h3>
          <p v-if="task.description" class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ task.description }}</p>
        </div>
        <div class="flex items-center space-x-2 ml-4">
          <button
            @click="$emit('edit', task)"
            class="focus:outline-none text-white bg-indigo-700 hover:bg-indigo-800 focus:ring-4 focus:ring-indigo-300 font-medium rounded-lg text-sm px-4 py-2.5 dark:bg-indigo-600 dark:hover:bg-indigo-700 dark:focus:ring-indigo-800 inline-flex items-center"
            title="Edit Task"
          >
            <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Edit
          </button>
          <button
            @click="$emit('delete', task.id)"
            class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2.5 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900 inline-flex items-center"
            title="Delete Task"
          >
            <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
            Delete
          </button>
        </div>
      </div>

      <div class="flex flex-wrap gap-2 mb-4">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="statusColor">
          {{ task.status_label }}
        </span>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="priorityBadgeColor">
          {{ task.priority_label }}
        </span>
        <span v-if="task.due_date" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
          Due: {{ formatDate(task.due_date) }}
        </span>
        <span v-if="task.is_overdue" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
          Overdue
        </span>
      </div>

      <div v-if="task.assigned_user" class="text-sm text-gray-600 dark:text-gray-300 mb-4">
        Assigned to: <span class="font-medium text-gray-900 dark:text-white">{{ task.assigned_user.name }}</span>
      </div>

      <div class="flex flex-wrap gap-2">
        <button
          v-if="task.status !== 'completed'"
          @click="$emit('status-change', { taskId: task.id, status: 'completed' })"
          class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition ease-in-out duration-150"
        >
          Mark Complete
        </button>
        <button
          v-if="task.status === 'pending'"
          @click="$emit('status-change', { taskId: task.id, status: 'in_progress' })"
          class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150"
        >
          Start
        </button>
        <button
          v-if="task.status !== 'cancelled' && task.status !== 'completed'"
          @click="$emit('status-change', { taskId: task.id, status: 'cancelled' })"
          class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-600 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150"
        >
          Cancel
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TaskCard',
  props: {
    task: {
      type: Object,
      required: true
    }
  },
  computed: {
    priorityBorderClass() {
      const colors = {
        urgent: 'border-l-4 !border-l-red-600 dark:!border-l-red-400',
        high: 'border-l-4 !border-l-purple-600 dark:!border-l-purple-400',
        medium: 'border-l-4 !border-l-blue-500 dark:!border-l-blue-400',
        low: 'border-l-4 !border-l-green-500 dark:!border-l-green-400'
      }
      return colors[this.task.priority] || ''
    },
    priorityBadgeColor() {
      const colors = {
        urgent: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        high: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
        medium: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        low: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
      }
      return colors[this.task.priority] || 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300'
    },
    statusColor() {
      const colors = {
        pending: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        in_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        on_hold: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
      }
      return colors[this.task.status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
    }
  },
  methods: {
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString()
    }
  }
}
</script>
