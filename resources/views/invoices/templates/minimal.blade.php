<div class="bg-white text-black p-12 min-h-screen font-sans">
    <!-- Header -->
    <div class="mb-12 pb-8 border-b border-gray-300">
        <div class="flex justify-between items-baseline">
            <h1 class="text-4xl font-light text-gray-900">FAKTÚRA</h1>
            <span class="text-2xl font-light text-gray-600">{{ $invoice->invoice_number }}</span>
        </div>
    </div>

    <!-- Company Info -->
    <div class="grid grid-cols-2 gap-16 mb-12">
        <!-- Supplier -->
        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-3">Dodávateľ</p>
            <p class="font-semibold text-lg mb-1">{{ $invoice->supplierCompany->name ?? 'N/A' }}</p>
            <p class="text-sm text-gray-600">{{ $invoice->supplierCompany->street ?? '' }}</p>
            <p class="text-sm text-gray-600">{{ $invoice->supplierCompany->postal_code ?? '' }} {{ $invoice->supplierCompany->city ?? '' }}</p>
            <div class="mt-3 text-sm text-gray-600 space-y-0.5">
                <p>IČO: {{ $invoice->supplierCompany->ico ?? 'N/A' }}</p>
                <p>DIČ: {{ $invoice->supplierCompany->dic ?? 'N/A' }}</p>
                @if($invoice->supplierCompany->ic_dph ?? false)
                    <p>IČ DPH: {{ $invoice->supplierCompany->ic_dph }}</p>
                @endif
                @if($invoice->supplier_registry_office || $invoice->supplier_registry_number)
                    <p class="mt-2">
                        {{ $invoice->supplier_registry_office }}{{ $invoice->supplier_registry_office && $invoice->supplier_registry_number ? ', ' : '' }}{{ $invoice->supplier_registry_number ? 'registrácia č. ' . $invoice->supplier_registry_number : '' }}
                    </p>
                @endif
            </div>
        </div>

        <!-- Client -->
        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-3">Odberateľ</p>
            <p class="font-semibold text-lg mb-1">{{ $invoice->company_name ?? 'N/A' }}</p>
            <p class="text-sm text-gray-600">{{ $invoice->company_address ?? '' }}</p>
            <p class="text-sm text-gray-600">{{ $invoice->company_zip ?? '' }} {{ $invoice->company_city ?? '' }}</p>
            <div class="mt-3 text-sm text-gray-600 space-y-0.5">
                <p>IČO: {{ $invoice->company_ico ?? 'N/A' }}</p>
                <p>DIČ: {{ $invoice->company_dic ?? 'N/A' }}</p>
                @if($invoice->company_ic_dph ?? false)
                    <p>IČ DPH: {{ $invoice->company_ic_dph }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Dates and Payment -->
    <div class="grid grid-cols-3 gap-8 mb-12 pb-8 border-b border-gray-300">
        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Dátum vystavenia</p>
            <p class="font-semibold">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d.m.Y') }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Dátum splatnosti</p>
            <p class="font-semibold">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d.m.Y') }}</p>
        </div>
        @if($invoice->variable_symbol ?? false)
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Variabilný symbol</p>
                <p class="font-mono font-semibold">{{ $invoice->variable_symbol }}</p>
            </div>
        @endif
    </div>

    <!-- Items Table -->
    <table class="w-full mb-12">
        <thead>
            <tr class="border-b-2 border-gray-900">
                <th class="text-left py-3 font-semibold text-sm uppercase tracking-wide">Popis</th>
                <th class="text-right py-3 font-semibold text-sm uppercase tracking-wide w-20">Počet</th>
                <th class="text-right py-3 font-semibold text-sm uppercase tracking-wide w-32">Cena/ks</th>
                <th class="text-right py-3 font-semibold text-sm uppercase tracking-wide w-32">Celkom</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr class="border-b border-gray-200">
                    <td class="py-4">{{ $item->description }}</td>
                    <td class="text-right py-4">{{ number_format($item->quantity, 0, ',', ' ') }}</td>
                    <td class="text-right py-4">{{ number_format($item->unit_price, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                    <td class="text-right py-4 font-semibold">
                        {{ number_format($item->total_price, 2, ',', ' ') }} {{ $invoice->currency }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Total -->
    <div class="flex justify-end mb-12">
        <div class="w-80">
            <div class="flex justify-between items-baseline py-4 border-t-2 border-gray-900">
                <span class="text-xl font-semibold">Celkom k úhrade</span>
                <span class="text-3xl font-bold">{{ number_format($invoice->total_amount, 2, ',', ' ') }} {{ $invoice->currency }}</span>
            </div>
        </div>
    </div>

    <!-- Payment Details -->
    @if($invoice->supplierCompany->iban ?? false)
        <div class="mb-12 pb-8 border-t border-gray-300 pt-8">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-4">Platobné údaje</p>
            <div class="flex gap-16">
                <div>
                    <p class="text-sm text-gray-600">Číslo účtu</p>
                    <p class="font-mono font-semibold">{{ $invoice->supplierCompany->iban }}</p>
                </div>
                @if($qrCode ?? false)
                    <div class="flex items-center gap-4">
                        <img src="{{ $qrCode }}" alt="Pay by Square QR Code" class="w-20 h-20">
                        <span class="text-xs text-gray-500">Pay by Square</span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Footer -->
    <div class="text-center text-sm text-gray-500">
        <p>Faktúru je potrebné uhradiť do dátumu splatnosti.</p>
        @if($invoice->notes ?? false)
            <p class="mt-2">{{ $invoice->notes }}</p>
        @endif
    </div>
</div>
