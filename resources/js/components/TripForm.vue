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

    <!-- Date -->
    <div class="mb-4">
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

    <!-- Start Location -->
    <div class="mb-4">
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
    <div class="mb-4">
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

    <!-- Purpose -->
    <div class="mb-4">
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

    <!-- Start Odometer -->
    <div class="mb-4">
      <label for="start_odometer" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Start Odometer') }}</label>
      <input 
        type="number" 
        id="start_odometer" 
        v-model="form.start_odometer" 
        @input="calculateDistance"
        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm"
        required
      >
      <div v-if="errors.start_odometer" class="mt-2 text-sm text-red-600 dark:text-red-500">{{ errors.start_odometer }}</div>
    </div>

    <!-- End Odometer -->
    <div class="mb-4">
      <label for="end_odometer" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('End Odometer') }}</label>
      <input 
        type="number" 
        id="end_odometer" 
        v-model="form.end_odometer" 
        @input="calculateDistance"
        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm"
        required
      >
      <div v-if="errors.end_odometer" class="mt-2 text-sm text-red-600 dark:text-red-500">{{ errors.end_odometer }}</div>
    </div>

    <!-- Distance -->
    <div class="mb-4">
      <label for="distance" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Distance') }}</label>
      <input 
        type="number" 
        id="distance" 
        v-model="form.distance" 
        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm"
      >
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Will be calculated automatically if left empty') }}</p>
      <div v-if="errors.distance" class="mt-2 text-sm text-red-600 dark:text-red-500">{{ errors.distance }}</div>
    </div>

    <!-- Driver Name -->
    <div class="mb-4">
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

    <!-- Fuel Information (Optional) -->
    <div class="mt-6 mb-4">
      <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Fuel Information (Optional)') }}</h3>
    </div>

    <!-- Fuel Amount -->
    <div class="mb-4">
      <label for="fuel_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Fuel Amount (liters)') }}</label>
      <input 
        type="number" 
        step="0.01" 
        id="fuel_amount" 
        v-model="form.fuel_amount" 
        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm"
      >
      <div v-if="errors.fuel_amount" class="mt-2 text-sm text-red-600 dark:text-red-500">{{ errors.fuel_amount }}</div>
    </div>

    <!-- Fuel Cost -->
    <div class="mb-4">
      <label for="fuel_cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Fuel Cost (EUR)') }}</label>
      <input 
        type="number" 
        step="0.01" 
        id="fuel_cost" 
        v-model="form.fuel_cost" 
        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm"
      >
      <div v-if="errors.fuel_cost" class="mt-2 text-sm text-red-600 dark:text-red-500">{{ errors.fuel_cost }}</div>
    </div>

    <!-- Fuel Receipt Number -->
    <div class="mb-4">
      <label for="fuel_receipt_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Fuel Receipt Number') }}</label>
      <input 
        type="text" 
        id="fuel_receipt_number" 
        v-model="form.fuel_receipt_number" 
        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm"
      >
      <div v-if="errors.fuel_receipt_number" class="mt-2 text-sm text-red-600 dark:text-red-500">{{ errors.fuel_receipt_number }}</div>
    </div>

    <div class="flex items-center justify-end mt-4">
      <a :href="cancelRoute" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 mr-2">
        {{ __('Cancel') }}
      </a>
      <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
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
      const startValue = parseInt(this.form.start_odometer) || 0;
      const endValue = parseInt(this.form.end_odometer) || 0;

      if (endValue > startValue) {
        this.form.distance = endValue - startValue;
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
