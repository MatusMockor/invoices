<template>
  <div class="modal-backdrop" @click.self="$emit('close')">
    <div class="modal-content modal-content-sm" @click.stop>
      <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Import Contacts</h3>
          <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Instructions -->
        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900 rounded-lg">
          <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">Import Instructions</h4>
          <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
            <li>• Upload a CSV file with the following columns: first_name, last_name, email, phone, job_title, notes</li>
            <li>• The first row should contain column headers</li>
            <li>• Maximum file size: 10MB</li>
            <li>• Supported formats: CSV, TXT</li>
          </ul>
        </div>

        <!-- File Upload -->
        <form @submit.prevent="importContacts" class="space-y-4">
          <div>
            <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select CSV File</label>
            <input ref="fileInput" type="file" id="file" accept=".csv,.txt" @change="handleFileSelect" required
                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            <p v-if="selectedFile" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
              Selected: {{ selectedFile.name }} ({{ formatFileSize(selectedFile.size) }})
            </p>
          </div>

          <!-- Import Options -->
          <div class="space-y-3">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Import Options</h4>
            
            <div class="flex items-center">
              <input v-model="options.skipDuplicates" type="checkbox" id="skip_duplicates"
                     class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
              <label for="skip_duplicates" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                Skip duplicate contacts (based on email)
              </label>
            </div>

            <div class="flex items-center">
              <input v-model="options.updateExisting" type="checkbox" id="update_existing"
                     class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
              <label for="update_existing" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                Update existing contacts
              </label>
            </div>
          </div>

          <!-- Progress -->
          <div v-if="importing" class="space-y-2">
            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
              <span>Importing contacts...</span>
              <span>{{ importProgress.current }} / {{ importProgress.total }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" 
                   :style="{ width: importProgress.percentage + '%' }"></div>
            </div>
          </div>

          <!-- Results -->
          <div v-if="importResults" class="space-y-2">
            <div class="p-4 rounded-lg" :class="importResults.errors.length > 0 ? 'bg-yellow-50 dark:bg-yellow-900' : 'bg-green-50 dark:bg-green-900'">
              <h4 class="text-sm font-medium mb-2" :class="importResults.errors.length > 0 ? 'text-yellow-800 dark:text-yellow-200' : 'text-green-800 dark:text-green-200'">
                Import Results
              </h4>
              <p class="text-sm" :class="importResults.errors.length > 0 ? 'text-yellow-700 dark:text-yellow-300' : 'text-green-700 dark:text-green-300'">
                Successfully imported {{ importResults.imported }} contacts.
              </p>
              <div v-if="importResults.errors.length > 0" class="mt-2">
                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Errors:</p>
                <ul class="text-sm text-yellow-700 dark:text-yellow-300 list-disc list-inside">
                  <li v-for="error in importResults.errors.slice(0, 5)" :key="error">{{ error }}</li>
                  <li v-if="importResults.errors.length > 5">... and {{ importResults.errors.length - 5 }} more errors</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-3 pt-4">
            <button type="button" @click="$emit('close')" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              Cancel
            </button>
            <button type="submit" :disabled="!selectedFile || importing" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
              {{ importing ? 'Importing...' : 'Import Contacts' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ContactImportModal',
  data() {
    return {
      selectedFile: null,
      importing: false,
      importProgress: {
        current: 0,
        total: 0,
        percentage: 0
      },
      importResults: null,
      options: {
        skipDuplicates: true,
        updateExisting: false
      }
    }
  },
  methods: {
    handleFileSelect(event) {
      this.selectedFile = event.target.files[0]
      this.importResults = null
    },
    
    async importContacts() {
      if (!this.selectedFile) return
      
      this.importing = true
      this.importResults = null
      
      const formData = new FormData()
      formData.append('file', this.selectedFile)
      formData.append('skip_duplicates', this.options.skipDuplicates)
      formData.append('update_existing', this.options.updateExisting)
      
      try {
        const response = await fetch('/crm/contacts/import', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: formData
        })
        
        if (response.ok) {
          const result = await response.json()
          this.importResults = result
          this.$emit('imported')
        } else {
          const error = await response.json()
          this.importResults = {
            imported: 0,
            errors: [error.message || 'Import failed']
          }
        }
      } catch (error) {
        console.error('Error importing contacts:', error)
        this.importResults = {
          imported: 0,
          errors: ['Network error occurred during import']
        }
      } finally {
        this.importing = false
      }
    },
    
    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes'
      const k = 1024
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    }
  }
}
</script>
