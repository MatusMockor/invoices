<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $company->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Company Details</h3>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700 dark:text-gray-300">
                            <div>
                                <p class="mb-2"><span class="font-medium">Name:</span> {{ $company->name }}</p>
                                <p class="mb-2"><span class="font-medium">Street:</span> {{ $company->street ?: 'N/A' }}</p>
                                <p class="mb-2"><span class="font-medium">City:</span> {{ $company->city ?: 'N/A' }}</p>
                                <p class="mb-2"><span class="font-medium">Postal Code:</span> {{ $company->postal_code ?: 'N/A' }}</p>
                                <p class="mb-2"><span class="font-medium">Country:</span> {{ $company->country ?: 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="mb-2"><span class="font-medium">ICO:</span> {{ $company->ico ?: 'N/A' }}</p>
                                <p class="mb-2"><span class="font-medium">DIC:</span> {{ $company->dic ?: 'N/A' }}</p>
                                <p class="mb-2"><span class="font-medium">IC DPH:</span> {{ $company->ic_dph ?: 'N/A' }}</p>
                                <p class="mb-2"><span class="font-medium">Phone:</span> {{ $company->phone ?: 'N/A' }}</p>
                                <p class="mb-2"><span class="font-medium">Email:</span> {{ $company->email ?: 'N/A' }}</p>
                                <p class="mb-2"><span class="font-medium">Website:</span> {{ $company->website ?: 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex space-x-4">
                        <a href="{{ route('companies.edit', $company) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Edit Company
                        </a>
                        
                        <form method="POST" action="{{ route('companies.destroy', $company) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150" onclick="return confirm('Are you sure you want to delete this company?')">
                                Delete Company
                            </button>
                        </form>
                        
                        <a href="{{ route('companies.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-500 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Back to Companies
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>