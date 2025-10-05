<template>
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        CRM Contacts
      </h2>
      <div style="display: flex; gap: 12px;">
        <button @click="showImportModal = true" style="background-color: #10b981; color: white; padding: 8px 16px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
          📥 Import
        </button>
        <button @click="exportContacts" style="background-color: #3b82f6; color: white; padding: 8px 16px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
          📤 Export
        </button>
        <button @click="showCreateModal = true" style="background-color: #8b5cf6; color: white; padding: 8px 16px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
          ➕ Add Contact
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6">
        <form @submit.prevent="applyFilters" class="flex flex-wrap gap-4">
          <div class="flex-1 min-w-0">
            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
            <input v-model="filters.search" type="text" id="search" 
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
          </div>
          <div class="flex-1 min-w-0">
            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
            <select v-model="filters.status" id="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
              <option value="">All</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div class="flex-1 min-w-0">
            <label for="tag" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tag</label>
            <select v-model="filters.tag" id="tag" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
              <option value="">All Tags</option>
              <option v-for="tag in tags" :key="tag.id" :value="tag.name">{{ tag.name }}</option>
            </select>
          </div>
          <div class="flex items-end">
            <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
              Filter
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Contacts Table -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 text-gray-900 dark:text-gray-100">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Phone</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Company</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tags</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last Contacted</th>
              <th class="px-6 py-3"></th>
            </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="contact in contacts.data" :key="contact.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="px-6 py-4 whitespace-nowrap">
                <input type="checkbox" v-model="selectedContacts" :value="contact.id" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center">
                      <span class="text-sm font-medium text-white">{{ getInitials(contact.first_name, contact.last_name) }}</span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ contact.full_name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ contact.job_title }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ contact.primary_email }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ contact.primary_phone }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ contact.company?.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex flex-wrap gap-1">
                  <span v-for="tag in contact.tags" :key="tag.id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :style="{ backgroundColor: tag.color + '20', color: tag.color }">
                    {{ tag.name }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="contact.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                  {{ contact.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ formatDate(contact.last_contacted_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end space-x-2">
                  <button @click="viewContact(contact)" class="inline-flex items-center p-2 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-md transition-colors duration-200" title="View Contact">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                  <button @click="editContact(contact)" class="inline-flex items-center p-2 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-md transition-colors duration-200" title="Edit Contact">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>
                  <button @click="deleteContact(contact)" class="inline-flex items-center p-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-md transition-colors duration-200" title="Delete Contact">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Bulk Actions -->
        <div class="mt-4 flex justify-between items-center">
          <div class="flex space-x-2">
            <button @click="showBulkUpdateModal = true" :disabled="selectedContacts.length === 0" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
              Bulk Update
            </button>
            <button @click="bulkDelete" :disabled="selectedContacts.length === 0" class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
              Bulk Delete
            </button>
          </div>
          <div v-if="contacts.links">
            <nav class="flex items-center space-x-2">
              <button v-for="link in contacts.links" :key="link.label" @click="loadPage(link.url)" :disabled="!link.url" :class="link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'" class="px-3 py-2 text-sm font-medium rounded-md border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed">
                <span v-html="link.label"></span>
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <ContactCreateModal 
      v-if="showCreateModal" 
      :routes="routes"
      :csrf-token="csrfToken"
      @close="showCreateModal = false" 
      @created="contactCreated" 
    />
    <ContactEditModal 
      v-if="showEditModal" 
      :contact="editingContact" 
      :routes="routes"
      :csrf-token="csrfToken"
      @close="showEditModal = false" 
      @updated="contactUpdated" 
    />
    <ContactViewModal 
      v-if="showViewModal" 
      :contact="viewingContact" 
      @close="showViewModal = false" 
    />
    <ContactImportModal 
      v-if="showImportModal" 
      :routes="routes"
      :csrf-token="csrfToken"
      @close="showImportModal = false" 
      @imported="contactsImported" 
    />
    <BulkUpdateModal 
      v-if="showBulkUpdateModal" 
      :contact-ids="selectedContacts" 
      :routes="routes"
      :csrf-token="csrfToken"
      @close="showBulkUpdateModal = false" 
      @updated="bulkUpdated" 
    />
  </div>
</template>

<script>
import ContactCreateModal from './ContactCreateModal.vue'
import ContactEditModal from './ContactEditModal.vue'
import ContactViewModal from './ContactViewModal.vue'
import ContactImportModal from './ContactImportModal.vue'
import BulkUpdateModal from './BulkUpdateModal.vue'
export default {
  name: 'ContactsIndex',
  components: {
    ContactCreateModal,
    ContactEditModal,
    ContactViewModal,
    ContactImportModal,
    BulkUpdateModal
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
      contacts: { data: [], links: [] },
      tags: [],
      selectedContacts: [],
      selectAll: false,
      filters: {
        search: '',
        status: '',
        tag: ''
      },
      showCreateModal: false,
      showEditModal: false,
      showViewModal: false,
      showImportModal: false,
      showBulkUpdateModal: false,
      editingContact: null,
      viewingContact: null
    }
  },
  mounted() {
    this.fetchContacts()
    this.fetchTags()
  },
  methods: {
    async fetchContacts() {
      try {
        const params = new URLSearchParams()
        if (this.filters.search) params.append('search', this.filters.search)
        if (this.filters.status) params.append('status', this.filters.status)
        if (this.filters.tag) params.append('tag', this.filters.tag)
        
        const response = await fetch(`${this.routes.index}?${params.toString()}`, {
          headers: { 
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        })
        
        if (response.ok) {
          this.contacts = await response.json()
        }
      } catch (error) {
        console.error('Error fetching contacts:', error)
      }
    },
    
    async fetchTags() {
      try {
        const response = await fetch(this.tagsRoute, {
          headers: { 
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        })
        
        if (response.ok) {
          const data = await response.json()
          this.tags = data.data || data
        }
      } catch (error) {
        console.error('Error fetching tags:', error)
      }
    },
    
    applyFilters() {
      this.fetchContacts()
    },
    
    toggleSelectAll() {
      if (this.selectAll) {
        this.selectedContacts = this.contacts.data.map(contact => contact.id)
      } else {
        this.selectedContacts = []
      }
    },
    
    viewContact(contact) {
      this.viewingContact = contact
      this.showViewModal = true
    },
    
    async editContact(contact) {
      try {
        // Fetch full contact data with notes
        const response = await fetch(`${this.routes.show.replace(':id', contact.id)}`, {
          headers: { 
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        })
        
        if (response.ok) {
          const data = await response.json()
          this.editingContact = data.data
          this.showEditModal = true
        } else {
          console.error('Error fetching contact details')
          // Fallback to basic contact data
          this.editingContact = contact
          this.showEditModal = true
        }
      } catch (error) {
        console.error('Error fetching contact details:', error)
        // Fallback to basic contact data
        this.editingContact = contact
        this.showEditModal = true
      }
    },
    
    async deleteContact(contact) {
      if (confirm('Are you sure you want to delete this contact?')) {
        try {
          const url = this.routes.destroy.replace(':id', contact.id)
          const response = await fetch(url, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': this.csrfToken,
              'Accept': 'application/json'
            }
          })
          
          if (response.ok) {
            this.fetchContacts()
          }
        } catch (error) {
          console.error('Error deleting contact:', error)
        }
      }
    },
    
    async bulkDelete() {
      if (confirm(`Are you sure you want to delete ${this.selectedContacts.length} contacts?`)) {
        try {
          const response = await fetch(this.routes.bulkDelete, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': this.csrfToken,
              'Accept': 'application/json'
            },
            body: JSON.stringify({ contact_ids: this.selectedContacts })
          })
          
          if (response.ok) {
            this.selectedContacts = []
            this.selectAll = false
            this.fetchContacts()
          }
        } catch (error) {
          console.error('Error bulk deleting contacts:', error)
        }
      }
    },
    
    async exportContacts() {
      try {
        const response = await fetch(this.routes.export, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.csrfToken,
            'Accept': 'application/json'
          },
          body: JSON.stringify({ contact_ids: this.selectedContacts })
        })
        
        if (response.ok) {
          const result = await response.json()
          window.open(result.download_url, '_blank')
        }
      } catch (error) {
        console.error('Error exporting contacts:', error)
      }
    },
    
    contactCreated() {
      this.showCreateModal = false
      this.fetchContacts()
    },
    
    contactUpdated() {
      this.showEditModal = false
      this.fetchContacts()
    },
    
    contactsImported() {
      this.showImportModal = false
      this.fetchContacts()
    },
    
    bulkUpdated() {
      this.showBulkUpdateModal = false
      this.selectedContacts = []
      this.selectAll = false
      this.fetchContacts()
    },
    
    loadPage(url) {
      if (url) {
        window.location.href = url
      }
    },
    
    getInitials(firstName, lastName) {
      return (firstName?.charAt(0) || '') + (lastName?.charAt(0) || '')
    },
    
    formatDate(date) {
      if (!date) return 'Never'
      return new Date(date).toLocaleDateString()
    }
  }
}
</script>
