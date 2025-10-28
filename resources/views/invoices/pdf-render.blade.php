<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <title>Faktúra {{ $invoice->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>

</head>
<body>
@switch($template ?? 'classic')
    @case('modern')
        @include('invoices.templates.modern')
        @break
    @case('minimal')
        @include('invoices.templates.minimal')
        @break
    @case('bold')
        @include('invoices.templates.bold')
        @break
    @default
        @include('invoices.templates.classic')
@endswitch
</body>
</html>
