<template>
  <div class="modal-backdrop" @click.self="$emit('close')">
    <div class="modal-content modal-content-sm" @click.stop>
      <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Edit Task</h3>
          <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div>
            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title *</label>
            <input
              v-model="form.title"
              type="text"
              id="title"
              required
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
            <textarea
              v-model="form.description"
              id="description"
              rows="3"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            ></textarea>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
              <select
                v-model="form.status"
                id="status"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              >
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
                <option value="on_hold">On Hold</option>
              </select>
            </div>

            <div>
              <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
              <select
                v-model="form.priority"
                id="priority"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              >
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>
          </div>

          <div>
            <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Due Date</label>
            <input
              v-model="form.due_date"
              type="date"
              id="due_date"
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-600 mt-8">
            <button
              type="button"
              @click="$emit('close')"
              class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
            >
              {{ loading ? 'Updating...' : 'Update Task' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TaskEditModal',
  props: {
    task: {
      type: Object,
      required: true
    },
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
      loading: false,
      form: {
        title: this.task.title,
        description: this.task.description || '',
        status: this.task.status,
        priority: this.task.priority,
        due_date: this.task.due_date ? this.task.due_date.split('T')[0] : ''
      }
    }
  },
  methods: {
    async handleSubmit() {
      this.loading = true
      try {
        await fetch(this.apiRoutes.update.replace(':id', this.task.id), {
          method: 'PUT',
          headers: {
            'X-CSRF-TOKEN': this.csrfToken,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(this.form)
        })
        this.$emit('updated')
      } catch (error) {
        console.error('Error updating task:', error)
      } finally {
        this.loading = false
      }
    }
  }
}
</script>
