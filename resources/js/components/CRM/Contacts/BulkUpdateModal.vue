<template>
  <div class="modal-backdrop" @click.self="$emit('close')">
    <div class="modal-content modal-content-sm" @click.stop>
      <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Bulk Update Contacts</h3>
          <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Info -->
        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900 rounded-lg">
          <p class="text-sm text-blue-700 dark:text-blue-300">
            You are about to update <strong>{{ contactIds.length }}</strong> selected contacts.
          </p>
        </div>

        <!-- Form -->
        <form @submit.prevent="bulkUpdate" class="space-y-4">
          <!-- Status -->
          <div>
            <label for="is_active" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
            <select v-model="form.is_active" id="is_active" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
              <option value="">Don't change</option>
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>

          <!-- Company -->
          <div>
            <label for="company_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
            <select v-model="form.company_id" id="company_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
              <option value="">Don't change</option>
              <option v-for="company in companies" :key="company.id" :value="company.id">{{ company.name }}</option>
            </select>
          </div>

          <!-- Tags -->
          <div>
            <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tags</label>
            <div class="mt-1 space-y-2">
              <div class="flex items-center">
                <input v-model="tagAction" type="radio" id="add_tags" value="add" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                <label for="add_tags" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Add tags</label>
              </div>
              <div class="flex items-center">
                <input v-model="tagAction" type="radio" id="remove_tags" value="remove" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                <label for="remove_tags" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Remove tags</label>
              </div>
              <div class="flex items-center">
                <input v-model="tagAction" type="radio" id="replace_tags" value="replace" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                <label for="replace_tags" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Replace all tags</label>
              </div>
            </div>
            <input v-if="tagAction" v-model="form.tags" type="text" placeholder="Enter tags separated by commas"
                   class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
          </div>

          <!-- Notes -->
          <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
            <div class="mt-1 space-y-2">
              <div class="flex items-center">
                <input v-model="notesAction" type="radio" id="append_notes" value="append" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                <label for="append_notes" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Append to existing notes</label>
              </div>
              <div class="flex items-center">
                <input v-model="notesAction" type="radio" id="prepend_notes" value="prepend" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                <label for="prepend_notes" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Prepend to existing notes</label>
              </div>
              <div class="flex items-center">
                <input v-model="notesAction" type="radio" id="replace_notes" value="replace" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                <label for="replace_notes" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Replace all notes</label>
              </div>
            </div>
            <textarea v-if="notesAction" v-model="form.notes" rows="3" placeholder="Enter notes"
                      class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
          </div>

          <!-- Custom Fields -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Custom Fields</label>
            <div class="mt-2 space-y-2">
              <div v-for="field in customFields" :key="field.id" class="flex items-center space-x-2">
                <input v-model="form.custom_fields[field.slug]" type="text" :placeholder="field.name"
                       class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <button type="button" @click="removeCustomField(field.slug)" class="text-red-600 hover:text-red-800">Remove</button>
              </div>
            </div>
            <button type="button" @click="addCustomField" class="mt-2 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              Add Custom Field
            </button>
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-3 pt-4">
            <button type="button" @click="$emit('close')" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              Cancel
            </button>
            <button type="submit" :disabled="loading" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
              {{ loading ? 'Updating...' : 'Update Contacts' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BulkUpdateModal',
  props: {
    contactIds: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      loading: false,
      companies: [],
      customFields: [],
      form: {
        is_active: '',
        company_id: '',
        tags: '',
        notes: '',
        custom_fields: {}
      },
      tagAction: '',
      notesAction: ''
    }
  },
  mounted() {
    this.fetchCompanies()
    this.fetchCustomFields()
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
    
    async fetchCustomFields() {
      try {
        const response = await fetch('/crm/custom-fields', {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        if (response.ok) {
          this.customFields = await response.json()
        }
      } catch (error) {
        console.error('Error fetching custom fields:', error)
      }
    },
    
    async bulkUpdate() {
      this.loading = true
      
      try {
        const updateData = { ...this.form }
        
        // Process tags based on action
        if (this.tagAction && this.form.tags) {
          updateData.tag_action = this.tagAction
          updateData.tags = this.form.tags.split(',').map(tag => tag.trim())
        }
        
        // Process notes based on action
        if (this.notesAction && this.form.notes) {
          updateData.notes_action = this.notesAction
        }
        
        // Remove empty values
        Object.keys(updateData).forEach(key => {
          if (updateData[key] === '' || updateData[key] === null || updateData[key] === undefined) {
            delete updateData[key]
          }
        })
        
        const response = await fetch('/crm/contacts/bulk-update', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({
            contact_ids: this.contactIds,
            data: updateData
          })
        })
        
        if (response.ok) {
          this.$emit('updated')
        } else {
          const error = await response.json()
          console.error('Bulk update error:', error)
        }
      } catch (error) {
        console.error('Error bulk updating contacts:', error)
      } finally {
        this.loading = false
      }
    },
    
    addCustomField() {
      // This would open a modal to select from available custom fields
      // For now, we'll just add a generic field
      const fieldSlug = 'custom_field_' + Date.now()
      this.form.custom_fields[fieldSlug] = ''
    },
    
    removeCustomField(fieldSlug) {
      delete this.form.custom_fields[fieldSlug]
    }
  }
}
</script>
