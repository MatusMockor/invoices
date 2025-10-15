<template>
  <div class="p-4 sm:p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Tasks & Follow-ups
      </h2>
      <div class="flex items-center gap-3">
        <!-- View Toggle -->
        <div class="inline-flex rounded-lg shadow-sm" role="group">
          <button
            @click="currentView = 'list'"
            type="button"
            :class="[
              'px-4 py-2 text-sm font-medium rounded-l-lg border',
              currentView === 'list'
                ? 'bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700'
                : 'bg-white text-gray-900 border-gray-200 hover:bg-gray-100 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600'
            ]"
          >
            <svg class="w-4 h-4 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
            </svg>
            List
          </button>
          <button
            @click="currentView = 'calendar'"
            type="button"
            :class="[
              'px-4 py-2 text-sm font-medium rounded-r-lg border-t border-r border-b',
              currentView === 'calendar'
                ? 'bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700'
                : 'bg-white text-gray-900 border-gray-200 hover:bg-gray-100 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600'
            ]"
          >
            <svg class="w-4 h-4 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
            </svg>
            Calendar
          </button>
        </div>

        <button
          @click="openCreateModal"
          class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
        >
          Create Task
        </button>
      </div>
    </div>

    <!-- Filters (only show in list view) -->
    <div v-if="currentView === 'list'" class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6">
        <div class="flex flex-wrap gap-4">
          <div class="flex-1 min-w-0">
            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
            <input
              v-model="searchQuery"
              @input="handleSearch"
              type="text"
              id="search"
              placeholder="Search tasks..."
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div class="flex-1 min-w-0">
            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
            <select
              v-model="selectedStatus"
              @change="handleFilterChange"
              id="status"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
              <option value="on_hold">On Hold</option>
            </select>
          </div>

          <div class="flex-1 min-w-0">
            <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
            <select
              v-model="selectedPriority"
              @change="handleFilterChange"
              id="priority"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="">All Priorities</option>
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Tasks List -->
    <div v-if="currentView === 'list'" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 text-gray-900 dark:text-gray-100">
        <div v-if="loading" class="text-center py-8">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>

        <div v-else-if="tasks.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
          No tasks found.
        </div>

        <div v-else class="space-y-4">
          <TaskCard
            v-for="task in tasks"
            :key="task.id"
            :task="task"
            @edit="openEditModal"
            @delete="handleDelete"
            @status-change="handleStatusChange"
          />
        </div>
      </div>
    </div>

    <!-- Calendar View -->
    <div v-if="currentView === 'calendar'">
      <TaskCalendar
        :tasks="allTasks"
        @create-task="handleCreateTaskFromCalendar"
      />
    </div>

    <TaskCreateModal
      v-if="showCreateModal"
      :api-routes="apiRoutes"
      :csrf-token="csrfToken"
      :initial-due-date="selectedDueDate"
      @close="closeCreateModal"
      @created="handleTaskCreated"
    />

    <TaskEditModal
      v-if="showEditModal && selectedTask"
      :task="selectedTask"
      :api-routes="apiRoutes"
      :csrf-token="csrfToken"
      @close="closeEditModal"
      @updated="handleTaskUpdated"
    />
    </div>
  </div>
</template>

<script>
import TaskCard from './TaskCard.vue'
import TaskCreateModal from './TaskCreateModal.vue'
import TaskEditModal from './TaskEditModal.vue'
import TaskCalendar from './TaskCalendar.vue'

export default {
  name: 'TasksIndex',
  components: {
    TaskCard,
    TaskCreateModal,
    TaskEditModal,
    TaskCalendar
  },
  props: {
    apiRoutes: {
      type: Object,
      required: true
    },
    csrfToken: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      tasks: [],
      allTasks: [],
      loading: false,
      searchQuery: '',
      selectedStatus: '',
      selectedPriority: '',
      showCreateModal: false,
      showEditModal: false,
      selectedTask: null,
      searchTimeout: null,
      currentView: 'list',
      selectedDueDate: ''
    }
  },
  watch: {
    currentView(newView) {
      if (newView === 'calendar') {
        this.fetchAllTasks()
      }
    }
  },
  mounted() {
    this.fetchTasks()
  },
  methods: {
    async fetchTasks() {
      this.loading = true
      try {
        const params = new URLSearchParams()
        if (this.searchQuery) params.append('search', this.searchQuery)
        if (this.selectedStatus) params.append('status', this.selectedStatus)
        if (this.selectedPriority) params.append('priority', this.selectedPriority)

        const response = await fetch(`${this.apiRoutes.index}?${params}`)
        const data = await response.json()
        this.tasks = data.data
      } catch (error) {
        console.error('Error fetching tasks:', error)
      } finally {
        this.loading = false
      }
    },
    async fetchAllTasks() {
      try {
        const response = await fetch(this.apiRoutes.calendar)
        const data = await response.json()
        this.allTasks = data.data
      } catch (error) {
        console.error('Error fetching calendar tasks:', error)
      }
    },
    handleSearch() {
      clearTimeout(this.searchTimeout)
      this.searchTimeout = setTimeout(() => {
        this.fetchTasks()
      }, 300)
    },
    handleFilterChange() {
      this.fetchTasks()
    },
    openCreateModal() {
      this.selectedDueDate = ''
      this.showCreateModal = true
    },
    closeCreateModal() {
      this.showCreateModal = false
      this.selectedDueDate = ''
    },
    handleCreateTaskFromCalendar(data) {
      this.selectedDueDate = data.dueDate
      this.showCreateModal = true
    },
    openEditModal(task) {
      this.selectedTask = task
      this.showEditModal = true
    },
    closeEditModal() {
      this.showEditModal = false
      this.selectedTask = null
    },
    handleTaskCreated() {
      this.closeCreateModal()
      if (this.currentView === 'calendar') {
        this.fetchAllTasks()
      } else {
        this.fetchTasks()
      }
    },
    handleTaskUpdated() {
      this.closeEditModal()
      this.fetchTasks()
    },
    async handleDelete(taskId) {
      if (!confirm('Are you sure you want to delete this task?')) return

      try {
        await fetch(`${this.apiRoutes.destroy.replace(':id', taskId)}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': this.csrfToken,
            'Content-Type': 'application/json'
          }
        })
        this.fetchTasks()
      } catch (error) {
        console.error('Error deleting task:', error)
      }
    },
    async handleStatusChange({ taskId, status }) {
      try {
        const endpoint = status === 'completed'
          ? this.apiRoutes.complete
          : status === 'in_progress'
          ? this.apiRoutes.inProgress
          : this.apiRoutes.cancel

        await fetch(endpoint.replace(':id', taskId), {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': this.csrfToken,
            'Content-Type': 'application/json'
          }
        })
        this.fetchTasks()
      } catch (error) {
        console.error('Error updating task status:', error)
      }
    }
  }
}
</script>
