<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
                {{ __('Detail faktúry') }} {{ $invoice->invoice_number }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('invoices.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-gray-600 focus:bg-gray-700 dark:focus:bg-gray-600 active:bg-gray-900 dark:active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Späť na zoznam
                </a>
                <a href="{{ route('invoices.edit', $invoice) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Upraviť faktúru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Invoice Header -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">FAKTÚRA č. {{ $invoice->invoice_number }}</h1>

                            @php
                                $statusColors = [
                                    'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                    'sent' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                    'paid' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                    'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                ];
                                $statusLabels = [
                                    'draft' => 'Koncept',
                                    'sent' => 'Odoslaná',
                                    'paid' => 'Zaplatená',
                                    'cancelled' => 'Zrušená',
                                ];
                            @endphp
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $statusColors[$invoice->status] }}">
                                {{ $statusLabels[$invoice->status] }}
                            </span>
                        </div>

                        <div class="mt-4 md:mt-0 text-right">
                            <p class="text-gray-600 dark:text-gray-400">Dátum vystavenia: <span class="font-semibold text-gray-900 dark:text-white">{{ $invoice->issue_date->format('d.m.Y') }}</span></p>
                            <p class="text-gray-600 dark:text-gray-400">Dátum splatnosti: <span class="font-semibold text-gray-900 dark:text-white">{{ $invoice->due_date->format('d.m.Y') }}</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Company Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Supplier (Your Company) -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Dodávateľ</h3>

                        <div class="space-y-2 text-gray-700 dark:text-gray-300">
                            <p class="font-bold text-lg text-gray-900 dark:text-white">{{$invoice->supplierCompany->name}}</p>
                            <p>{{$invoice->supplierCompany->street}}</p>
                            <p>{{$invoice->supplierCompany->postal_code .' '. $invoice->supplierCompany->city}}</p>
                            <p>{{$invoice->supplierCompany->country}}</p>
                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700 mt-4">
                                <p>IČO: {{$invoice->supplierCompany->ico}}</p>
                                <p>DIČ: {{$invoice->supplierCompany->dic}}</p>
                                <p>IČ DPH: {{$invoice->supplierCompany->ic_dph}}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Odberateľ</h3>

                        <div class="space-y-2 text-gray-700 dark:text-gray-300">
                            <p class="font-bold text-lg text-gray-900 dark:text-white">{{ $invoice->partner->name }}</p>
                            <p>{{ $invoice->partner->street }}</p>
                            <p>{{ $invoice->partner->postal_code }} {{ $invoice->partner->city }}</p>
                            <p>{{ $invoice->partner->country }}</p>
                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700 mt-4">
                                <p>IČO: {{ $invoice->partner->ico }}</p>
                                @if($invoice->partner->dic)
                                    <p>DIČ: {{ $invoice->partner->dic }}</p>
                                @endif
                                @if($invoice->partner->ic_dph)
                                    <p>IČ DPH: {{ $invoice->partner->ic_dph }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Položky faktúry</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700">
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Popis</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Množstvo</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Jednotková cena</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Spolu</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @php
                                    $subtotal = 0;
                                @endphp

                                @foreach($invoice->items as $item)
                                    @php
                                        $subtotal += $item->total_price;
                                    @endphp
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $item->description }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                            {{ $item->quantity }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                            {{ number_format($item->unit_price, 2, ',', ' ') }} {{ $invoice->currency }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                            {{ number_format($item->total_price, 2, ',', ' ') }} {{ $invoice->currency }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50 dark:bg-gray-700">
                                    <td colspan="3" class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white text-right">
                                        Medzisúčet:
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ number_format($subtotal, 2, ',', ' ') }} {{ $invoice->currency }}
                                    </td>
                                </tr>
                                <tr class="bg-gray-50 dark:bg-gray-700">
                                    <td colspan="3" class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white text-right">
                                        DPH (20%):
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ number_format($subtotal * 0.2, 2, ',', ' ') }} {{ $invoice->currency }}
                                    </td>
                                </tr>
                                <tr class="bg-gray-50 dark:bg-gray-700">
                                    <td colspan="3" class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white text-right">
                                        Celkom:
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">
                                        {{ number_format($invoice->total_amount, 2, ',', ' ') }} {{ $invoice->currency }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment Info and Notes -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Platobné údaje</h3>

                        <div class="space-y-3 text-gray-700 dark:text-gray-300">
                            <p><span class="font-medium">Banka:</span> Slovenská sporiteľňa, a.s.</p>
                            <p><span class="font-medium">IBAN:</span> SK12 0900 0000 0001 2345 6789</p>
                            <p><span class="font-medium">SWIFT:</span> GIBASKBX</p>
                            <p><span class="font-medium">Variabilný symbol:</span> {{ preg_replace('/[^0-9]/', '', $invoice->invoice_number) }}</p>
                            @if($invoice->constant_symbol)
                            <p><span class="font-medium">Konštantný symbol:</span> {{ $invoice->constant_symbol }}</p>
                            @endif
                            <p class="mt-4 text-lg font-bold text-gray-900 dark:text-white"><span class="font-medium">Suma na úhradu:</span> {{ number_format($invoice->total_amount, 2, ',', ' ') }} {{ $invoice->currency }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Dodatočné informácie</h3>

                        @if($invoice->note)
                            <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Poznámka k faktúre:</h4>
                                <p class="text-gray-600 dark:text-gray-400">{{ $invoice->note }}</p>
                            </div>
                        @endif

                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-4">
                            <p>Faktúra bola vygenerovaná elektronicky a je platná bez podpisu a pečiatky.</p>
                            <p>Dodávateľ je zapísaný v Obchodnom registri Okresného súdu Bratislava I, oddiel: Sro, vložka č.: 12345/B.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 flex flex-col sm:flex-row justify-between items-center">
                    <div class="mb-4 sm:mb-0 flex space-x-2">
                        <a href="{{ route('invoices.pdf.download', $invoice) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Stiahnuť PDF
                        </a>
                        <a href="{{ route('invoices.pdf.view', $invoice) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-500 focus:bg-purple-500 active:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Zobraziť PDF
                        </a>
                    </div>

                    <div class="flex space-x-2">
                        <a href="{{ route('invoices.edit', $invoice) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Upraviť
                        </a>
                        <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('Naozaj chcete vymazať túto faktúru?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Vymazať
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
