<template>
  <form @submit.prevent="submitForm" class="space-y-6">
    <!-- Hidden field for vehicle_id if not showing vehicle selection -->
    <input v-if="!showVehicleSelection" type="hidden" v-model="form.vehicle_id">

    <!-- Vehicle Selection (only shown in edit mode or if explicitly enabled) -->
    <div v-if="showVehicleSelection" class="mb-4">
      <label for="vehicle_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Vehicle') }}</label>
      <select 
        id="vehicle_id" 
        v-model="form.vehicle_id" 
        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm"
        required
      >
        <option v-for="vehicle in vehicles" :key="vehicle.id" :value="vehicle.id">
          {{ vehicle.type }} - {{ vehicle.license_plate }}
        </option>
      </select>
      <div v-if="errors.vehicle_id" class="mt-2 text-sm text-red-600 dark:text-red-500">{{ errors.vehicle_id }}</div>
    </div>

    <!-- Trip Details Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Date -->
      <div>
        <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Date') }}</label>
        <input 
          type="date" 
          id="date" 
          v-model="form.date" 
          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm"
          required
        >
        <div v-if="errors.date" class="mt-2 text-sm text-red-600 dark:text-red-500">{{ errors.date }}</div>
      </div>

      <!-- Driver Name -->
      <div>
        <label for="driver_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Driver Name') }}</label>
        <input 
          type="text" 
          id="driver_name" 
          v-model="form.driver_name" 
          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm"
          required
        >
        <div v-if="errors.driver_name" class="mt-2 text-sm text-red-600 dark:text-red-500">{{ errors.driver_name }}</div>
      </div>

      <!-- Start Location -->
      <div>
        <label for="start_location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Start Location') }}</label>
        <input 
          type="text" 
          id="start_location" 
          v-model="form.start_location" 
          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm"
          required
        >
        <div v-if="errors.start_location" class="mt-2 text-sm text-red-600 dark:text-red-500">{{ errors.start_location }}</div>
      </div>

      <!-- End Location -->
      <div>
        <label for="end_location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('End Location') }}</label>
        <input 
          type="text" 
          id="end_location" 
          v-model="form.end_location" 
          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm"
          required
        >
        <div v-if="errors.end_location" class="mt-2 text-sm text-red-600 dark:text-red-500">{{ errors.end_location }}</div>
      </div>
    </div>

    <!-- Purpose (Full Width) -->
    <div class="mt-6">
      <label for="purpose" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Purpose') }}</label>
      <input 
        type="text" 
        id="purpose" 
        v-model="form.purpose" 
        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm"
        required
      >
      <div v-if="errors.purpose" class="mt-2 text-sm text-red-600 dark:text-red-500">{{ errors.purpose }}</div>
    </div>

    <!-- Odometer Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
      <!-- Start Odometer -->
      <div>
        <label for="start_odometer" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Start Odometer') }}</label>
        <input 
          type="number" 
          id="start_odometer" 
          v-model.number="form.start_odometer" 
          @input="validateNumericInput($event, 'start_odometer')"
          @keypress="preventNonNumericInput"
          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm"
          required
        >
        <div v-if="errors.start_odometer" class="mt-2 text-sm text-red-600 dark:text-red-500">{{ errors.start_odometer }}</div>
      </div>

      <!-- End Odometer -->
      <div>
        <label for="end_odometer" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('End Odometer') }}</label>
        <input 
          type="number" 
          id="end_odometer" 
          v-model.number="form.end_odometer" 
          @input="validateNumericInput($event, 'end_odometer')"
          @keypress="preventNonNumericInput"
          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm"
          required
        >
        <div v-if="errors.end_odometer" class="mt-2 text-sm text-red-600 dark:text-red-500">{{ errors.end_odometer }}</div>
      </div>

      <!-- Distance -->
      <div>
        <label for="distance" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Distance') }}</label>
        <input 
          type="number" 
          id="distance" 
          v-model="form.distance" 
          readonly
          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 cursor-not-allowed"
        >
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Calculated automatically based on odometer values') }}</p>
        <div v-if="errors.distance" class="mt-2 text-sm text-red-600 dark:text-red-500">{{ errors.distance }}</div>
      </div>
    </div>

    <!-- Fuel Information Section -->
    <div class="mt-6 mb-4">
      <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Fuel Information (Optional)') }}</h3>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Fuel Amount -->
      <div>
        <label for="fuel_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Fuel Amount (liters)') }}</label>
        <input 
          type="number" 
          step="0.01" 
          id="fuel_amount" 
          v-model.number="form.fuel_amount" 
          @input="validateNumericInput($event, 'fuel_amount')"
          @keypress="preventNonNumericInput"
          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm"
        >
        <div v-if="errors.fuel_amount" class="mt-2 text-sm text-red-600 dark:text-red-500">{{ errors.fuel_amount }}</div>
      </div>

      <!-- Fuel Cost -->
      <div>
        <label for="fuel_cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Fuel Cost (EUR)') }}</label>
        <input 
          type="number" 
          step="0.01" 
          id="fuel_cost" 
          v-model.number="form.fuel_cost" 
          @input="validateNumericInput($event, 'fuel_cost')"
          @keypress="preventNonNumericInput"
          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm"
        >
        <div v-if="errors.fuel_cost" class="mt-2 text-sm text-red-600 dark:text-red-500">{{ errors.fuel_cost }}</div>
      </div>

      <!-- Fuel Receipt Number -->
      <div>
        <label for="fuel_receipt_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Fuel Receipt Number') }}</label>
        <input 
          type="text" 
          id="fuel_receipt_number" 
          v-model="form.fuel_receipt_number" 
          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm"
        >
        <div v-if="errors.fuel_receipt_number" class="mt-2 text-sm text-red-600 dark:text-red-500">{{ errors.fuel_receipt_number }}</div>
      </div>
    </div>

    <div class="flex items-center justify-end mt-6">
      <a :href="cancelRoute" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 mr-3">
        {{ __('Cancel') }}
      </a>
      <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 dark:hover:bg-indigo-400 focus:bg-indigo-700 dark:focus:bg-indigo-400 active:bg-indigo-800 dark:active:bg-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
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
    submitButtonText: {
      type: String,
      default: 'Create Trip'
    },
    vehicles: {
      type: Array,
      default: () => []
    },
    showVehicleSelection: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      form: {
        vehicle_id: this.initialData.vehicle_id || '',
        date: this.initialData.date || '',
        start_location: this.initialData.start_location || '',
        end_location: this.initialData.end_location || '',
        purpose: this.initialData.purpose || '',
        start_odometer: this.initialData.start_odometer || '',
        end_odometer: this.initialData.end_odometer || '',
        distance: this.initialData.distance || '',
        driver_name: this.initialData.driver_name || '',
        fuel_amount: this.initialData.fuel_amount || '',
        fuel_cost: this.initialData.fuel_cost || '',
        fuel_receipt_number: this.initialData.fuel_receipt_number || ''
      }
    }
  },
  methods: {
    // Translation method to replace Laravel's __() function
    __(text) {
      return text;
    },
    calculateDistance() {
      const startValue = parseFloat(this.form.start_odometer) || 0;
      const endValue = parseFloat(this.form.end_odometer) || 0;

      if (endValue >= startValue) {
        this.form.distance = endValue - startValue;
      } else {
        this.form.distance = 0;
      }
    },
    validateNumericInput(event, fieldName) {
      // Get the input value
      const value = event.target.value;

      // If the value is not a valid number, reset it to the last valid value or empty string
      if (value === '' || isNaN(parseFloat(value))) {
        this.form[fieldName] = '';
      } else {
        // Ensure the value is stored as a number
        this.form[fieldName] = parseFloat(value);
      }

      // If this is an odometer field, recalculate the distance
      if (fieldName === 'start_odometer' || fieldName === 'end_odometer') {
        this.calculateDistance();
      }
    },
    preventNonNumericInput(event) {
      // Allow: backspace, delete, tab, escape, enter, decimal point, and numbers
      const charCode = (event.which) ? event.which : event.keyCode;

      // Allow decimal point (.)
      if (charCode === 46) {
        // Check if the input already contains a decimal point
        if (event.target.value.indexOf('.') !== -1) {
          event.preventDefault();
        }
        return;
      }

      // If the character is not a number (0-9), prevent the input
      if (charCode < 48 || charCode > 57) {
        event.preventDefault();
      }
    },
    submitForm() {
      // Determine if this is an edit operation by checking if the URL contains '/trips/{id}'
      const isEdit = this.submitRoute.match(/\/trips\/\d+$/);

      // Use axios.put for edit operations, axios.post for create operations
      const request = isEdit 
        ? axios.put(this.submitRoute, this.form)
        : axios.post(this.submitRoute, this.form);

      request
        .then(response => {
          window.location.href = response.data.redirect || this.cancelRoute;
        })
        .catch(error => {
          console.error('Error submitting form:', error);
          if (error.response && error.response.data && error.response.data.errors) {
            this.errors = error.response.data.errors;
          }
        });
    }
  }
}
</script>
