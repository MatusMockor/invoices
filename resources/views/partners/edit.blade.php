<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Partner') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('partners.update', $partner) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="name" :value="__('Company Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $partner->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="street" :value="__('Street')" />
                            <x-text-input id="street" class="block mt-1 w-full" type="text" name="street" :value="old('street', $partner->street)" required />
                            <x-input-error :messages="$errors->get('street')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="city" :value="__('City')" />
                            <x-text-input id="city" class="block mt-1 w-full" type="text" name="city" :value="old('city', $partner->city)" required />
                            <x-input-error :messages="$errors->get('city')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="postal_code" :value="__('Postal Code')" />
                            <x-text-input id="postal_code" class="block mt-1 w-full" type="text" name="postal_code" :value="old('postal_code', $partner->postal_code)" required />
                            <x-input-error :messages="$errors->get('postal_code')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="country" :value="__('Country')" />
                            <x-text-input id="country" class="block mt-1 w-full" type="text" name="country" :value="old('country', $partner->country)" required />
                            <x-input-error :messages="$errors->get('country')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="ico" :value="__('IČO')" />
                            <x-text-input id="ico" class="block mt-1 w-full" type="text" name="ico" :value="old('ico', $partner->ico)" required />
                            <x-input-error :messages="$errors->get('ico')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="dic" :value="__('DIČ')" />
                            <x-text-input id="dic" class="block mt-1 w-full" type="text" name="dic" :value="old('dic', $partner->dic)" />
                            <x-input-error :messages="$errors->get('dic')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="ic_dph" :value="__('IČ DPH')" />
                            <x-text-input id="ic_dph" class="block mt-1 w-full" type="text" name="ic_dph" :value="old('ic_dph', $partner->ic_dph)" />
                            <x-input-error :messages="$errors->get('ic_dph')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="company_type" :value="__('Právna forma')" />
                            <select id="company_type" name="company_type" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">-- Vyberte právnu formu --</option>
                                <option value="živnosť" {{ old('company_type', $partner->company_type) == 'živnosť' ? 'selected' : '' }}>Živnosť</option>
                                <option value="s.r.o." {{ old('company_type', $partner->company_type) == 's.r.o.' ? 'selected' : '' }}>s.r.o.</option>
                            </select>
                            <x-input-error :messages="$errors->get('company_type')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="registration_number" :value="__('Registračné číslo')" />
                            <x-text-input id="registration_number" class="block mt-1 w-full" type="text" name="registration_number" :value="old('registration_number', $partner->registration_number)" placeholder="Obchodný register / Živnostenský register" />
                            <x-input-error :messages="$errors->get('registration_number')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('partners.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Update Partner') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
