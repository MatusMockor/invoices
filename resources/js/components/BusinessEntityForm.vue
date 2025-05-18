<template>
  <form @submit.prevent="submitForm" class="space-y-8">
    <!-- Basic Information Card -->
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
      <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
          </svg>
          Business Entity Basic Information
        </h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter the basic details of your business entity.</p>
      </div>
      <div class="p-6 bg-white dark:bg-gray-800 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="name" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">Company Name *</label>
            <input 
              id="name" 
              v-model="form.name" 
              type="text" 
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              :class="{ 'border-red-500': errors.name }"
            >
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
          </div>
          
          <div>
            <label for="ico" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">IČO *</label>
            <div class="mt-1 flex rounded-md shadow-sm">
              <input 
                id="ico" 
                v-model="form.ico" 
                type="text" 
                class="block w-full rounded-l-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                :class="{ 'border-red-500': errors.ico }"
              >
              <button 
                type="button" 
                @click="loadBusinessEntityData" 
                class="inline-flex items-center px-4 py-2 border border-l-0 border-gray-300 dark:border-gray-700 text-sm font-medium rounded-r-md text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                :disabled="loading"
              >
                <span v-if="loading" class="mr-2">
                  <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                Load
              </button>
            </div>
            <p v-if="errors.ico" class="mt-1 text-sm text-red-600">{{ errors.ico[0] }}</p>
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="dic" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">DIČ</label>
            <input 
              id="dic" 
              v-model="form.dic" 
              type="text" 
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              :class="{ 'border-red-500': errors.dic }"
            >
            <p v-if="errors.dic" class="mt-1 text-sm text-red-600">{{ errors.dic[0] }}</p>
          </div>
          
          <div>
            <label for="ic_dph" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">IČ DPH</label>
            <input 
              id="ic_dph" 
              v-model="form.ic_dph" 
              type="text" 
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              :class="{ 'border-red-500': errors.ic_dph }"
            >
            <p v-if="errors.ic_dph" class="mt-1 text-sm text-red-600">{{ errors.ic_dph[0] }}</p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Address Card -->
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
      <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
          </svg>
          Address Information
        </h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter the address details of your business entity.</p>
      </div>
      <div class="p-6 bg-white dark:bg-gray-800 space-y-6">
        <div>
          <label for="street" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">Street Address *</label>
          <input 
            id="street" 
            v-model="form.street" 
            type="text" 
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            :class="{ 'border-red-500': errors.street }"
          >
          <p v-if="errors.street" class="mt-1 text-sm text-red-600">{{ errors.street[0] }}</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="city" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">City *</label>
            <input 
              id="city" 
              v-model="form.city" 
              type="text" 
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              :class="{ 'border-red-500': errors.city }"
            >
            <p v-if="errors.city" class="mt-1 text-sm text-red-600">{{ errors.city[0] }}</p>
          </div>
          
          <div>
            <label for="postal_code" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">Postal Code *</label>
            <input 
              id="postal_code" 
              v-model="form.postal_code" 
              type="text" 
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              :class="{ 'border-red-500': errors.postal_code }"
            >
            <p v-if="errors.postal_code" class="mt-1 text-sm text-red-600">{{ errors.postal_code[0] }}</p>
          </div>
        </div>
        
        <div>
          <label for="country" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">Country *</label>
          <input 
            id="country" 
            v-model="form.country" 
            type="text" 
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            :class="{ 'border-red-500': errors.country }"
          >
          <p v-if="errors.country" class="mt-1 text-sm text-red-600">{{ errors.country[0] }}</p>
        </div>
      </div>
    </div>
    
    <!-- Additional Information Card -->
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
      <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM4 4h3a3 3 0 006 0h3a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zm2.5 7a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm2.45 4a2.5 2.5 0 10-4.9 0h4.9zM12 9a1 1 0 100 2h3a1 1 0 100-2h-3zm-1 4a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1z" clip-rule="evenodd" />
          </svg>
          Additional Information
        </h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter any additional details about your business entity.</p>
      </div>
      <div class="p-6 bg-white dark:bg-gray-800 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="company_type" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">Company Type</label>
            <input 
              id="company_type" 
              v-model="form.company_type" 
              type="text" 
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              :class="{ 'border-red-500': errors.company_type }"
            >
            <p v-if="errors.company_type" class="mt-1 text-sm text-red-600">{{ errors.company_type[0] }}</p>
          </div>
          
          <div>
            <label for="registration_number" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">Registration Number</label>
            <input 
              id="registration_number" 
              v-model="form.registration_number" 
              type="text" 
              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              :class="{ 'border-red-500': errors.registration_number }"
            >
            <p v-if="errors.registration_number" class="mt-1 text-sm text-red-600">{{ errors.registration_number[0] }}</p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Business Entity Selection Dropdown -->
    <div v-if="showBusinessEntityModal" class="fixed inset-0 z-50" @click.self="closeBusinessEntityModal">
      <div ref="businessEntityDropdown" class="fixed bg-white dark:bg-gray-800 rounded-md shadow-lg overflow-hidden" 
           :style="dropdownStyle">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Odberateľ</h3>
        </div>
        
        <div class="p-4">
          <div v-if="businessEntityLoading" class="flex justify-center items-center py-4">
            <svg class="animate-spin h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="ml-2 text-gray-700 dark:text-gray-300">Načítavam údaje o obchodnom subjekte...</span>
          </div>
          
          <div v-else-if="businessEntityData" 
               class="bg-gray-50 dark:bg-gray-700 p-4 rounded-md cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
               @click="selectBusinessEntity">
            <h4 class="font-bold text-gray-900 dark:text-gray-100">{{ businessEntityData.name }}</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ businessEntityData.street }}, {{ businessEntityData.postal_code }} {{ businessEntityData.city }}</p>
            <div class="mt-2 text-sm">
              <p class="text-gray-600 dark:text-gray-400">IČO: {{ businessEntityData.ico }}</p>
              <p v-if="businessEntityData.dic" class="text-gray-600 dark:text-gray-400">DIČ: {{ businessEntityData.dic }}</p>
              <p v-if="businessEntityData.ic_dph" class="text-gray-600 dark:text-gray-400">IČ DPH: {{ businessEntityData.ic_dph }}</p>
            </div>
          </div>
          
          <div v-else class="text-center py-4">
            <p class="text-gray-700 dark:text-gray-300">{{ businessEntityErrorMessage || 'Žiadne údaje o obchodnom subjekte neboli nájdené' }}</p>
          </div>
        </div>
        
        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 flex justify-end space-x-2">
          <button type="button" @click="closeBusinessEntityModal" class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-50 dark:hover:bg-gray-700 text-sm">
            Zavrieť
          </button>
        </div>
      </div>
    </div>
    
    <!-- Submit Button -->
    <div class="flex items-center justify-end space-x-4">
      <a 
        href="/business-entities" 
        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
      >
        Cancel
      </a>
      <button 
        type="submit" 
        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
      >
        Save Business Entity
      </button>
    </div>
  </form>
</template>

<script>
export default {
  props: {
    fetchBusinessEntityRoute: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      form: {
        name: '',
        ico: '',
        dic: '',
        ic_dph: '',
        street: '',
        city: '',
        postal_code: '',
        country: 'Slovakia',
        company_type: '',
        registration_number: ''
      },
      errors: {},
      loading: false,
      showBusinessEntityModal: false,
      businessEntityLoading: false,
      businessEntityData: null,
      businessEntityErrorMessage: null,
      dropdownStyle: {
        top: '0px',
        left: '0px',
        width: '500px'
      }
    }
  },
  methods: {
    updateDropdownPosition() {
      if (!this.showBusinessEntityModal) return;
      
      const icoInput = document.getElementById('ico');
      if (icoInput) {
        const rect = icoInput.getBoundingClientRect();
        this.dropdownStyle = {
          top: `${rect.bottom + 5}px`,
          left: `${rect.left}px`,
          width: '500px'
        };
      }
    },
    
    async loadBusinessEntityData() {
      if (!this.form.ico || this.form.ico.length !== 8) {
        alert('Please enter a valid 8-digit ICO');
        return;
      }
      
      this.loading = true;
      this.businessEntityLoading = true;
      this.showBusinessEntityModal = true;
      this.businessEntityData = null;
      this.businessEntityErrorMessage = null;
      
      // Position the dropdown under the ICO field
      this.$nextTick(() => {
        this.updateDropdownPosition();
        
        // Add event listeners for scrolling and resizing
        window.addEventListener('scroll', this.updateDropdownPosition);
        window.addEventListener('resize', this.updateDropdownPosition);
      });
      
      try {
        const response = await axios.get(`${this.fetchBusinessEntityRoute}?ico=${this.form.ico}`);
        
        const data = response.data.data || response.data;
        
        // Store the business entity data but don't fill the form yet
        this.businessEntityData = data;
        
      } catch (error) {
        console.error('Error:', error);
        this.businessEntityErrorMessage = 'Could not find business entity with this ICO. Please enter the details manually.';
      } finally {
        this.loading = false;
        this.businessEntityLoading = false;
      }
    },
    
    async submitForm() {
      try {
        const response = await axios.post('/business-entities', this.form);
        window.location.href = '/business-entities';
      } catch (error) {
        if (error.response && error.response.status === 422) {
          this.errors = error.response.data.errors;
        } else {
          console.error('Error submitting form:', error);
          alert('An error occurred while saving the business entity. Please try again.');
        }
      }
    },
    
    closeBusinessEntityModal() {
      this.showBusinessEntityModal = false;
      this.businessEntityData = null;
      this.businessEntityErrorMessage = null;
      
      // Remove event listeners when modal is closed
      window.removeEventListener('scroll', this.updateDropdownPosition);
      window.removeEventListener('resize', this.updateDropdownPosition);
    },
    
    selectBusinessEntity() {
      // Fill form fields with the selected business entity data
      if (this.businessEntityData) {
        this.form.name = this.businessEntityData.name || '';
        this.form.dic = this.businessEntityData.dic || '';
        this.form.ic_dph = this.businessEntityData.ic_dph || '';
        this.form.street = this.businessEntityData.street || '';
        this.form.city = this.businessEntityData.city || '';
        this.form.postal_code = this.businessEntityData.postal_code || '';
        this.form.country = this.businessEntityData.country || 'Slovakia';
        this.form.company_type = this.businessEntityData.company_type || '';
        this.form.registration_number = this.businessEntityData.registration_number || '';
      }
      
      this.closeBusinessEntityModal();
    }
  }
}
</script>