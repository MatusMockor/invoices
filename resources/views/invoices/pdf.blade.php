<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktúra {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            margin-bottom: 30px;
        }
        .logo {
            max-width: 200px;
            max-height: 80px;
            margin-bottom: 10px;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .invoice-number {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .row {
            display: flex;
            margin-bottom: 5px;
        }
        .col {
            flex: 1;
        }
        .col-2 {
            flex: 2;
        }
        .col-3 {
            flex: 3;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th {
            background-color: #f5f5f5;
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals {
            width: 300px;
            float: right;
            margin-top: 20px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .total-amount {
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            margin-top: 50px;
            font-size: 10px;
            color: #666;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .qr-code {
            text-align: center;
            margin-top: 20px;
        }
        .qr-code svg {
            max-width: 200px;
            max-height: 200px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($invoice->company->logo)
            <img src="{{ $invoice->company->logo }}" alt="{{ $invoice->company->name }} Logo" class="logo">
        @endif
        <div class="invoice-title">FAKTÚRA</div>
        <div class="invoice-number">Číslo faktúry: {{ $invoice->invoice_number }}</div>
    </div>
    
    <div class="section">
        <div class="row">
            <div class="col">
                <div class="section-title">Dodávateľ</div>
                <div><strong>{{ $invoice->company->name }}</strong></div>
                <div>{{ $invoice->company->address }}</div>
                <div>{{ $invoice->company->zip }} {{ $invoice->company->city }}</div>
                <div>{{ $invoice->company->country }}</div>
                <div>&nbsp;</div>
                <div>IČO: {{ $invoice->company->ico }}</div>
                <div>DIČ: {{ $invoice->company->dic }}</div>
                @if($invoice->company->ic_dph)
                <div>IČ DPH: {{ $invoice->company->ic_dph }}</div>
                @endif
            </div>
            <div class="col">
                <div class="section-title">Odberateľ</div>
                <div><strong>{{ $invoice->customer_name }}</strong></div>
                <div>{{ $invoice->customer_address }}</div>
                <div>{{ $invoice->customer_zip }} {{ $invoice->customer_city }}</div>
                <div>{{ $invoice->customer_country }}</div>
                <div>&nbsp;</div>
                @if($invoice->customer_ico)
                <div>IČO: {{ $invoice->customer_ico }}</div>
                @endif
                @if($invoice->customer_dic)
                <div>DIČ: {{ $invoice->customer_dic }}</div>
                @endif
                @if($invoice->customer_ic_dph)
                <div>IČ DPH: {{ $invoice->customer_ic_dph }}</div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="section">
        <div class="row">
            <div class="col">
                <div><strong>Dátum vystavenia:</strong> {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d.m.Y') }}</div>
                <div><strong>Dátum dodania:</strong> {{ \Carbon\Carbon::parse($invoice->delivery_date)->format('d.m.Y') }}</div>
                <div><strong>Dátum splatnosti:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d.m.Y') }}</div>
            </div>
            <div class="col">
                <div><strong>Forma úhrady:</strong> {{ $invoice->payment_method }}</div>
                @if($invoice->variable_symbol)
                <div><strong>Variabilný symbol:</strong> {{ $invoice->variable_symbol }}</div>
                @endif
                @if($invoice->constant_symbol)
                <div><strong>Konštantný symbol:</strong> {{ $invoice->constant_symbol }}</div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">Položky faktúry</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Názov položky</th>
                    <th class="text-right">Množstvo</th>
                    <th class="text-right">Cena za jednotku</th>
                    <th class="text-right">Celkom bez DPH</th>
                    <th class="text-right">DPH %</th>
                    <th class="text-right">Celkom s DPH</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ $item->quantity }} {{ $item->unit }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                    <td class="text-right">{{ number_format($item->total_price_without_vat, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                    <td class="text-right">{{ $item->vat_rate }}%</td>
                    <td class="text-right">{{ number_format($item->total_price, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="totals">
        <div class="totals-row">
            <div>Celkom bez DPH:</div>
            <div>{{ number_format($invoice->total_amount_without_vat, 2, ',', ' ') }} {{ $invoice->currency }}</div>
        </div>
        <div class="totals-row">
            <div>DPH:</div>
            <div>{{ number_format($invoice->total_vat, 2, ',', ' ') }} {{ $invoice->currency }}</div>
        </div>
        <div class="totals-row total-amount">
            <div>Celkom s DPH:</div>
            <div>{{ number_format($invoice->total_amount, 2, ',', ' ') }} {{ $invoice->currency }}</div>
        </div>
    </div>
    
    <div style="clear: both;"></div>
    
    <div class="section">
        @if($invoice->note)
        <div class="section-title">Poznámka</div>
        <div>{{ $invoice->note }}</div>
        @endif
        
        <div class="section">
            <div class="row">
                <div class="col">
                @if($iban)
                <div class="section-title" style="margin-top: 20px;">Platobné údaje</div>
                <div><strong>IBAN:</strong> {{ $iban }}</div>
                <div><strong>Variabilný symbol:</strong> {{ $invoice->invoice_number }}</div>
                <div><strong>Suma na úhradu:</strong> {{ number_format($invoice->total_amount, 2, ',', ' ') }} {{ $invoice->currency }}</div>
                
                @if($qrCode)
                <div class="qr-code" style="margin-top: 15px; margin-bottom: 15px;">
                    <div style="margin-bottom: 10px; font-weight: bold;">Naskenujte pre platbu cez PAY by square</div>
                    <div style="width: 200px; height: 200px;">
                        {!! $qrCode !!}
                    </div>
                </div>
                @endif
                @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>Faktúra bola vygenerovaná elektronicky a je platná bez podpisu a pečiatky.</p>
        @if($invoice->company->registration_info)
        <p>{{ $invoice->company->registration_info }}</p>
        @endif
    </div>
</body>
</html>
