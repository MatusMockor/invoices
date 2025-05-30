<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Trip') }}
            </h2>
            <a href="{{ route('trips.show', $trip) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                {{ __('Cancel') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <trip-form
                            :initial-data="{
                            vehicle_id: {{ $trip->vehicle_id }},
                            date: '{{ old('date', $trip->date->format('Y-m-d')) }}',
                            start_location: '{{ old('start_location', $trip->start_location) }}',
                            end_location: '{{ old('end_location', $trip->end_location) }}',
                            purpose: '{{ old('purpose', $trip->purpose) }}',
                            start_odometer: '{{ old('start_odometer', $trip->start_odometer) }}',
                            end_odometer: '{{ old('end_odometer', $trip->end_odometer) }}',
                            distance: '{{ old('distance', $trip->distance) }}',
                            driver_name: '{{ old('driver_name', $trip->driver_name) }}',
                            fuel_amount: '{{ old('fuel_amount', $trip->fuel_amount) }}',
                            fuel_cost: '{{ old('fuel_cost', $trip->fuel_cost) }}',
                            fuel_receipt_number: '{{ old('fuel_receipt_number', $trip->fuel_receipt_number) }}'
                        }"
                            :errors="{{ json_encode($errors->messages(), JSON_THROW_ON_ERROR) }}"
                            submit-route="{{ route('trips.update', $trip) }}"
                            cancel-route="{{ route('trips.show', $trip) }}"
                            submit-button-text="{{ __('Update Trip') }}"
                            :vehicles="{{ json_encode($vehicles, JSON_THROW_ON_ERROR) }}"
                            :show-vehicle-selection="true"
                    ></trip-form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
