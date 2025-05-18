<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Upraviť faktúru') }} {{ $invoice->invoice_number }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('invoices.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Späť na zoznam
                </a>
                <a href="{{ route('invoices.show', $invoice) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Zobraziť detail
                </a>
            </div>
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

    <form action="{{ route('invoices.update', $invoice) }}" method="POST" id="invoice-form">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                        <invoice-form
                                :fetch-company-url="'{{ route('business-entities.fetch-by-ico') }}'"
                                submit-url="{{ route('invoices.update', $invoice) }}"
                                csrf-token="{{ csrf_token() }}"
                                method="PUT"
                                :invoice-data="{{ json_encode([
                        'invoice_number' => $invoice->invoice_number,
                        'issue_date' => $invoice->issue_date->format('Y-m-d'),
                        'due_date' => $invoice->due_date->format('Y-m-d'),
                        'delivery_date' => $invoice->delivery_date ? $invoice->delivery_date->format('Y-m-d') : null,
                        'status' => $invoice->status,
                        'currency' => $invoice->currency,
                        'constant_symbol' => $invoice->constant_symbol,
                        'note' => $invoice->note,
                        'businessEntity' => [
                            'ico' => $invoice->businessEntity->ico,
                            'name' => $invoice->businessEntity->name,
                            'street' => $invoice->businessEntity->street,
                            'city' => $invoice->businessEntity->city,
                            'postal_code' => $invoice->businessEntity->postal_code,
                            'dic' => $invoice->businessEntity->dic,
                            'ic_dph' => $invoice->businessEntity->ic_dph
                        ],
                        'items' => $invoice->items->map(function($item) {
                            return [
                                'id' => $item->id,
                                'description' => $item->description,
                                'quantity' => $item->quantity,
                                'unit_price' => $item->unit_price
                            ];
                        })
                    ], JSON_THROW_ON_ERROR) }}"
                        ></invoice-form>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>
