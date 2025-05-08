<template>
  <form @submit.prevent="submitForm" class="space-y-8">
    <!-- Basic Information Card -->
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
      <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
          </svg>
          Partner Basic Information
        </h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter the basic details of your partner.</p>
      </div>
      <div class="p-6 bg-white dark:bg-gray-800 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="name" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">Company Name *</label>
            <input 
              id="name" 
              v-model="form.name" 
              type="text" 
              class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
              required 
              autofocus 
              placeholder="Partner company name"
            />
            <div v-if="errors.name" class="mt-2 text-sm text-red-600">{{ errors.name }}</div>
          </div>
          
          <div>
            <label for="ico" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">IČO *</label>
            <div class="flex mt-1">
              <input 
                id="ico" 
                v-model="form.ico" 
                type="text" 
                class="w-full rounded-r-none border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
                required 
                placeholder="12345678"
              />
              <button 
                type="button" 
                @click="loadPartnerData" 
                :disabled="loading" 
                class="px-4 py-2 bg-blue-600 text-white rounded-r-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                {{ loading ? 'Loading...' : 'Load' }}
              </button>
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Company identification number</p>
            <div v-if="errors.ico" class="mt-2 text-sm text-red-600">{{ errors.ico }}</div>
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="dic" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">DIČ</label>
            <input 
              id="dic" 
              v-model="form.dic" 
              type="text" 
              class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
              placeholder="1234567890"
            />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tax identification number</p>
            <div v-if="errors.dic" class="mt-2 text-sm text-red-600">{{ errors.dic }}</div>
          </div>
          
          <div>
            <label for="ic_dph" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">IČ DPH</label>
            <input 
              id="ic_dph" 
              v-model="form.ic_dph" 
              type="text" 
              class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
              placeholder="SK1234567890"
            />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">VAT identification number</p>
            <div v-if="errors.ic_dph" class="mt-2 text-sm text-red-600">{{ errors.ic_dph }}</div>
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
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter the address details of your partner.</p>
      </div>
      <div class="p-6 bg-white dark:bg-gray-800 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="street" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">Street Address *</label>
            <input 
              id="street" 
              v-model="form.street" 
              type="text" 
              class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
              required 
              placeholder="123 Business St."
            />
            <div v-if="errors.street" class="mt-2 text-sm text-red-600">{{ errors.street }}</div>
          </div>
          
          <div class="grid grid-cols-3 gap-4">
            <div class="col-span-2">
              <label for="city" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">City *</label>
              <input 
                id="city" 
                v-model="form.city" 
                type="text" 
                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
                required 
                placeholder="Bratislava"
              />
              <div v-if="errors.city" class="mt-2 text-sm text-red-600">{{ errors.city }}</div>
            </div>
            <div>
              <label for="postal_code" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">Postal Code *</label>
              <input 
                id="postal_code" 
                v-model="form.postal_code" 
                type="text" 
                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
                required 
                placeholder="10001"
              />
              <div v-if="errors.postal_code" class="mt-2 text-sm text-red-600">{{ errors.postal_code }}</div>
            </div>
          </div>
        </div>
        
        <div>
          <label for="country" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">Country *</label>
          <input 
            id="country" 
            v-model="form.country" 
            type="text" 
            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
            required 
            placeholder="Slovakia"
          />
          <div v-if="errors.country" class="mt-2 text-sm text-red-600">{{ errors.country }}</div>
        </div>
      </div>
    </div>
    
    <!-- Legal Information Card -->
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
      <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM4 4h3a3 3 0 006 0h3a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zm2.5 7a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm2.45 4a2.5 2.5 0 10-4.9 0h4.9zM12 9a1 1 0 100 2h3a1 1 0 100-2h-3zm-1 4a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1z" clip-rule="evenodd" />
          </svg>
          Legal Information
        </h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter the legal details of your partner.</p>
      </div>
      <div class="p-6 bg-white dark:bg-gray-800 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="company_type" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">Právna forma *</label>
            <select 
              id="company_type" 
              v-model="form.company_type" 
              class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full" 
              required
            >
              <option value="">-- Vyberte právnu formu --</option>
              <option value="živnosť">Živnosť</option>
              <option value="s.r.o.">s.r.o.</option>
            </select>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Legal form of the company</p>
            <div v-if="errors.company_type" class="mt-2 text-sm text-red-600">{{ errors.company_type }}</div>
          </div>
          
          <div>
            <label for="registration_number" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">Registračné číslo *</label>
            <input 
              id="registration_number" 
              v-model="form.registration_number" 
              type="text" 
              class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
              required 
              placeholder="Obchodný register / Živnostenský register"
            />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Registration number in business or trade register</p>
            <div v-if="errors.registration_number" class="mt-2 text-sm text-red-600">{{ errors.registration_number }}</div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Submit Button -->
    <div class="flex items-center justify-end space-x-4">
      <a 
        href="/partners" 
        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150"
      >
        Cancel
      </a>
      <button 
        type="submit" 
        class="inline-flex items-center px-4 py-2 bg-green-600 dark:bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 dark:hover:bg-green-700 focus:bg-green-700 dark:focus:bg-green-700 active:bg-green-900 dark:active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
      >
        Create Partner
      </button>
    </div>
  </form>
</template>

<script>
export default {
  props: {
    fetchPartnerRoute: {
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
      loading: false
    }
  },
  methods: {
    async loadPartnerData() {
      if (!this.form.ico || this.form.ico.length !== 8) {
        alert('Please enter a valid 8-digit ICO');
        return;
      }
      
      this.loading = true;
      
      try {
        const response = await axios.get(`${this.fetchPartnerRoute}?ico=${this.form.ico}`);
        
        const data = response.data.data || response.data;
        
        // Fill form fields with the returned data
        this.form.name = data.name || '';
        this.form.dic = data.dic || '';
        this.form.ic_dph = data.ic_dph || '';
        this.form.street = data.street || '';
        this.form.city = data.city || '';
        this.form.postal_code = data.postal_code || '';
        this.form.country = data.country || 'Slovakia';
        this.form.company_type = data.company_type || '';
        this.form.registration_number = data.registration_number || '';
      } catch (error) {
        console.error('Error:', error);
        alert('Could not find partner with this ICO. Please enter the details manually.');
      } finally {
        this.loading = false;
      }
    },
    
    async submitForm() {
      try {
        const response = await axios.post('/partners', this.form);
        window.location.href = '/partners';
      } catch (error) {
        if (error.response && error.response.status === 422) {
          this.errors = error.response.data.errors;
        } else {
          console.error('Error submitting form:', error);
          alert('An error occurred while saving the partner. Please try again.');
        }
      }
    }
  }
}
</script>
