<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Trip') }}
        </h2>
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
