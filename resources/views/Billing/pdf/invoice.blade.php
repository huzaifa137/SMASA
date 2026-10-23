<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        @page { margin: 0; }
        body { margin: 0; padding: 0; font-family: 'DejaVu Sans', sans-serif; }
    </style>
</head>
<body>
    @include('Billing.partials.invoice-document')
</body>
</html>
