<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Company') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('companies.update', $company) }}" class="space-y-8">
                @csrf
                @method('PUT')

                <!-- Company Information Card -->
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd" />
                            </svg>
                            {{ __('Company Basic Information') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Update the basic details of your company.') }}</p>
                    </div>
                    <div class="p-6 bg-white dark:bg-gray-800 space-y-6">
                        <!-- Company Name -->
                        <div>
                            <x-input-label for="name" :value="__('Company Name')" class="font-semibold" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $company->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Address -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="street" :value="__('Street Address')" class="font-semibold" />
                                <x-text-input id="street" class="block mt-1 w-full" type="text" name="street" :value="old('street', $company->street)" />
                                <x-input-error :messages="$errors->get('street')" class="mt-2" />
                            </div>
                            
                            <div class="grid grid-cols-3 gap-4">
                                <div class="col-span-2">
                                    <x-input-label for="city" :value="__('City')" class="font-semibold" />
                                    <x-text-input id="city" class="block mt-1 w-full" type="text" name="city" :value="old('city', $company->city)" />
                                    <x-input-error :messages="$errors->get('city')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="postal_code" :value="__('Postal Code')" class="font-semibold" />
                                    <x-text-input id="postal_code" class="block mt-1 w-full" type="text" name="postal_code" :value="old('postal_code', $company->postal_code)" />
                                    <x-input-error :messages="$errors->get('postal_code')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div>
                            <x-input-label for="country" :value="__('Country')" class="font-semibold" />
                            <x-text-input id="country" class="block mt-1 w-full" type="text" name="country" :value="old('country', $company->country)" />
                            <x-input-error :messages="$errors->get('country')" class="mt-2" />
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
                            {{ __('Business Registration Details') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Update your company\'s registration and tax information.') }}</p>
                    </div>
                    <div class="p-6 bg-white dark:bg-gray-800">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <!-- ICO -->
                            <div>
                                <x-input-label for="ico" :value="__('IČO')" class="font-semibold" />
                                <x-text-input id="ico" class="block mt-1 w-full" type="text" name="ico" :value="old('ico', $company->ico)" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Company identification number') }}</p>
                                <x-input-error :messages="$errors->get('ico')" class="mt-2" />
                            </div>
                        
                            <!-- DIC -->
                            <div>
                                <x-input-label for="dic" :value="__('DIČ')" class="font-semibold" />
                                <x-text-input id="dic" class="block mt-1 w-full" type="text" name="dic" :value="old('dic', $company->dic)" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Tax identification number') }}</p>
                                <x-input-error :messages="$errors->get('dic')" class="mt-2" />
                            </div>
                        
                            <!-- IC DPH -->
                            <div>
                                <x-input-label for="ic_dph" :value="__('IČ DPH')" class="font-semibold" />
                                <x-text-input id="ic_dph" class="block mt-1 w-full" type="text" name="ic_dph" :value="old('ic_dph', $company->ic_dph)" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('VAT identification number') }}</p>
                                <x-input-error :messages="$errors->get('ic_dph')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- IBAN -->
                            <div>
                                <x-input-label for="iban" :value="__('IBAN')" class="font-semibold" />
                                <x-text-input id="iban" class="block mt-1 w-full" type="text" name="iban" :value="old('iban', $company->iban)" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('International Bank Account Number') }}</p>
                                <x-input-error :messages="$errors->get('iban')" class="mt-2" />
                            </div>
                        
                            <!-- SWIFT -->
                            <div>
                                <x-input-label for="swift" :value="__('SWIFT/BIC')" class="font-semibold" />
                                <x-text-input id="swift" class="block mt-1 w-full" type="text" name="swift" :value="old('swift', $company->swift)" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Bank Identifier Code') }}</p>
                                <x-input-error :messages="$errors->get('swift')" class="mt-2" />
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
                            {{ __('Contact Information') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Update contact details for your company.') }}</p>
                    </div>
                    <div class="p-6 bg-white dark:bg-gray-800">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Phone -->
                            <div>
                                <x-input-label for="phone" :value="__('Phone')" class="font-semibold" />
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                                        </svg>
                                    </span>
                                    <x-text-input id="phone" type="text" name="phone" :value="old('phone', $company->phone)" class="flex-1 block w-full rounded-none rounded-r-md" />
                                </div>
                                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                            </div>
                            
                            <!-- Email -->
                            <div>
                                <x-input-label for="email" :value="__('Email')" class="font-semibold" />
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                        </svg>
                                    </span>
                                    <x-text-input id="email" type="email" name="email" :value="old('email', $company->email)" class="flex-1 block w-full rounded-none rounded-r-md" />
                                </div>
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <!-- Website -->
                            <div>
                                <x-input-label for="website" :value="__('Website')" class="font-semibold" />
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4.083 9h1.946c.089-1.546.383-2.97.837-4.118A6.004 6.004 0 004.083 9zM10 2a8 8 0 100 16 8 8 0 000-16zm0 2c-.076 0-.232.032-.465.262-.238.234-.497.623-.737 1.182-.389.907-.673 2.142-.766 3.556h3.936c-.093-1.414-.377-2.649-.766-3.556-.24-.56-.5-.948-.737-1.182C10.232 4.032 10.076 4 10 4zm3.971 5c-.089-1.546-.383-2.97-.837-4.118A6.004 6.004 0 0115.917 9h-1.946zm-2.003 2H8.032c.093 1.414.377 2.649.766 3.556.24.56.5.948.737 1.182.233.23.389.262.465.262.076 0 .232-.032.465-.262.238-.234.498-.623.737-1.182.389-.907.673-2.142.766-3.556zm1.166 4.118c.454-1.147.748-2.572.837-4.118h1.946a6.004 6.004 0 01-2.783 4.118zm-6.268 0C6.412 13.97 6.118 12.546 6.03 11H4.083a6.004 6.004 0 002.783 4.118z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                    <x-text-input id="website" type="url" name="website" :value="old('website', $company->website)" class="flex-1 block w-full rounded-none rounded-r-md" />
                                </div>
                                <x-input-error :messages="$errors->get('website')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-4">
                    <a href="{{ route('companies.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                        {{ __('Update Company') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
