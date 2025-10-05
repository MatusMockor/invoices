<template>
  <div class="modal-backdrop" @click.self="$emit('close')">
    <div class="modal-content modal-content-sm" @click.stop>
      <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Edit Contact</h3>
          <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="updateContact" class="space-y-6">
          <!-- Basic Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">First Name *</label>
              <input v-model="form.first_name" type="text" id="first_name" required
                     class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div>
              <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Name *</label>
              <input v-model="form.last_name" type="text" id="last_name" required
                     class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="primary_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Primary Email</label>
              <input v-model="form.primary_email" type="email" id="primary_email"
                     class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div>
              <label for="primary_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Primary Phone</label>
              <input v-model="form.primary_phone" type="text" id="primary_phone"
                     class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
          </div>

          <div>
            <label for="job_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Job Title</label>
            <input v-model="form.job_title" type="text" id="job_title"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
          </div>

          <div>
            <label for="company_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company *</label>
            <select v-model="form.company_id" id="company_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
              <option value="">Select Company</option>
              <option v-for="company in companies" :key="company.id" :value="company.id">{{ company.name }}</option>
            </select>
          </div>

          <!-- Notes -->
          <div>
            <div class="flex justify-between items-center mb-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
              <button type="button" @click="addNote" class="text-sm bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700">
                Add Note
              </button>
            </div>
            <div v-for="(note, index) in form.notes" :key="index" class="mb-3 p-3 border border-gray-300 dark:border-gray-600 rounded-md">
              <div class="flex justify-between items-start mb-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Note {{ index + 1 }}</label>
                <button type="button" @click="removeNote(index)" class="text-red-600 hover:text-red-800 text-sm">
                  Remove
                </button>
              </div>
              <textarea 
                v-model="form.notes[index].content" 
                :placeholder="'Enter note content...'"
                rows="3" 
                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
              </textarea>
            </div>
            <div v-if="form.notes.length === 0" class="text-gray-500 text-sm italic">
              No notes added yet. Click "Add Note" to add one.
            </div>
          </div>

          <!-- Tags -->
          <div>
            <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tags</label>
            <input v-model="form.tags" type="text" id="tags" placeholder="Enter tags separated by commas"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
          </div>

          <div class="flex items-center">
            <input v-model="form.is_active" type="checkbox" id="is_active" value="1"
                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Active</label>
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-3 pt-4">
            <button type="button" @click="$emit('close')" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              Cancel
            </button>
            <button type="submit" :disabled="loading" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
              {{ loading ? 'Updating...' : 'Update Contact' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ContactEditModal',
  props: {
    contact: {
      type: Object,
      required: true
    },
    routes: {
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
      companies: [],
      form: {
        first_name: '',
        last_name: '',
        primary_email: '',
        primary_phone: '',
        job_title: '',
        company_id: '',
        notes: [],
        tags: '',
        is_active: true
      }
    }
  },
  async mounted() {
    await this.fetchCompanies()
    this.initializeForm()
  },
  methods: {
    async fetchCompanies() {
      try {
        const response = await fetch(this.routes.companies, {
          headers: { 
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        })
        if (response.ok) {
          this.companies = await response.json()
        }
      } catch (error) {
        console.error('Error fetching companies:', error)
      }
    },
    
    initializeForm() {
      console.log('Contact data:', this.contact)
      console.log('Contact company_id:', this.contact.company_id)
      
      // Initialize notes array
      let notes = []
      if (this.contact.notes && Array.isArray(this.contact.notes)) {
        // If notes is already an array (from API)
        notes = this.contact.notes.map(note => ({
          id: note.id || null,
          content: note.content || note.note || '',
          created_at: note.created_at || null
        }))
      } else if (this.contact.notes && typeof this.contact.notes === 'string') {
        // If notes is a string (legacy field)
        notes = [{ id: null, content: this.contact.notes, created_at: null }]
      }
      
      this.form = {
        first_name: this.contact.first_name || '',
        last_name: this.contact.last_name || '',
        primary_email: this.contact.primary_email || '',
        primary_phone: this.contact.primary_phone || '',
        job_title: this.contact.job_title || '',
        company_id: this.contact.company_id || '',
        notes: notes,
        tags: this.contact.tags ? this.contact.tags.map(tag => tag.name).join(', ') : '',
        is_active: this.contact.is_active
      }
      
      console.log('Form initialized with company_id:', this.form.company_id)
      console.log('Form initialized with notes:', this.form.notes)
    },
    
    addNote() {
      this.form.notes.push({
        id: null,
        content: '',
        created_at: null
      })
    },
    
    removeNote(index) {
      this.form.notes.splice(index, 1)
    },
    
    async updateContact() {
      this.loading = true
      try {
        const url = this.routes.update.replace(':id', this.contact.id)
        const response = await fetch(url, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.csrfToken
          },
          body: JSON.stringify({
            ...this.form,
            tags: this.form.tags ? this.form.tags.split(',').map(tag => tag.trim()) : []
          })
        })
        
        if (response.ok) {
          this.$emit('updated')
        } else {
          const errors = await response.json()
          console.error('Validation errors:', errors)
        }
      } catch (error) {
        console.error('Error updating contact:', error)
      } finally {
        this.loading = false
      }
    }
  }
}
</script>
