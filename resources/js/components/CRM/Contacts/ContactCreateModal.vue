<template>
  <div class="modal-backdrop" @click.self="$emit('close')">
    <div class="modal-content modal-content-sm" @click.stop>
      <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Create New Contact</h3>
          <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="createContact" class="space-y-6">
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

          <!-- Additional Emails -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Additional Emails</label>
            <div class="mt-2 space-y-2">
              <div v-for="(email, index) in form.emails" :key="index" class="flex space-x-2">
                <input v-model="email.email" type="email" placeholder="Email" required
                       class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <select v-model="email.type" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  <option value="secondary">Secondary</option>
                  <option value="work">Work</option>
                  <option value="personal">Personal</option>
                </select>
                <button type="button" @click="removeEmail(index)" class="text-red-600 hover:text-red-800">Remove</button>
              </div>
            </div>
            <button type="button" @click="addEmail" class="mt-2 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              Add Email
            </button>
          </div>

          <!-- Additional Phones -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Additional Phones</label>
            <div class="mt-2 space-y-2">
              <div v-for="(phone, index) in form.phones" :key="index" class="flex space-x-2">
                <input v-model="phone.phone" type="text" placeholder="Phone" required
                       class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <select v-model="phone.type" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  <option value="secondary">Secondary</option>
                  <option value="work">Work</option>
                  <option value="mobile">Mobile</option>
                  <option value="home">Home</option>
                </select>
                <input v-model="phone.country_code" type="text" placeholder="+421" value="+421"
                       class="w-20 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <button type="button" @click="removePhone(index)" class="text-red-600 hover:text-red-800">Remove</button>
              </div>
            </div>
            <button type="button" @click="addPhone" class="mt-2 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              Add Phone
            </button>
          </div>

          <!-- Tags -->
          <div>
            <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tags</label>
            <input v-model="form.tags" type="text" id="tags" placeholder="Enter tags separated by commas"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
          </div>

          <div class="flex items-center">
            <input v-model="form.is_active" type="checkbox" id="is_active" value="1" checked
                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Active</label>
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-600 mt-8">
            <button type="button" @click="$emit('close')" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              Cancel
            </button>
            <button type="submit" :disabled="loading" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
              {{ loading ? 'Creating...' : 'Create Contact' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ContactCreateModal',
  props: {
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
        emails: [],
        phones: [],
        tags: '',
        is_active: true
      }
    }
  },
  mounted() {
    this.fetchCompanies()
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
    
    addNote() {
      this.form.notes.push({
        content: ''
      })
    },
    
    removeNote(index) {
      this.form.notes.splice(index, 1)
    },
    
    async createContact() {
      this.loading = true
      try {
        const response = await fetch(this.routes.store, {
          method: 'POST',
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
          this.$emit('created')
        } else {
          const errors = await response.json()
          console.error('Validation errors:', errors)
        }
      } catch (error) {
        console.error('Error creating contact:', error)
      } finally {
        this.loading = false
      }
    },
    
    addEmail() {
      this.form.emails.push({ email: '', type: 'secondary' })
    },
    
    removeEmail(index) {
      this.form.emails.splice(index, 1)
    },
    
    addPhone() {
      this.form.phones.push({ phone: '', type: 'secondary', country_code: '+421' })
    },
    
    removePhone(index) {
      this.form.phones.splice(index, 1)
    }
  }
}
</script>
