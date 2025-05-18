<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Faktúra {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.2;
            color: #333;
        }
        .header-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .invoice-title {
            text-align: right;
            font-size: 12pt;
            font-weight: bold;
        }
        .party-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .party-title {
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-size: 9pt;
        }
        .payment-box {
            border: 1px solid #ddd;
            padding: 8px;
            margin-top: 10px;
        }
        .payment-row {
            margin-bottom: 3px;
            font-size: 8pt;
        }
        .payment-label {
            display: inline-block;
            width: 100px;
        }
        .payment-value {
            font-weight: bold;
            display: inline-block;
            width: 150px;
            word-break: keep-all;
            white-space: nowrap;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 8pt;
            table-layout: fixed;
        }
        .items-table th {
            border-bottom: 1px solid #ddd;
            padding: 4px;
            text-align: left;
            font-weight: bold;
            font-size: 7pt;
        }
        .items-table td {
            padding: 4px;
            border-bottom: 1px solid #ddd;
        }
        .number-col {
            text-align: right;
        }
        .quantity-col {
            text-align: center;
            padding-right: 5px;
        }
        .price-col {
            text-align: center;
            padding-right: 5px;
        }
        .total-col {
            text-align: right;
        }
        .summary-row {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            font-size: 7pt;
            color: #666;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-number {
            text-align: right;
            font-size: 7pt;
            color: #666;
            margin-top: 10px;
        }
        .qr-code {
            width: 80px;
            height: 80px;
            margin-left: auto;
        }
    </style>
</head>
<body>
    <!-- Header with Invoice Number -->
    <table class="header-table">
        <tr>
            <td width="50%"></td>
            <td width="50%">
                <div class="invoice-title">
                    FAKTÚRA {{ str_replace(['INV-', '-'], '', $invoice->invoice_number) }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Supplier and Customer -->
    <table class="party-table">
        <tr>
            <td width="48%" valign="top">
                <div class="party-title">Dodávateľ</div>
                <div style="margin-bottom: 10px;">
                    <strong>{{ $invoice->supplierCompany->name }}</strong><br>
                    {{ $invoice->supplierCompany->street }}<br>
                    {{ $invoice->supplierCompany->postal_code }} {{ $invoice->supplierCompany->city }}, Slovenská republika<br>
                </div>

                <div style="margin-bottom: 10px; font-size: 8pt;">
                    <div style="white-space: nowrap;">
                        <span style="display: inline-block;">IČO: {{ $invoice->supplierCompany->ico }}</span>
                    </div>
                    <div style="white-space: nowrap;">
                        <span style="display: inline-block;">DIČ: {{ $invoice->supplierCompany->dic }}</span>
                    </div>
                    @if($invoice->supplierCompany->ic_dph)
                    <div style="white-space: nowrap;">
                        <span style="display: inline-block;">IČ DPH: {{ $invoice->supplierCompany->ic_dph }}</span>
                    </div>
                    @else
                    <div style="white-space: nowrap;">
                        <span style="display: inline-block;">Nie je platiteľ DPH</span>
                    </div>
                    @endif
                    @if($invoice->supplierCompany->company_type)
                    <div style="white-space: nowrap;">
                        <span style="display: inline-block;">Právna forma: {{ $invoice->supplierCompany->company_type }}</span>
                    </div>
                    @endif
                    @if($invoice->supplierCompany->registration_number)
                    <div style="white-space: nowrap;">
                        <span style="display: inline-block;">
                            @if($invoice->supplierCompany->company_type == 's.r.o.')
                                Zápis v OR: {{ $invoice->supplierCompany->registration_number }}
                            @else
                                Zápis v ŽR: {{ $invoice->supplierCompany->registration_number }}
                            @endif
                        </span>
                    </div>
                    @endif
                </div>

                <div style="margin-top: 15px;">
                    <table cellpadding="0" cellspacing="0" style="font-size: 8pt; width: 100%;">
                        <tr>
                            <td width="100" style="padding-bottom: 3px;">Dátum vystavenia:</td>
                            <td style="padding-bottom: 3px;">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d.m.Y') }}</td>
                        </tr>
                        <tr>
                            <td width="100" style="padding-bottom: 3px;">Dátum dodania:</td>
                            <td style="padding-bottom: 3px;">{{ \Carbon\Carbon::parse($invoice->delivery_date)->format('d.m.Y') }}</td>
                        </tr>
                        <tr>
                            <td width="100">Splatnosť:</td>
                            <td style="font-weight: bold;">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d.m.Y') }}</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td width="4%"></td>
            <td width="48%" valign="top">
                <div class="party-title">Odberateľ</div>
                <div>
                    <strong>{{ $invoice->businessEntity->name }}</strong><br>
                    {{ $invoice->businessEntity->street }}<br>
                    {{ $invoice->businessEntity->postal_code }} {{ $invoice->businessEntity->city }}<br>
                    Slovenská republika<br>
                    <br>
                    <div style="margin-top: 5px; font-size: 8pt; white-space: nowrap;">
                        <span style="display: inline-block;">IČO: {{ $invoice->businessEntity->ico }}</span>
                        <span style="margin-left: 10px; display: inline-block;">DIČ: {{ $invoice->businessEntity->dic }}</span>
                        @if($invoice->businessEntity->ic_dph)
                            <span style="margin-left: 10px; display: inline-block;">IČ DPH: {{ $invoice->businessEntity->ic_dph }}</span>
                        @endif
                    </div>
                    @if($invoice->businessEntity->company_type || $invoice->businessEntity->registration_number)
                    <div style="margin-top: 3px; font-size: 8pt; white-space: nowrap;">
                        @if($invoice->businessEntity->company_type)
                            <span style="display: inline-block;">Právna forma: {{ $invoice->businessEntity->company_type }}</span>
                        @endif
                        @if($invoice->businessEntity->registration_number)
                            <span style="margin-left: 10px; display: inline-block;">
                                @if($invoice->businessEntity->company_type == 's.r.o.')
                                    Zápis v OR: {{ $invoice->businessEntity->registration_number }}
                                @else
                                    Zápis v ŽR: {{ $invoice->businessEntity->registration_number }}
                                @endif
                            </span>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="payment-box">
                    <table width="100%">
                        <tr>
                            <td width="65%" valign="top">
                                <div class="payment-row">
                                    <span class="payment-label">Spôsob úhrady:</span>
                                    <span class="payment-value">Bankový prevod</span>
                                </div>
                                <div class="payment-row">
                                    <span class="payment-label">Suma:</span>
                                    <span class="payment-value">{{ number_format($invoice->total_amount, 2, ',', ' ') }} {{ $invoice->currency }}</span>
                                </div>
                                <div class="payment-row">
                                    <span class="payment-label">Variabilný symbol:</span>
                                    <span class="payment-value">{{ substr(str_replace(['INV-', '-'], '', $invoice->invoice_number), 0, 10) }}</span>
                                </div>
                                @if($invoice->constant_symbol)
                                <div class="payment-row">
                                    <span class="payment-label">Konštantný symbol:</span>
                                    <span class="payment-value">{{ $invoice->constant_symbol }}</span>
                                </div>
                                @endif
                                <div class="payment-row">
                                    <span class="payment-label">IBAN:</span>
                                    <span class="payment-value" style="font-size: 7pt;">{{ $invoice->supplierCompany->iban ?? 'SK14 0900 0000 0052 7700 4607' }}</span>
                                </div>
                                <div class="payment-row">
                                    <span class="payment-label">SWIFT:</span>
                                    <span class="payment-value">{{ $invoice->supplierCompany->swift ?? 'GIBASKBX' }}</span>
                                </div>
                            </td>
                            <td width="35%" valign="top" align="right">
                                @if($qrCode)
                                <div>
                                    <img src="{{ $qrCode }}" class="qr-code" alt="Pay by Square QR kód">
                                </div>
                                <div style="text-align: center; font-size: 6pt; color: #666; margin-top: 3px;">
                                    Pay by Square
                                </div>
                                @else
                                <div style="width: 70px; height: 70px; border: 1px solid #ddd; text-align: center; margin-left: auto;">
                                    <!-- QR kód placeholder -->
                                </div>
                                <div style="text-align: center; font-size: 6pt; color: #666; margin-top: 3px;">
                                    Pay by invoice
                                </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- Invoice Items -->
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">Č.</th>
                <th width="50%">NÁZOV</th>
                <th width="15%" style="text-align: center;">MNOŽSTVO</th>
                <th width="15%" style="text-align: center;">JEDN. CENA</th>
                <th width="15%" style="text-align: right;">SPOLU</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}.</td>
                <td>{{ $item->description }}</td>
                <td style="text-align: center;">{{ number_format($item->quantity, 2, ',', ' ') }}</td>
                <td style="text-align: center;">{{ number_format($item->unit_price, 2, ',', ' ') }}</td>
                <td style="text-align: right;">{{ number_format($item->total_price, 2, ',', ' ') }}</td>
            </tr>
            @endforeach
            <tr class="summary-row">
                <td colspan="4" style="text-align: right;">Spolu</td>
                <td style="text-align: right;">{{ number_format($invoice->total_amount, 2, ',', ' ') }} {{ $invoice->currency }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>Spoločnosť je zapísaná v Živnostenskom registri Okresného úradu Nové Zámky, registrácia č. 440-46274</p>
        <p>Doklad obsahuje {{ count($invoice->items) }} položky, dátové číslo a importačné ID pridelené do systému.</p>
        <p>www.kros.sk</p>
    </div>

    <div class="page-number">
        Strana 1/1
    </div>
</body>
</html>