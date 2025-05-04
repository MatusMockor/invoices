<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $company->name }}
            </h2>
            <div class="flex space-x-2">
                @if(auth()->user()->currentCompany?->id !== $company->id)
                    <form method="POST" action="{{ route('companies.switch', $company) }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            {{ __('Set as Current') }}
                        </button>
                    </form>
                @else
                    <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full border border-green-200">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        {{ __('Current Company') }}
                    </span>
                @endif
                <a href="{{ route('companies.edit', $company) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    {{ __('Edit') }}
                </a>
                <form method="POST" action="{{ route('companies.destroy', $company) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this company?') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        {{ __('Delete') }}
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Company Basic Information -->
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd" />
                        </svg>
                        {{ __('Company Information') }}
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Address Info -->
                        <div>
                            <h4 class="font-semibold text-base mb-3 text-gray-700 dark:text-gray-300">{{ __('Address') }}</h4>
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <address class="not-italic">
                                    @if($company->street)<div>{{ $company->street }}</div>@endif
                                    @if($company->city || $company->postal_code)
                                        <div>{{ $company->city }} {{ $company->postal_code }}</div>
                                    @endif
                                    @if($company->country)<div>{{ $company->country }}</div>@endif
                                </address>
                            </div>
                        </div>

                        <!-- Registration Info -->
                        <div>
                            <h4 class="font-semibold text-base mb-3 text-gray-700 dark:text-gray-300">{{ __('Registration Information') }}</h4>
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <dl class="grid grid-cols-2 gap-x-6 gap-y-2">
                                    @if($company->ico)
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('IČO') }}:</dt>
                                        <dd>{{ $company->ico }}</dd>
                                    @endif
                                    
                                    @if($company->dic)
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('DIČ') }}:</dt>
                                        <dd>{{ $company->dic }}</dd>
                                    @endif
                                    
                                    @if($company->ic_dph)
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('IČ DPH') }}:</dt>
                                        <dd>{{ $company->ic_dph }}</dd>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banking Information -->
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                        {{ __('Banking Information') }}
                    </h3>
                </div>
                <div class="p-6">
                    @if($company->iban || $company->swift)
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                                @if($company->iban)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('IBAN') }}</dt>
                                        <dd class="mt-1 text-lg font-medium font-mono tracking-wide">{{ $company->iban }}</dd>
                                    </div>
                                @endif
                                
                                @if($company->swift)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('SWIFT/BIC') }}</dt>
                                        <dd class="mt-1 text-lg font-medium font-mono">{{ $company->swift }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @else
                        <div class="text-center py-4 text-gray-500 dark:text-gray-400 italic">
                            {{ __('No banking information provided.') }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                        </svg>
                        {{ __('Contact Information') }}
                    </h3>
                </div>
                <div class="p-6">
                    @if($company->phone || $company->email || $company->website)
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            @if($company->phone)
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <div class="flex items-center mb-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                                        </svg>
                                        <h4 class="font-medium text-gray-700 dark:text-gray-300">{{ __('Phone') }}</h4>
                                    </div>
                                    <a href="tel:{{ $company->phone }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $company->phone }}</a>
                                </div>
                            @endif

                            @if($company->email)
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <div class="flex items-center mb-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                        </svg>
                                        <h4 class="font-medium text-gray-700 dark:text-gray-300">{{ __('Email') }}</h4>
                                    </div>
                                    <a href="mailto:{{ $company->email }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $company->email }}</a>
                                </div>
                            @endif

                            @if($company->website)
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <div class="flex items-center mb-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4.083 9h1.946c.089-1.546.383-2.97.837-4.118A6.004 6.004 0 004.083 9zM10 2a8 8 0 100 16 8 8 0 000-16zm0 2c-.076 0-.232.032-.465.262-.238.234-.497.623-.737 1.182-.389.907-.673 2.142-.766 3.556h3.936c-.093-1.414-.377-2.649-.766-3.556-.24-.56-.5-.948-.737-1.182C10.232 4.032 10.076 4 10 4zm3.971 5c-.089-1.546-.383-2.97-.837-4.118A6.004 6.004 0 0115.917 9h-1.946zm-2.003 2H8.032c.093 1.414.377 2.649.766 3.556.24.56.5.948.737 1.182.233.23.389.262.465.262.076 0 .232-.032.465-.262.238-.234.498-.623.737-1.182.389-.907.673-2.142.766-3.556zm1.166 4.118c.454-1.147.748-2.572.837-4.118h1.946a6.004 6.004 0 01-2.783 4.118zm-6.268 0C6.412 13.97 6.118 12.546 6.03 11H4.083a6.004 6.004 0 002.783 4.118z" clip-rule="evenodd" />
                                        </svg>
                                        <h4 class="font-medium text-gray-700 dark:text-gray-300">{{ __('Website') }}</h4>
                                    </div>
                                    <a href="{{ $company->website }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $company->website }}</a>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-4 text-gray-500 dark:text-gray-400 italic">
                            {{ __('No contact information provided.') }}
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="mt-6 text-right">
                <a href="{{ route('companies.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    {{ __('Back to Companies') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>