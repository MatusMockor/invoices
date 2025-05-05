<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Faktúra #{{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 1cm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.5;
            background-color: #fff;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 10px;
        }
        .logo {
            text-align: left;
            margin-bottom: 20px;
        }
        .document-title {
            font-size: 22px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
            color: #000;
        }
        .document-subtitle {
            font-size: 11pt;
            text-align: center;
            margin-bottom: 30px;
            color: #555;
        }
        .meta-box {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 3px 5px;
        }
        .meta-table .label {
            font-weight: bold;
            width: 150px;
        }
        .parties-box {
            margin-bottom: 20px;
        }
        .party-table {
            width: 100%;
            border-collapse: collapse;
        }
        .party-table td {
            vertical-align: top;
            padding: 5px;
        }
        .party-heading {
            font-weight: bold;
            margin-bottom: 5px;
            padding-bottom: 3px;
            border-bottom: 1px solid #ddd;
        }
        .party-content {
            padding: 5px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #e6e6e6;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .items-table .number-col {
            text-align: right;
        }
        .items-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .summary-table {
            width: 350px;
            margin-left: auto;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 5px;
        }
        .summary-table .label {
            font-weight: bold;
            text-align: left;
        }
        .summary-table .value {
            text-align: right;
        }
        .summary-table .total-row {
            font-weight: bold;
            font-size: 12pt;
            border-top: 1px solid #ddd;
        }
        .notes {
            margin-top: 30px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .notes-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 9pt;
            color: #666;
            text-align: center;
        }
        .qr-code {
            text-align: right;
            margin-top: 20px;
        }
        .payment-info {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .payment-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .payment-table {
            width: 100%;
        }
        .payment-table td {
            padding: 3px 0;
        }
        .payment-table .label {
            font-weight: bold;
            width: 150px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Logo and Title -->
        <div class="logo">
            <!-- Logo placeholder -->
        </div>
        
        <div class="document-title">FAKTÚRA č. {{ $invoice->invoice_number }}</div>
        <div class="document-subtitle">Daňový doklad v zmysle § 71 ods. 2 zákona č. 222/2004 Z. z.</div>
        
        <!-- Invoice Metadata -->
        <div class="meta-box">
            <table class="meta-table">
                <tr>
                    <td class="label">Dátum vystavenia:</td>
                    <td>{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d.m.Y') }}</td>
                    <td class="label">Variabilný symbol:</td>
                    <td>{{ str_replace(['INV-', '-'], '', $invoice->invoice_number) }}</td>
                </tr>
                <tr>
                    <td class="label">Dátum splatnosti:</td>
                    <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d.m.Y') }}</td>
                    <td class="label">Spôsob úhrady:</td>
                    <td>Bankový prevod</td>
                </tr>
                <tr>
                    <td class="label">Dátum dodania:</td>
                    <td>{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d.m.Y') }}</td>
                    <td class="label">Stav:</td>
                    <td>
                        @php
                            $statusLabels = [
                                'draft' => 'Koncept',
                                'sent' => 'Odoslaná',
                                'paid' => 'Zaplatená',
                                'cancelled' => 'Zrušená',
                            ];
                        @endphp
                        {{ $statusLabels[$invoice->status] }}
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Supplier and Customer -->
        <div class="parties-box">
            <table class="party-table">
                <tr>
                    <td width="48%">
                        <div class="party-heading">Dodávateľ</div>
                        <div class="party-content">
                            <strong>{{ $invoice->supplierCompany->name }}</strong><br>
                            {{ $invoice->supplierCompany->street }}<br>
                            {{ $invoice->supplierCompany->postal_code }} {{ $invoice->supplierCompany->city }}<br>
                            <br>
                            IČO: {{ $invoice->supplierCompany->ico }}<br>
                            DIČ: {{ $invoice->supplierCompany->dic }}<br>
                            @if($invoice->supplierCompany->ic_dph)
                            IČ DPH: {{ $invoice->supplierCompany->ic_dph }}<br>
                            @endif
                        </div>
                    </td>
                    <td width="4%"></td>
                    <td width="48%">
                        <div class="party-heading">Odberateľ</div>
                        <div class="party-content">
                            <strong>{{ $invoice->partner->name }}</strong><br>
                            {{ $invoice->partner->street }}<br>
                            {{ $invoice->partner->postal_code }} {{ $invoice->partner->city }}<br>
                            <br>
                            IČO: {{ $invoice->partner->ico }}<br>
                            DIČ: {{ $invoice->partner->dic }}<br>
                            @if($invoice->partner->ic_dph)
                            IČ DPH: {{ $invoice->partner->ic_dph }}<br>
                            @endif
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Invoice Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%">P.č.</th>
                    <th width="45%">Popis položky</th>
                    <th width="10%" class="number-col">Množstvo</th>
                    <th width="10%" class="number-col">MJ</th>
                    <th width="15%" class="number-col">Cena za jedn.</th>
                    <th width="15%" class="number-col">Celkom</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                <tr>
                    <td class="number-col">{{ $index + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="number-col">{{ $item->quantity }}</td>
                    <td class="number-col">ks</td>
                    <td class="number-col">{{ number_format($item->unit_price, 2, ',', ' ') }}</td>
                    <td class="number-col">{{ number_format($item->total_price, 2, ',', ' ') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Payment Information -->
        <div class="payment-info">
            <div class="payment-title">Platobné údaje</div>
            <table class="payment-table">
                <tr>
                    <td class="label">Banka:</td>
                    <td>{{ $invoice->supplierCompany->bank_name ?? 'Slovenská sporiteľňa, a.s.' }}</td>
                </tr>
                <tr>
                    <td class="label">IBAN:</td>
                    <td>{{ $invoice->supplierCompany->iban ?? 'SK00 0000 0000 0000 0000 0000' }}</td>
                </tr>
                <tr>
                    <td class="label">SWIFT:</td>
                    <td>{{ $invoice->supplierCompany->swift ?? 'GIBASKBX' }}</td>
                </tr>
                <tr>
                    <td class="label">Variabilný symbol:</td>
                    <td>{{ str_replace(['INV-', '-'], '', $invoice->invoice_number) }}</td>
                </tr>
            </table>
        </div>
        
        <!-- Invoice Summary -->
        <table class="summary-table">
            <tr>
                <td class="label">Celkom bez DPH:</td>
                <td class="value">{{ number_format($invoice->total_amount / 1.2, 2, ',', ' ') }} {{ $invoice->currency }}</td>
            </tr>
            <tr>
                <td class="label">DPH 20%:</td>
                <td class="value">{{ number_format($invoice->total_amount - ($invoice->total_amount / 1.2), 2, ',', ' ') }} {{ $invoice->currency }}</td>
            </tr>
            <tr class="total-row">
                <td class="label">Celkom s DPH:</td>
                <td class="value">{{ number_format($invoice->total_amount, 2, ',', ' ') }} {{ $invoice->currency }}</td>
            </tr>
        </table>
        
        <!-- Notes -->
        @if($invoice->note)
        <div class="notes">
            <div class="notes-title">Poznámka:</div>
            <p>{{ $invoice->note }}</p>
        </div>
        @endif
        
        <!-- Footer -->
        <div class="footer">
            <p>Faktúra bola vygenerovaná elektronicky a je platná bez podpisu a pečiatky.</p>
            <p>Ďakujeme za Vašu dôveru a tešíme sa na ďalšiu spoluprácu.</p>
        </div>
    </div>
</body>
</html>
