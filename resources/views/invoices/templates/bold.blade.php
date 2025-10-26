<div class="bg-gray-900 text-white p-8 min-h-screen">
    <!-- Header -->
    <div class="mb-8 pb-6 border-b-4 border-cyan-500">
        <div class="flex justify-between items-center">
            <h1 class="text-4xl font-black text-cyan-400">InvoiceHub</h1>
            <div class="text-right">
                <p class="text-sm text-gray-400">FAKTÚRA</p>
                <p class="text-2xl font-black text-cyan-400">{{ $invoice->invoice_number }}</p>
            </div>
        </div>
    </div>

    <!-- Company Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Supplier -->
        <div class="bg-gray-800 border-l-4 border-cyan-500 p-6 rounded-lg">
            <h3 class="font-black text-cyan-400 mb-3 text-xs uppercase tracking-widest">Dodávateľ</h3>
            <p class="font-bold text-xl mb-2 text-white">{{ $invoice->supplierCompany->name ?? 'N/A' }}</p>
            <p class="text-sm text-gray-300">{{ $invoice->supplierCompany->street ?? '' }}</p>
            <p class="text-sm text-gray-300">{{ $invoice->supplierCompany->postal_code ?? '' }} {{ $invoice->supplierCompany->city ?? '' }}</p>
            <div class="mt-3 pt-3 border-t border-gray-700 text-sm text-gray-400 space-y-1">
                <p>IČO: <span class="text-white font-semibold">{{ $invoice->supplierCompany->ico ?? 'N/A' }}</span></p>
                <p>DIČ: <span class="text-white font-semibold">{{ $invoice->supplierCompany->dic ?? 'N/A' }}</span></p>
                @if($invoice->supplierCompany->ic_dph ?? false)
                    <p>IČ DPH: <span class="text-white font-semibold">{{ $invoice->supplierCompany->ic_dph }}</span></p>
                @endif
            </div>
        </div>

        <!-- Client -->
        <div class="bg-gray-800 border-l-4 border-purple-500 p-6 rounded-lg">
            <h3 class="font-black text-purple-400 mb-3 text-xs uppercase tracking-widest">Odberateľ</h3>
            <p class="font-bold text-xl mb-2 text-white">{{ $invoice->customer_name ?? 'N/A' }}</p>
            <p class="text-sm text-gray-300">{{ $invoice->customer_street ?? '' }}</p>
            <p class="text-sm text-gray-300">{{ $invoice->customer_postal_code ?? '' }} {{ $invoice->customer_city ?? '' }}</p>
            <div class="mt-3 pt-3 border-t border-gray-700 text-sm text-gray-400 space-y-1">
                <p>IČO: <span class="text-white font-semibold">{{ $invoice->customer_ico ?? 'N/A' }}</span></p>
                <p>DIČ: <span class="text-white font-semibold">{{ $invoice->customer_dic ?? 'N/A' }}</span></p>
                @if($invoice->customer_ic_dph ?? false)
                    <p>IČ DPH: <span class="text-white font-semibold">{{ $invoice->customer_ic_dph }}</span></p>
                @endif
            </div>
        </div>
    </div>

    <!-- Payment Info -->
    <div class="bg-gradient-to-r from-cyan-900/50 to-purple-900/50 rounded-lg p-6 mb-8 border border-cyan-500/30">
        <h3 class="font-black text-cyan-400 mb-4 uppercase tracking-widest">Platobné údaje</h3>
        <div class="flex gap-6 items-start">
            <div class="flex-1 space-y-4">
                <!-- Dates -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-800 p-4 rounded-lg">
                        <p class="text-xs text-gray-400 mb-1 uppercase tracking-wider">Vystavenie</p>
                        <p class="font-bold text-lg text-white">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d.m.Y') }}</p>
                    </div>
                    <div class="bg-gray-800 p-4 rounded-lg">
                        <p class="text-xs text-gray-400 mb-1 uppercase tracking-wider">Splatnosť</p>
                        <p class="font-bold text-lg text-cyan-400">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d.m.Y') }}</p>
                    </div>
                </div>

                <!-- Bank -->
                <div class="bg-gray-800 p-4 rounded-lg space-y-3">
                    @if($invoice->supplierCompany->iban ?? false)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-400">Číslo účtu</span>
                            <span class="font-mono font-bold text-white">{{ $invoice->supplierCompany->iban }}</span>
                        </div>
                    @endif
                    @if($invoice->variable_symbol ?? false)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-400">Var. symbol</span>
                            <span class="font-mono font-bold text-white">{{ $invoice->variable_symbol }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between pt-2 border-t-2 border-cyan-500">
                        <span class="font-black text-cyan-400">SUMA K ÚHRADE</span>
                        <span class="font-black text-3xl text-cyan-400">{{ number_format($invoice->total_amount, 2, ',', ' ') }} {{ $invoice->currency }}</span>
                    </div>
                </div>
            </div>

            <!-- QR Code -->
            @if($qrCode ?? false)
                <div class="bg-white p-4 rounded-lg">
                    <img src="{{ $qrCode }}" alt="Pay by Square QR Code" class="w-[120px] h-[120px]">
                    <p class="text-center text-xs font-bold text-cyan-600 mt-2">Pay by Square</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-gray-800 rounded-lg overflow-hidden mb-8">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-cyan-600 to-purple-600">
                <tr>
                    <th class="text-left py-4 px-4 font-black uppercase tracking-wider">Popis</th>
                    <th class="text-right py-4 px-4 font-black uppercase tracking-wider w-20">Počet</th>
                    <th class="text-right py-4 px-4 font-black uppercase tracking-wider w-28">Cena/ks</th>
                    <th class="text-right py-4 px-4 font-black uppercase tracking-wider w-28">Celkom</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                    <tr class="border-b border-gray-700 {{ $index % 2 === 0 ? 'bg-gray-800' : 'bg-gray-750' }}">
                        <td class="py-4 px-4 text-white">{{ $item->description }}</td>
                        <td class="text-right py-4 px-4 text-gray-300">{{ number_format($item->quantity, 0, ',', ' ') }}</td>
                        <td class="text-right py-4 px-4 text-gray-300">{{ number_format($item->unit_price, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                        <td class="text-right py-4 px-4 font-bold text-cyan-400">
                            {{ number_format($item->total_price, 2, ',', ' ') }} {{ $invoice->currency }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Total -->
    <div class="flex justify-end mb-8">
        <div class="bg-gradient-to-r from-cyan-600 to-purple-600 rounded-lg p-6 shadow-2xl">
            <div class="flex justify-between items-center gap-12">
                <span class="font-black text-xl uppercase tracking-wider">Celkom k úhrade</span>
                <span class="font-black text-4xl">{{ number_format($invoice->total_amount, 2, ',', ' ') }} {{ $invoice->currency }}</span>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="text-center text-gray-400 border-t border-gray-800 pt-6">
        <p class="text-sm">Faktúru je potrebné uhradiť do dátumu splatnosti.</p>
        @if($invoice->notes ?? false)
            <p class="text-sm mt-2">{{ $invoice->notes }}</p>
        @endif
        <p class="text-xs mt-4">Ďakujeme za vašu dôveru!</p>
    </div>
</div>
