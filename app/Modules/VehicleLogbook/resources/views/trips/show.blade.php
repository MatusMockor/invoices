<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Trip Details') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('trips.edit', $trip) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 dark:bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white dark:text-white uppercase tracking-widest hover:bg-yellow-700 dark:hover:bg-yellow-700 focus:bg-yellow-700 dark:focus:bg-yellow-700 active:bg-yellow-800 dark:active:bg-yellow-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('Edit Trip') }}
                </a>
                <a href="{{ route('trips.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('Back to Trips') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('Trip Information') }}</h3>
                            
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Date') }}</h4>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $trip->date->format('Y-m-d') }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Vehicle') }}</h4>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $vehicle->type }} - {{ $vehicle->license_plate }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Start Location') }}</h4>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $trip->start_location }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('End Location') }}</h4>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $trip->end_location }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Purpose') }}</h4>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $trip->purpose }}</p>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('Odometer & Distance') }}</h3>
                            
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Start Odometer') }}</h4>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $trip->start_odometer }} km</p>
                            </div>
                            
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('End Odometer') }}</h4>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $trip->end_odometer }} km</p>
                            </div>
                            
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Distance') }}</h4>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $trip->distance }} km</p>
                            </div>
                            
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Driver Name') }}</h4>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $trip->driver_name }}</p>
                            </div>
                        </div>
                    </div>
                    
                    @if($trip->fuel_amount || $trip->fuel_cost || $trip->fuel_receipt_number)
                        <div class="mt-8">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('Fuel Information') }}</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                @if($trip->fuel_amount)
                                    <div class="mb-4">
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Fuel Amount') }}</h4>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $trip->fuel_amount }} liters</p>
                                    </div>
                                @endif
                                
                                @if($trip->fuel_cost)
                                    <div class="mb-4">
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Fuel Cost') }}</h4>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $trip->fuel_cost }} EUR</p>
                                    </div>
                                @endif
                                
                                @if($trip->fuel_receipt_number)
                                    <div class="mb-4">
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Receipt Number') }}</h4>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $trip->fuel_receipt_number }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    
                    <div class="mt-8 flex justify-end">
                        <form action="{{ route('trips.destroy', $trip) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 dark:bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white dark:text-white uppercase tracking-widest hover:bg-red-700 dark:hover:bg-red-700 focus:bg-red-700 dark:focus:bg-red-700 active:bg-red-800 dark:active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150" onclick="return confirm('{{ __('Are you sure you want to delete this trip?') }}')">
                                {{ __('Delete Trip') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>