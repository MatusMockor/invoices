<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pridať novú spoločnosť') }}
            </h2>
            <a href="{{ route('companies.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Späť na zoznam
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if ($errors->any())
                        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Chyba!</strong>
                            <span class="block sm:inline">Prosím opravte nasledujúce chyby:</span>
                            <ul class="mt-3 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('companies.store') }}" method="POST">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="mb-4">
                                    <label for="ico" class="block text-sm font-medium text-gray-700">IČO *</label>
                                    <div class="mt-1 flex rounded-md shadow-sm">
                                        <input type="text" name="ico" id="ico" value="{{ old('ico') }}" class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-md sm:text-sm border-gray-300" placeholder="Zadajte IČO" required>
                                        <button type="button" id="fetch-company-btn" class="ml-2 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Načítať údaje
                                        </button>
                                    </div>
                                    <div id="company-message" class="text-sm text-gray-500 mt-1 hidden"></div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="name" class="block text-sm font-medium text-gray-700">Názov spoločnosti *</label>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="street" class="block text-sm font-medium text-gray-700">Ulica a číslo *</label>
                                    <input type="text" name="street" id="street" value="{{ old('street') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="city" class="block text-sm font-medium text-gray-700">Mesto *</label>
                                    <input type="text" name="city" id="city" value="{{ old('city') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                </div>
                            </div>
                            
                            <div>
                                <div class="mb-4">
                                    <label for="postal_code" class="block text-sm font-medium text-gray-700">PSČ *</label>
                                    <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="country" class="block text-sm font-medium text-gray-700">Krajina *</label>
                                    <input type="text" name="country" id="country" value="{{ old('country', 'Slovensko') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="dic" class="block text-sm font-medium text-gray-700">DIČ</label>
                                    <input type="text" name="dic" id="dic" value="{{ old('dic') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="ic_dph" class="block text-sm font-medium text-gray-700">IČ DPH</label>
                                    <input type="text" name="ic_dph" id="ic_dph" value="{{ old('ic_dph') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="note" class="block text-sm font-medium text-gray-700">Poznámka</label>
                            <textarea name="note" id="note" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('note') }}</textarea>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Pridať spoločnosť
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fetch company data when button is clicked
            document.getElementById('fetch-company-btn').addEventListener('click', function() {
                const ico = document.getElementById('ico').value;
                const messageDiv = document.getElementById('company-message');
                
                if (!ico) {
                    messageDiv.textContent = 'Zadajte IČO';
                    messageDiv.classList.remove('hidden', 'text-green-500');
                    messageDiv.classList.add('text-red-500');
                    return;
                }
                
                messageDiv.textContent = 'Načítavam údaje...';
                messageDiv.classList.remove('hidden', 'text-red-500', 'text-green-500');
                
                fetch('{{ route('companies.fetch-by-ico') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ ico: ico })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('name').value = data.data.name;
                        document.getElementById('street').value = data.data.street;
                        document.getElementById('city').value = data.data.city;
                        document.getElementById('postal_code').value = data.data.postal_code;
                        document.getElementById('dic').value = data.data.dic;
                        document.getElementById('ic_dph').value = data.data.ic_dph;
                        
                        messageDiv.textContent = 'Údaje úspešne načítané';
                        messageDiv.classList.remove('text-red-500');
                        messageDiv.classList.add('text-green-500');
                    } else {
                        messageDiv.textContent = data.message || 'Nepodarilo sa načítať údaje';
                        messageDiv.classList.remove('text-green-500');
                        messageDiv.classList.add('text-red-500');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    messageDiv.textContent = 'Chyba pri načítaní údajov';
                    messageDiv.classList.remove('text-green-500');
                    messageDiv.classList.add('text-red-500');
                });
            });
        });
    </script>
</x-app-layout>
