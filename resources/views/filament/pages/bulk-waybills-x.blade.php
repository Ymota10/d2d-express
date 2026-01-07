<!DOCTYPE html>
<html lang="{{ $language ?? 'ar' }}" dir="{{ ($language ?? 'ar') == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>D2D Express Waybill</title>
    <style>

@page {
    size: 80mm auto; /* X Printer (80mm) */
    margin: 0;
}

        @font-face {
            font-family: 'Amiri';
            src: url('{{ public_path("fonts/Amiri-Regular.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: 'Amiri', DejaVu Sans, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
            background: #fff;
            direction: {{ ($language ?? 'ar') == 'ar' ? 'rtl' : 'ltr' }};
            text-align: {{ ($language ?? 'ar') == 'ar' ? 'right' : 'left' }};
        }

        .waybill {
            width: 380px; /* Thermal printer width */
            padding: 8px;
            margin: 8px auto;
            border: 1px solid #000;
            page-break-inside: avoid;
        }

        /* Header (Logo + Barcode) */
        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }
        .logo img {
            height: 60px;
        }
        .barcode {
            font-size: 18px;
            letter-spacing: 3px;
            margin-top: 5px;
            font-weight: bold;
        }
        .waybill-number {
            font-size: 12px;
            font-weight: bold;
            margin-top: 2px;
        }

        /* COD + Delivery Type */
        .summary {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 11px;
            border: 1px solid #000;
            border-bottom: none;
            padding: 5px 6px;
            background: #f9f9f9;
        }

        /* Info sections */
        .section {
            border: 1px solid #000;
            border-top: none;
        }

        .section-title {
            font-weight: bold;
            font-size: 11px;
            background: #eaeaea;
            border-bottom: 1px solid #000;
            padding: 3px 5px;
            text-align: center;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            direction: {{ ($language ?? 'ar') == 'ar' ? 'rtl' : 'ltr' }};
        }

        .info-table td {
            padding: 3px 5px;
            font-size: 10px;
            vertical-align: top;
            border-bottom: 1px solid #ddd;
            word-break: break-word;
        }

        .info-table td.label {
            font-weight: bold;
            width: 40%;
            text-align: {{ ($language ?? 'ar') == 'ar' ? 'right' : 'left' }};
        }

        .info-table td.value {
            text-align: {{ ($language ?? 'ar') == 'ar' ? 'right' : 'left' }};
            font-family: 'Amiri', DejaVu Sans, sans-serif;
        }

        /* Footer */
        .footer {
            border-top: 1px solid #000;
            margin-top: 6px;
            padding-top: 3px;
            font-size: 9px;
        }

        .footer-row {
            display: flex;
            justify-content: space-between;
            direction: {{ ($language ?? 'ar') == 'ar' ? 'rtl' : 'ltr' }};
        }
    </style>
</head>
<body>
@php
    use ArPHP\I18N\Arabic;
    $arabic = new Arabic();
@endphp

@foreach ($orders as $order)
    @php
        $receiverName = $arabic->utf8Glyphs($order->receiver_name ?? '');
        $receiverAddress = $arabic->utf8Glyphs($order->receiver_address ?? '');
        $areaName = $arabic->utf8Glyphs($order->area->name ?? '');
        $cityName = $arabic->utf8Glyphs($order->city->name ?? '');
        $shipperName = $arabic->utf8Glyphs($order->user->name ?? '');
    @endphp

    <div class="waybill">
        <!-- HEADER -->
        <div class="header">
            <div class="logo">
                <img src="{{ public_path('images/d2d_MAIN_LOGO-removebg-preview.png') }}" alt="D2D Express">
            </div>
            <div class="barcode">||| || ||| ||| || |||</div>
            <div class="waybill-number">{{ $order->waybill_number }}</div>
        </div>

        <!-- COD + DELIVERY -->
        <div class="summary">
            <div>{{ __('DELIVER') }}</div>
            <div>{{ __('COD') }}: {{ number_format($order->cod_amount, 2) }} {{ __('EGP') }}</div>
        </div>

        <!-- RECEIVER INFO -->
        <div class="section">
            <div class="section-title">{{ __('Receiver Information') }}</div>
            <table class="info-table">
                <tr><td class="label">{{ __('Shipper') }}</td><td class="value">{!! $shipperName !!}</td></tr>
                <tr><td class="label">{{ __('Name') }}</td><td class="value">{!! $receiverName !!}</td></tr>
                <tr><td class="label">{{ __('Mobile 1') }}</td><td class="value">{{ $order->receiver_mobile_1 }}</td></tr>
                <tr><td class="label">{{ __('Mobile 2') }}</td><td class="value">{{ $order->receiver_mobile_2 ?? 'N/A' }}</td></tr>
                <tr><td class="label">{{ __('Address') }}</td><td class="value">{!! $receiverAddress !!}</td></tr>
                <tr><td class="label">{{ __('Area') }}</td><td class="value">{!! $areaName !!}</td></tr>
                <tr><td class="label">{{ __('City') }}</td><td class="value">{!! $cityName !!}</td></tr>
            </table>
        </div>

        <!-- SHIPMENT DETAILS -->
        <div class="section">
            <div class="section-title">{{ __('Shipment Details') }}</div>
            <table class="info-table">
                <tr><td class="label">{{ __('Item') }}</td><td class="value">{{ $order->item_name }}</td></tr>
                <tr><td class="label">{{ __('Description') }}</td><td class="value">{{ $order->description ?? 'N/A' }}</td></tr>
                <tr><td class="label">{{ __('Service') }}</td><td class="value">{{ ucfirst(str_replace('_', ' ', $order->service_type)) }}</td></tr>
                <tr><td class="label">{{ __('Weight') }}</td><td class="value">{{ $order->weight }} kg</td></tr>
                <tr><td class="label">{{ __('Size') }}</td><td class="value">{{ $order->size }}</td></tr>
                <tr><td class="label">{{ __('Quantity') }}</td><td class="value">{{ $order->quantity }}</td></tr>
                <tr><td class="label">{{ __('Status') }}</td><td class="value">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</td></tr>
                <tr><td class="label">{{ __('Open Package') }}</td><td class="value">{{ ucfirst($order->open_package) }}</td></tr>
                @if($order->status === 'undelivered')
                    <tr><td class="label">{{ __('Reason') }}</td><td class="value">{{ ucfirst(str_replace('_', ' ', $order->undelivered_reason)) }}</td></tr>
                @endif
            </table>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            <div class="footer-row">
                <div>{{ __('Order Ref') }}: {{ $order->reference ?? '-' }}</div>
                <div><strong>{{ __('Generated') }}:</strong> {{ now()->format('Y-m-d H:i') }}</div>
            </div>
        </div>
    </div>
@endforeach
</body>
</html>
