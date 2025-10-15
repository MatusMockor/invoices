<template>
  <div>
    <main>
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <TasksIndex
              :api-routes="apiRoutes"
              :csrf-token="csrfToken"
            />
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import TasksIndex from './TasksIndex.vue'

export default {
  name: 'TaskManagementModule',
  components: {
    TasksIndex
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
  }
}
</script>
