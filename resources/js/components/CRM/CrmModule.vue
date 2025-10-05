<template>
  <div>
    <!-- Main Content -->
    <main>
      <!-- Contacts Tab -->
      <div v-if="activeTab === 'contacts'">
        <ContactsIndex 
          :routes="routes"
          :tags-route="tagsRoute"
          :csrf-token="csrfToken"
        />
      </div>

      <!-- Companies Tab -->
      <div v-else-if="activeTab === 'companies'" class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <h2 class="text-lg font-medium mb-4">Companies</h2>
            <p class="text-gray-500 dark:text-gray-400">Companies management will be implemented here.</p>
          </div>
        </div>
      </div>

      <!-- Deals Tab -->
      <div v-else-if="activeTab === 'deals'" class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <h2 class="text-lg font-medium mb-4">Deals</h2>
            <p class="text-gray-500 dark:text-gray-400">Deals management will be implemented here.</p>
          </div>
        </div>
      </div>

      <!-- Analytics Tab -->
      <div v-else-if="activeTab === 'analytics'" class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <h2 class="text-lg font-medium mb-4">Analytics</h2>
            <p class="text-gray-500 dark:text-gray-400">Analytics dashboard will be implemented here.</p>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import ContactsIndex from './Contacts/ContactsIndex.vue'

export default {
  name: 'CrmModule',
  components: {
    ContactsIndex
  },
  props: {
    routes: {
      type: Object,
      required: true
    },
    tagsRoute: {
      type: String,
      required: true
    },
    csrfToken: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      activeTab: 'contacts'
    }
  }
}
</script>
