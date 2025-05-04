<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Vytvoriť novú faktúru') }}
            </h2>
            <a href="{{ route('invoices.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Späť na zoznam
            </a>
        </div>
    </x-slot>

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

    <form action="{{ route('invoices.store') }}" method="POST" id="invoice-form">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Company Information -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Údaje o spoločnosti</h3>
                
                <div class="mb-4">
                    <label for="ico" class="block text-sm font-medium text-gray-700">IČO</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <input type="text" name="ico" id="ico" value="{{ old('ico') }}" class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-md sm:text-sm border-gray-300" placeholder="Zadajte IČO">
                        <button type="button" id="fetch-company-btn" class="ml-2 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Načítať údaje
                        </button>
                    </div>
                    <div id="company-message" class="text-sm text-gray-500 mt-1 hidden"></div>
                </div>
                
                <div id="company-details" class="space-y-4">
                    <div>
                        <label for="company_name" class="block text-sm font-medium text-gray-700">Názov spoločnosti</label>
                        <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50">
                    </div>
                    
                    <div>
                        <label for="company_address" class="block text-sm font-medium text-gray-700">Adresa</label>
                        <input type="text" id="company_address" name="company_address" value="{{ old('company_address') }}" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50">
                    </div>
                    
                    <div>
                        <label for="company_city" class="block text-sm font-medium text-gray-700">Mesto</label>
                        <input type="text" id="company_city" name="company_city" value="{{ old('company_city') }}" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50">
                    </div>
                    
                    <div>
                        <label for="company_postal_code" class="block text-sm font-medium text-gray-700">PSČ</label>
                        <input type="text" id="company_postal_code" name="company_postal_code" value="{{ old('company_postal_code') }}" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50">
                    </div>
                    
                    <div>
                        <label for="company_dic" class="block text-sm font-medium text-gray-700">DIČ</label>
                        <input type="text" id="company_dic" name="company_dic" value="{{ old('company_dic') }}" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50">
                    </div>
                    
                    <div>
                        <label for="company_ic_dph" class="block text-sm font-medium text-gray-700">IČ DPH</label>
                        <input type="text" id="company_ic_dph" name="company_ic_dph" value="{{ old('company_ic_dph') }}" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50">
                    </div>
                </div>
            </div>
            
            <!-- Invoice Information -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Údaje o faktúre</h3>
                
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="invoice_number" class="block text-sm font-medium text-gray-700">Číslo faktúry</label>
                        <input type="text" name="invoice_number" id="invoice_number" value="{{ old('invoice_number', 'INVOICE-' . date('Ymd') . '-' . rand(1000, 9999)) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="issue_date" class="block text-sm font-medium text-gray-700">Dátum vystavenia</label>
                        <input type="date" name="issue_date" id="issue_date" value="{{ old('issue_date', date('Y-m-d')) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700">Dátum splatnosti</label>
                        <input type="date" name="due_date" id="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+14 days'))) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Stav faktúry</label>
                        <select name="status" id="status" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Koncept</option>
                            <option value="sent" {{ old('status') == 'sent' ? 'selected' : '' }}>Odoslaná</option>
                            <option value="paid" {{ old('status') == 'paid' ? 'selected' : '' }}>Zaplatená</option>
                            <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Zrušená</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700">Mena</label>
                        <select name="currency" id="currency" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="EUR" {{ old('currency', 'EUR') == 'EUR' ? 'selected' : '' }}>EUR</option>
                            <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                            <option value="CZK" {{ old('currency') == 'CZK' ? 'selected' : '' }}>CZK</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="note" class="block text-sm font-medium text-gray-700">Poznámka</label>
                        <textarea name="note" id="note" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('note') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Invoice Items -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Položky faktúry</h3>
            
            <div class="flex flex-col">
                <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                        <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200" id="items-table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Popis</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Množstvo</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jednotková cena</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Spolu</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Akcie</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="items-container">
                                    <tr class="item-row">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="text" name="items[0][description]" class="item-description border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" placeholder="Zadajte popis položky" required>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" name="items[0][quantity]" class="item-quantity border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm w-20" min="1" value="1" required>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" name="items[0][unit_price]" class="item-price border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm w-32" min="0" step="0.01" placeholder="0.00" required>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap item-total">
                                            0.00
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button type="button" class="text-red-600 hover:text-red-900 remove-item" onclick="removeItem(this)">Odstrániť</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="button" id="add-item" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Pridať položku
                    </button>
                </div>
                
                <div class="mt-6 text-right">
                    <div class="text-sm text-gray-500">Medzisúčet: <span id="subtotal">0.00</span> <span id="currency-display">EUR</span></div>
                    <div class="text-sm text-gray-500">DPH (20%): <span id="vat">0.00</span> <span id="currency-display2">EUR</span></div>
                    <div class="text-lg font-bold">Spolu: <span id="grand-total">0.00</span> <span id="currency-display3">EUR</span></div>
                    <input type="hidden" name="total_amount" id="total_amount" value="0">
                </div>
            </div>
        </div>
        
        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Vytvoriť faktúru
            </button>
        </div>
    </form>

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
                        document.getElementById('company_name').value = data.data.name;
                        document.getElementById('company_address').value = data.data.street;
                        document.getElementById('company_city').value = data.data.city;
                        document.getElementById('company_postal_code').value = data.data.postal_code;
                        document.getElementById('company_dic').value = data.data.dic;
                        document.getElementById('company_ic_dph').value = data.data.ic_dph;
                        
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
            
            // Add item row
            document.getElementById('add-item').addEventListener('click', function() {
                const itemsContainer = document.getElementById('items-container');
                const itemCount = itemsContainer.getElementsByClassName('item-row').length;
                
                const newRow = document.createElement('tr');
                newRow.className = 'item-row';
                newRow.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="text" name="items[${itemCount}][description]" class="item-description border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" placeholder="Zadajte popis položky" required>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="number" name="items[${itemCount}][quantity]" class="item-quantity border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm w-20" min="1" value="1" required>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="number" name="items[${itemCount}][unit_price]" class="item-price border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm w-32" min="0" step="0.01" placeholder="0.00" required>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap item-total">
                        0.00
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button type="button" class="text-red-600 hover:text-red-900 remove-item" onclick="removeItem(this)">Odstrániť</button>
                    </td>
                `;
                
                itemsContainer.appendChild(newRow);
                
                // Add event listeners to new inputs
                const newQuantityInput = newRow.querySelector('.item-quantity');
                const newPriceInput = newRow.querySelector('.item-price');
                
                newQuantityInput.addEventListener('input', updateTotal);
                newPriceInput.addEventListener('input', updateTotal);
            });
            
            // Update currency display when changed
            document.getElementById('currency').addEventListener('change', function() {
                const currency = this.value;
                document.getElementById('currency-display').textContent = currency;
                document.getElementById('currency-display2').textContent = currency;
                document.getElementById('currency-display3').textContent = currency;
                updateTotal();
            });
            
            // Calculate item totals
            function updateItemTotal(row) {
                const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
                const price = parseFloat(row.querySelector('.item-price').value) || 0;
                const total = quantity * price;
                row.querySelector('.item-total').textContent = total.toFixed(2);
                return total;
            }
            
            // Calculate invoice total
            function updateTotal() {
                const rows = document.querySelectorAll('.item-row');
                let subtotal = 0;
                
                rows.forEach(row => {
                    subtotal += updateItemTotal(row);
                });
                
                const vat = subtotal * 0.2; // 20% VAT
                const grandTotal = subtotal + vat;
                
                document.getElementById('subtotal').textContent = subtotal.toFixed(2);
                document.getElementById('vat').textContent = vat.toFixed(2);
                document.getElementById('grand-total').textContent = grandTotal.toFixed(2);
                document.getElementById('total_amount').value = grandTotal.toFixed(2);
            }
            
            // Remove item row
            window.removeItem = function(button) {
                const row = button.closest('tr');
                const table = document.getElementById('items-container');
                
                // Don't allow removing the last row
                if (table.querySelectorAll('.item-row').length > 1) {
                    row.remove();
                    // Update indices for the remaining item inputs
                    const rows = table.querySelectorAll('.item-row');
                    rows.forEach((row, index) => {
                        row.querySelectorAll('input').forEach(input => {
                            const name = input.getAttribute('name');
                            const newName = name.replace(/items\[\d+\]/, `items[${index}]`);
                            input.setAttribute('name', newName);
                        });
                    });
                    updateTotal();
                } else {
                    alert('Faktúra musí obsahovať aspoň jednu položku');
                }
            };
            
            // Add event listeners to initial row inputs
            const quantityInputs = document.querySelectorAll('.item-quantity');
            const priceInputs = document.querySelectorAll('.item-price');
            
            quantityInputs.forEach(input => {
                input.addEventListener('input', updateTotal);
            });
            
            priceInputs.forEach(input => {
                input.addEventListener('input', updateTotal);
            });
            
            // Initialize totals
            updateTotal();
            
            // Form validation before submit
            document.getElementById('invoice-form').addEventListener('submit', function(e) {
                const ico = document.getElementById('ico').value;
                const invoiceNumber = document.getElementById('invoice_number').value;
                const issueDate = document.getElementById('issue_date').value;
                const dueDate = document.getElementById('due_date').value;
                
                // Check if company data is loaded
                if (!document.getElementById('company_name').value) {
                    e.preventDefault();
                    alert('Zadajte IČO a načítajte údaje o spoločnosti');
                    return;
                }
                
                // Check if all required fields are filled
                if (!ico || !invoiceNumber || !issueDate || !dueDate) {
                    e.preventDefault();
                    alert('Vyplňte všetky povinné polia faktúry');
                    return;
                }
                
                // Check if at least one item has a description and price
                const rows = document.querySelectorAll('.item-row');
                let validItems = false;
                
                rows.forEach(row => {
                    const description = row.querySelector('.item-description').value;
                    const price = parseFloat(row.querySelector('.item-price').value) || 0;
                    
                    if (description && price > 0) {
                        validItems = true;
                    }
                });
                
                if (!validItems) {
                    e.preventDefault();
                    alert('Pridajte aspoň jednu položku s popisom a cenou');
                    return;
                }
            });
        });
    </script>
</x-app-layout>
