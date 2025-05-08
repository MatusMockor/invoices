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
            <company-form 
                :initial-data="{{ Illuminate\Support\Js::from($company) }}"
                submit-route="{{ route('companies.update', $company) }}"
                cancel-route="{{ route('companies.index') }}"
                fetch-company-route="{{ route('companies.fetch-by-ico') }}"
                submit-button-text="{{ __('Update Company') }}"
                :errors="{{ Illuminate\Support\Js::from($errors->get('*')) }}"
            ></company-form>
        </div>
    </div>
</x-app-layout>
