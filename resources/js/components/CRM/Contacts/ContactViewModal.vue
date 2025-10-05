<template>
  <div class="modal-backdrop" @click.self="$emit('close')">
    <div class="modal-content modal-content-lg" @click.stop>
      <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8 pb-4 border-b border-gray-200 dark:border-gray-600">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ contact?.full_name }}</h3>
          <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <div v-if="contact" class="space-y-8">
          <!-- Main Content -->
          <div class="space-y-8">
            <!-- Basic Information -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
              <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Basic Information</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">First Name</label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ contact.first_name }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Last Name</label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ contact.last_name }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Job Title</label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ contact.job_title || 'N/A' }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Company</label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ contact.company?.name || 'N/A' }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                  <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="contact.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                    {{ contact.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Last Contacted</label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ formatDate(contact.last_contacted_at) }}</p>
                </div>
              </div>
              <div v-if="contact.notes" class="mt-4">
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Notes</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ contact.notes }}</p>
              </div>
            </div>

            <!-- Contact Methods -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
              <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Contact Methods</h4>
              
              <!-- Emails -->
              <div class="mb-4">
                <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Emails</h5>
                <div class="space-y-2">
                  <div v-if="contact.primary_email" class="flex items-center space-x-2">
                    <span class="text-sm text-gray-900 dark:text-gray-100">{{ contact.primary_email }}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Primary</span>
                  </div>
                  <div v-for="email in contact.emails" :key="email.id" class="flex items-center space-x-2">
                    <span class="text-sm text-gray-900 dark:text-gray-100">{{ email.email }}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">{{ email.type }}</span>
                    <span v-if="email.is_verified" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Verified</span>
                  </div>
                </div>
              </div>

              <!-- Phones -->
              <div>
                <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phones</h5>
                <div class="space-y-2">
                  <div v-if="contact.primary_phone" class="flex items-center space-x-2">
                    <span class="text-sm text-gray-900 dark:text-gray-100">{{ contact.primary_phone }}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Primary</span>
                  </div>
                  <div v-for="phone in contact.phones" :key="phone.id" class="flex items-center space-x-2">
                    <span class="text-sm text-gray-900 dark:text-gray-100">{{ phone.full_phone }}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">{{ phone.type }}</span>
                    <span v-if="phone.is_verified" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Verified</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Addresses -->
            <div v-if="contact.addresses && contact.addresses.length > 0" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
              <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Addresses</h4>
              <div class="space-y-4">
                <div v-for="address in contact.addresses" :key="address.id" class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                  <div class="flex justify-between items-start">
                    <div>
                      <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ address.type }} Address</h5>
                      <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ address.full_address }}</p>
                    </div>
                    <span v-if="address.is_primary" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Primary</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Custom Fields -->
            <div v-if="contact.custom_field_values && contact.custom_field_values.length > 0" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
              <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Custom Fields</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="fieldValue in contact.custom_field_values" :key="fieldValue.id">
                  <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">{{ fieldValue.field_definition?.name }}</label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ fieldValue.value }}</p>
                </div>
              </div>
            </div>

            <!-- Tags -->
            <div v-if="contact.tags && contact.tags.length > 0" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
              <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Tags</h4>
              <div class="flex flex-wrap gap-2">
                <span v-for="tag in contact.tags" :key="tag.id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :style="{ backgroundColor: tag.color + '20', color: tag.color }">
                  {{ tag.name }}
                </span>
              </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
              <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Recent Activities</h4>
              <div class="space-y-3">
                <div v-for="activity in contact.activities?.slice(0, 5)" :key="activity.id" class="flex items-start space-x-3">
                  <div class="flex-shrink-0">
                    <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                      <span class="text-xs font-medium text-indigo-600">{{ getInitial(activity.user?.name) }}</span>
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-900 dark:text-gray-100">{{ activity.description }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(activity.occurred_at) }}</p>
                  </div>
                </div>
                <p v-if="!contact.activities || contact.activities.length === 0" class="text-sm text-gray-500 dark:text-gray-400">No recent activities</p>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
              <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <button class="flex items-center px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-md transition-colors duration-200">
                  <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                  </svg>
                  Log Call
                </button>
                <button class="flex items-center px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-md transition-colors duration-200">
                  <svg class="w-5 h-5 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                  </svg>
                  Send Email
                </button>
                <button class="flex items-center px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-md transition-colors duration-200">
                  <svg class="w-5 h-5 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                  Schedule Meeting
                </button>
                <button class="flex items-center px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-md transition-colors duration-200">
                  <svg class="w-5 h-5 mr-3 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                  Add Note
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ContactViewModal',
  props: {
    contact: {
      type: Object,
      required: true
    }
  },
  methods: {
    formatDate(date) {
      if (!date) return 'Never'
      return new Date(date).toLocaleDateString()
    },
    
    getInitial(name) {
      return name ? name.charAt(0).toUpperCase() : 'S'
    }
  }
}
</script>
