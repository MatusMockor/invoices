<template>
  <form @submit.prevent="submitForm" class="space-y-8">
    <!-- Basic Information Card -->
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
      <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd" />
          </svg>
          Company Basic Information
        </h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter the basic details of your company.</p>
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
              placeholder="Your company name"
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
                @click="loadCompanyData" 
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

        <!-- Address Fields -->
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
    
    <!-- Business Registration Information -->
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
      <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd" />
          </svg>
          Business Registration Details
        </h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter your company's registration and tax information.</p>
      </div>
      <div class="p-6 bg-white dark:bg-gray-800 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Company Type -->
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
          
          <!-- Registration Number -->
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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- IBAN -->
          <div>
            <label for="iban" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">IBAN</label>
            <input 
              id="iban" 
              v-model="form.iban" 
              type="text" 
              class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
              placeholder="SK82 8330 0000 0022 0012 3456"
            />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">International Bank Account Number</p>
            <div v-if="errors.iban" class="mt-2 text-sm text-red-600">{{ errors.iban }}</div>
          </div>
        
          <!-- SWIFT -->
          <div>
            <label for="swift" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">SWIFT/BIC</label>
            <input 
              id="swift" 
              v-model="form.swift" 
              type="text" 
              class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
              placeholder="TATRSKBX"
            />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Bank Identifier Code</p>
            <div v-if="errors.swift" class="mt-2 text-sm text-red-600">{{ errors.swift }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Contact Information -->
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
      <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
          </svg>
          Contact Information
        </h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter contact details for your company.</p>
      </div>
      <div class="p-6 bg-white dark:bg-gray-800 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Phone -->
          <div>
            <label for="phone" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">Phone</label>
            <input 
              id="phone" 
              v-model="form.phone" 
              type="text" 
              class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
              placeholder="+421 901 123 456"
            />
            <div v-if="errors.phone" class="mt-2 text-sm text-red-600">{{ errors.phone }}</div>
          </div>
          
          <!-- Email -->
          <div>
            <label for="email" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">Email</label>
            <input 
              id="email" 
              v-model="form.email" 
              type="email" 
              class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
              placeholder="info@company.com"
            />
            <div v-if="errors.email" class="mt-2 text-sm text-red-600">{{ errors.email }}</div>
          </div>

          <!-- Website -->
          <div>
            <label for="website" class="block font-semibold text-sm text-gray-700 dark:text-gray-300">Website</label>
            <input 
              id="website" 
              v-model="form.website" 
              type="url" 
              class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
              placeholder="https://www.company.com"
            />
            <div v-if="errors.website" class="mt-2 text-sm text-red-600">{{ errors.website }}</div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Submit Button -->
    <div class="flex items-center justify-end space-x-4">
      <a :href="cancelRoute" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
        Cancel
      </a>
      <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
        {{ submitButtonText }}
      </button>
    </div>
  </form>
</template>

<script>
export default {
  props: {
    initialData: {
      type: Object,
      default: () => ({})
    },
    errors: {
      type: Object,
      default: () => ({})
    },
    submitRoute: {
      type: String,
      required: true
    },
    cancelRoute: {
      type: String,
      required: true
    },
    fetchPartnerRoute: {
      type: String,
      required: true
    },
    submitButtonText: {
      type: String,
      default: 'Save'
    }
  },
  data() {
    return {
      loading: false,
      form: {
        name: this.initialData.name || '',
        ico: this.initialData.ico || '',
        dic: this.initialData.dic || '',
        ic_dph: this.initialData.ic_dph || '',
        street: this.initialData.street || '',
        city: this.initialData.city || '',
        postal_code: this.initialData.postal_code || '',
        country: this.initialData.country || 'Slovakia',
        company_type: this.initialData.company_type || '',
        registration_number: this.initialData.registration_number || '',
        iban: this.initialData.iban || '',
        swift: this.initialData.swift || '',
        phone: this.initialData.phone || '',
        email: this.initialData.email || '',
        website: this.initialData.website || ''
      }
    }
  },
  methods: {
    async loadCompanyData() {
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
        this.form.iban = data.iban || '';
        this.form.swift = data.swift || '';
        this.form.phone = data.phone || '';
        this.form.email = data.email || '';
        this.form.website = data.website || '';
      } catch (error) {
        console.error('Error:', error);
        alert('Could not find company with this ICO. Please enter the details manually.');
      } finally {
        this.loading = false;
      }
    },
    
    submitForm() {
      axios.post(this.submitRoute, this.form)
        .then(response => {
          window.location.href = response.data.redirect || this.cancelRoute;
        })
        .catch(error => {
          console.error('Error submitting form:', error);
        });
    }
  }
}
</script>
