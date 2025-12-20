<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=210mm, height=297mm">
    <title>Faktura</title>

    <!-- Preload and load Figtree font -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=block" rel="stylesheet" />

    <!-- Invoice data must be defined BEFORE React loads -->
    <script>
        window.__INVOICE_DATA__ = {
            invoice: @json($invoiceData),
            template: @json($template)
        };
    </script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 210mm;
            height: 297mm;
            background: white;
        }

        #root {
            width: 210mm;
            min-height: 297mm;
            padding: 10mm;
            background: white;
        }

        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }

            /* Prevent page breaks inside table rows */
            tr {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            /* Keep totals section together */
            .totals-section, [class*="total"] {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            /* Keep header together */
            header, .header {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            /* Tailwind print utilities fallback */
            .print\:break-inside-avoid {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .print\:break-inside-auto {
                page-break-inside: auto !important;
                break-inside: auto !important;
            }

            .print\:break-before-avoid {
                page-break-before: avoid !important;
                break-before: avoid !important;
            }

            .print\:table-header-group {
                display: table-header-group !important;
            }

            .print\:p-0 {
                padding: 0 !important;
            }

            /* Ensure table headers repeat on each page */
            thead {
                display: table-header-group;
            }

            /* Keep reverse charge and tax exemption text together */
            [class*="yellow"], [class*="blue"] {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
    </style>

    <!-- Use built assets directly for Browsershot (no Vite dev server inside container) -->
    @php
        $manifestPath = public_path('build/manifest.json');
        $manifest = [];
        $cssFiles = [];
        $jsFile = null;
        $imports = [];

        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true) ?? [];
            $entry = $manifest['resources/js/pdf-preview.tsx'] ?? [];
            $cssFiles = $entry['css'] ?? [];
            $jsFile = $entry['file'] ?? null;
            $imports = $entry['imports'] ?? [];
        }
    @endphp

    @if(empty($manifest))
        <script>console.error('Build manifest not found. Run: npm run build');</script>
    @endif

    @foreach($cssFiles as $css)
        <link rel="stylesheet" href="/build/{{ $css }}">
    @endforeach
</head>
<body>
    <div id="root"></div>

    @foreach($imports as $import)
        @if(isset($manifest[$import]['file']))
            <script type="module" src="/build/{{ $manifest[$import]['file'] }}"></script>
        @endif
    @endforeach
    @if($jsFile)
        <script type="module" src="/build/{{ $jsFile }}"></script>
    @endif
</body>
</html>
