<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add Trip for') }} {{ $vehicle->type }} - {{ $vehicle->license_plate }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('vehicles.trips.store', $vehicle) }}">
                        @csrf

                        <!-- Date -->
                        <div class="mb-4">
                            <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Date') }}</label>
                            <input type="date" id="date" name="date" value="{{ old('date') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm">
                            @error('date')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Start Location -->
                        <div class="mb-4">
                            <label for="start_location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Start Location') }}</label>
                            <input type="text" id="start_location" name="start_location" value="{{ old('start_location') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm">
                            @error('start_location')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- End Location -->
                        <div class="mb-4">
                            <label for="end_location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('End Location') }}</label>
                            <input type="text" id="end_location" name="end_location" value="{{ old('end_location') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm">
                            @error('end_location')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Purpose -->
                        <div class="mb-4">
                            <label for="purpose" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Purpose') }}</label>
                            <input type="text" id="purpose" name="purpose" value="{{ old('purpose') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm">
                            @error('purpose')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Start Odometer -->
                        <div class="mb-4">
                            <label for="start_odometer" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Start Odometer') }}</label>
                            <input type="number" id="start_odometer" name="start_odometer" value="{{ old('start_odometer') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm">
                            @error('start_odometer')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- End Odometer -->
                        <div class="mb-4">
                            <label for="end_odometer" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('End Odometer') }}</label>
                            <input type="number" id="end_odometer" name="end_odometer" value="{{ old('end_odometer') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm">
                            @error('end_odometer')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Distance -->
                        <div class="mb-4">
                            <label for="distance" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Distance') }}</label>
                            <input type="number" id="distance" name="distance" value="{{ old('distance') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm">
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Will be calculated automatically if left empty') }}</p>
                            @error('distance')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Driver Name -->
                        <div class="mb-4">
                            <label for="driver_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Driver Name') }}</label>
                            <input type="text" id="driver_name" name="driver_name" value="{{ old('driver_name') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm">
                            @error('driver_name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fuel Information (Optional) -->
                        <div class="mt-6 mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Fuel Information (Optional)') }}</h3>
                        </div>

                        <!-- Fuel Amount -->
                        <div class="mb-4">
                            <label for="fuel_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Fuel Amount (liters)') }}</label>
                            <input type="number" step="0.01" id="fuel_amount" name="fuel_amount" value="{{ old('fuel_amount') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm">
                            @error('fuel_amount')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fuel Cost -->
                        <div class="mb-4">
                            <label for="fuel_cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Fuel Cost (EUR)') }}</label>
                            <input type="number" step="0.01" id="fuel_cost" name="fuel_cost" value="{{ old('fuel_cost') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm">
                            @error('fuel_cost')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fuel Receipt Number -->
                        <div class="mb-4">
                            <label for="fuel_receipt_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Fuel Receipt Number') }}</label>
                            <input type="text" id="fuel_receipt_number" name="fuel_receipt_number" value="{{ old('fuel_receipt_number') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm">
                            @error('fuel_receipt_number')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('vehicles.show', $vehicle) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 mr-2">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('Create Trip') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startOdometerInput = document.getElementById('start_odometer');
            const endOdometerInput = document.getElementById('end_odometer');
            const distanceInput = document.getElementById('distance');

            // Calculate distance when start or end odometer changes
            function calculateDistance() {
                const startValue = parseInt(startOdometerInput.value) || 0;
                const endValue = parseInt(endOdometerInput.value) || 0;
                
                if (endValue > startValue) {
                    distanceInput.value = endValue - startValue;
                }
            }

            startOdometerInput.addEventListener('input', calculateDistance);
            endOdometerInput.addEventListener('input', calculateDistance);
        });
    </script>
</x-app-layout>