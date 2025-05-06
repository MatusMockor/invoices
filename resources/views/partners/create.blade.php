<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Add New Partner') }}
            </h2>
            <a href="{{ route('partners.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                {{ __('Back to Partners') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('partners.store') }}" class="space-y-8">
                @csrf
                
                <!-- Basic Information Card -->
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                            </svg>
                            {{ __('Partner Basic Information') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Enter the basic details of your partner.') }}</p>
                    </div>
                    <div class="p-6 bg-white dark:bg-gray-800 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="name" :value="__('Company Name *')" class="font-semibold" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus placeholder="Partner company name" />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="ico" :value="__('IČO *')" class="font-semibold" />
                                <x-text-input id="ico" class="block mt-1 w-full" type="text" name="ico" :value="old('ico')" required placeholder="12345678" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Company identification number') }}</p>
                                <x-input-error :messages="$errors->get('ico')" class="mt-2" />
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="dic" :value="__('DIČ')" class="font-semibold" />
                                <x-text-input id="dic" class="block mt-1 w-full" type="text" name="dic" :value="old('dic')" placeholder="1234567890" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Tax identification number') }}</p>
                                <x-input-error :messages="$errors->get('dic')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="ic_dph" :value="__('IČ DPH')" class="font-semibold" />
                                <x-text-input id="ic_dph" class="block mt-1 w-full" type="text" name="ic_dph" :value="old('ic_dph')" placeholder="SK1234567890" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('VAT identification number') }}</p>
                                <x-input-error :messages="$errors->get('ic_dph')" class="mt-2" />
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
                            {{ __('Address Information') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Enter the address details of your partner.') }}</p>
                    </div>
                    <div class="p-6 bg-white dark:bg-gray-800 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="street" :value="__('Street Address *')" class="font-semibold" />
                                <x-text-input id="street" class="block mt-1 w-full" type="text" name="street" :value="old('street')" required placeholder="123 Business St." />
                                <x-input-error :messages="$errors->get('street')" class="mt-2" />
                            </div>
                            
                            <div class="grid grid-cols-3 gap-4">
                                <div class="col-span-2">
                                    <x-input-label for="city" :value="__('City *')" class="font-semibold" />
                                    <x-text-input id="city" class="block mt-1 w-full" type="text" name="city" :value="old('city')" required placeholder="Bratislava" />
                                    <x-input-error :messages="$errors->get('city')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="postal_code" :value="__('Postal Code *')" class="font-semibold" />
                                    <x-text-input id="postal_code" class="block mt-1 w-full" type="text" name="postal_code" :value="old('postal_code')" required placeholder="10001" />
                                    <x-input-error :messages="$errors->get('postal_code')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <x-input-label for="country" :value="__('Country *')" class="font-semibold" />
                            <x-text-input id="country" class="block mt-1 w-full" type="text" name="country" :value="old('country', 'Slovakia')" required placeholder="Slovakia" />
                            <x-input-error :messages="$errors->get('country')" class="mt-2" />
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
                            {{ __('Legal Information') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Enter the legal details of your partner.') }}</p>
                    </div>
                    <div class="p-6 bg-white dark:bg-gray-800 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="company_type" :value="__('Právna forma *')" class="font-semibold" />
                                <select id="company_type" name="company_type" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full" required>
                                    <option value="">-- Vyberte právnu formu --</option>
                                    <option value="živnosť" {{ old('company_type') == 'živnosť' ? 'selected' : '' }}>Živnosť</option>
                                    <option value="s.r.o." {{ old('company_type') == 's.r.o.' ? 'selected' : '' }}>s.r.o.</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Legal form of the company') }}</p>
                                <x-input-error :messages="$errors->get('company_type')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="registration_number" :value="__('Registračné číslo *')" class="font-semibold" />
                                <x-text-input id="registration_number" class="block mt-1 w-full" type="text" name="registration_number" :value="old('registration_number')" required placeholder="Obchodný register / Živnostenský register" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Registration number in business or trade register') }}</p>
                                <x-input-error :messages="$errors->get('registration_number')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="flex items-center justify-end space-x-4">
                    <a href="{{ route('partners.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 dark:bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 dark:hover:bg-green-700 focus:bg-green-700 dark:focus:bg-green-700 active:bg-green-900 dark:active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        {{ __('Create Partner') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
