<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Companies') }}
            </h2>
            <a href="{{ route('companies.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                {{ __('Add Company') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if(count($companies) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($companies as $company)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-md rounded-lg hover:shadow-lg transition-shadow duration-300">
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 {{ auth()->user()->currentCompany?->id === $company->id ? 'bg-green-50 dark:bg-green-900' : 'bg-gray-50 dark:bg-gray-900' }}">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 truncate">
                                        {{ $company->name }}
                                    </h3>
                                    @if(auth()->user()->currentCompany?->id === $company->id)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('Current') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="mb-4 space-y-1">
                                    @if($company->city)
                                        <div class="flex items-start text-sm text-gray-600 dark:text-gray-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5 text-gray-400 dark:text-gray-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                            </svg>
                                            <span>{{ $company->city }}{{ $company->country ? ', ' . $company->country : '' }}</span>
                                        </div>
                                    @endif

                                    @if($company->ico)
                                        <div class="flex items-start text-sm text-gray-600 dark:text-gray-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5 text-gray-400 dark:text-gray-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                            </svg>
                                            <span>{{ __('IČO') }}: {{ $company->ico }}</span>
                                        </div>
                                    @endif

                                    @if($company->email)
                                        <div class="flex items-start text-sm text-gray-600 dark:text-gray-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5 text-gray-400 dark:text-gray-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                            </svg>
                                            <span class="truncate">{{ $company->email }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-6 flex justify-between items-center">
                                    <a href="{{ route('companies.show', $company) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                        {{ __('View Details') }} →
                                    </a>
                                    <div class="flex space-x-2">
                                        @if(auth()->user()->currentCompany?->id !== $company->id)
                                            <form method="POST" action="{{ route('companies.switch', $company) }}">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center text-xs px-2.5 py-1.5 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z" />
                                                    </svg>
                                                    {{ __('Set as Current') }}
                                                </button>
                                            </form>
                                        @endif
                                                                                    <a href="{{ route('companies.edit', $company) }}" class="inline-flex items-center text-xs px-2.5 py-1.5 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                            {{ __('Edit') }}
                                                                                    </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400 dark:text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('No Companies Found') }}</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">{{ __('You haven\'t created any companies yet. Get started by adding your first company.') }}</p>
                        <a href="{{ route('companies.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            {{ __('Create Your First Company') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- JavaScript for dropdown menus -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get all dropdown buttons
            const dropdownButtons = document.querySelectorAll('[id^="dropdown-button-"]');

            // Add click event listener to each button
            dropdownButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.id.replace('dropdown-button-', '');
                    const menu = document.getElementById(`dropdown-menu-${id}`);

                    // Toggle dropdown visibility
                    menu.classList.toggle('hidden');

                    // Update aria-expanded attribute
                    this.setAttribute('aria-expanded', menu.classList.contains('hidden') ? 'false' : 'true');
                });
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                dropdownButtons.forEach(button => {
                    const id = button.id.replace('dropdown-button-', '');
                    const menu = document.getElementById(`dropdown-menu-${id}`);

                    if (!button.contains(event.target) && !menu.contains(event.target) && !menu.classList.contains('hidden')) {
                        menu.classList.add('hidden');
                        button.setAttribute('aria-expanded', 'false');
                    }
                });
            });
        });
    </script>
</x-app-layout>
