<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Autorizacia - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto min-h-screen lg:py-0">
        <a href="/" class="flex items-center mb-6 text-2xl font-semibold text-gray-900 dark:text-white">
            <x-application-logo class="w-10 h-10 mr-2 text-gray-800 dark:text-white" />
            {{ config('app.name', 'Laravel') }}
        </a>

        <div class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-lg xl:p-0 dark:bg-gray-800 dark:border-gray-700">
            <div class="p-6 space-y-6 sm:p-8">
                <!-- Header -->
                <div class="text-center">
                    <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                        Autorizacia pristupu
                    </h1>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Aplikacia <span class="font-semibold text-gray-900 dark:text-white">{{ $client['name'] }}</span> ziada pristup k vasmu uctu.
                    </p>
                </div>

                <!-- Scopes -->
                <div class="space-y-4">
                    <h2 class="text-sm font-medium text-gray-900 dark:text-white">
                        Aplikacia bude mat pristup k:
                    </h2>
                    <ul class="space-y-3">
                        @foreach($scopes as $scope)
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $scope['name'] }}</span>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $scope['description'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Warning -->
                <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-yellow-700 dark:text-yellow-200">
                            Autorizaciu mozete kedykolvek zrusit v nastaveniach vasho uctu.
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col-reverse sm:flex-row sm:space-x-4 space-y-4 space-y-reverse sm:space-y-0">
                    <!-- Deny Form -->
                    <form method="POST" action="{{ route('oauth.authorize.deny') }}" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="auth_token" value="{{ $authToken }}">
                        <input type="hidden" name="client_id" value="{{ $request['client_id'] }}">
                        <input type="hidden" name="redirect_uri" value="{{ $request['redirect_uri'] }}">
                        <input type="hidden" name="response_type" value="{{ $request['response_type'] }}">
                        <input type="hidden" name="scope" value="{{ $request['scope'] }}">
                        <input type="hidden" name="state" value="{{ $request['state'] }}">
                        <button type="submit" class="w-full text-gray-900 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center border border-gray-300 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600 dark:focus:ring-gray-700">
                            Odmietnut
                        </button>
                    </form>

                    <!-- Approve Form -->
                    <form method="POST" action="{{ route('oauth.authorize.approve') }}" class="flex-1">
                        @csrf
                        <input type="hidden" name="auth_token" value="{{ $authToken }}">
                        <input type="hidden" name="client_id" value="{{ $request['client_id'] }}">
                        <input type="hidden" name="redirect_uri" value="{{ $request['redirect_uri'] }}">
                        <input type="hidden" name="response_type" value="{{ $request['response_type'] }}">
                        <input type="hidden" name="scope" value="{{ $request['scope'] }}">
                        <input type="hidden" name="state" value="{{ $request['state'] }}">
                        <button type="submit" class="w-full text-white bg-indigo-700 hover:bg-indigo-800 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-indigo-600 dark:hover:bg-indigo-700 dark:focus:ring-indigo-800">
                            Autorizovat
                        </button>
                    </form>
                </div>

                <!-- User Info -->
                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-xs text-center text-gray-500 dark:text-gray-400">
                        Prihlaseny ako <span class="font-medium">{{ auth()->user()->email }}</span>
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-indigo-600 hover:underline dark:text-indigo-400 ml-2">
                            Odhlasit sa
                        </a>
                    </p>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
