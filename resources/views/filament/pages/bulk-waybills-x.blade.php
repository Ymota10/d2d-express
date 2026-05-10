<!DOCTYPE html>
<html lang="{{ $language ?? 'ar' }}" dir="{{ ($language ?? 'ar') == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>D2D Express Waybill</title>

    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }

        @font-face {
            font-family: 'Amiri';
            src: url('{{ public_path("fonts/Amiri-Regular.ttf") }}') format('truetype');
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
            width: 380px;
            margin: 5px auto;
            border: 1px solid #000;
            padding: 6px;
            page-break-inside: avoid;
            box-sizing: border-box;
        }

        /* HEADER */
        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
            margin-bottom: 4px;
        }

        .qr-wrapper {
    text-align: center;
}

.waybill-number {
    margin-top: 4px;
    font-size: 11px;
    font-weight: bold;
    letter-spacing: 1px;
}

        /* SUMMARY */
        .summary {
            overflow: hidden;
            font-weight: bold;
            font-size: 11px;
            border: 1px solid #000;
            border-bottom: none;
            padding: 4px 6px;
            background: #f7f7f7;
        }

        .summary-left {
            float: left;
        }

        .summary-right {
            float: right;
        }

        /* SECTION */
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
        }

        .info-table td {
            padding: 2px 5px;
            font-size: 9px;
            vertical-align: top;
            border-bottom: 1px solid #ddd;
            line-height: 1.2;
        }

        .info-table td.label {
            font-weight: bold;
            width: 38%;
        }

        .info-table td.value {
            width: 62%;
            word-break: break-word;
        }

        /* FOOTER */
        .footer {
            border-top: 1px solid #000;
            margin-top: 4px;
            padding-top: 3px;
            font-size: 8px;
        }

        .footer-row {
            overflow: hidden;
        }

        .footer-left {
            float: left;
        }

        .footer-right {
            float: right;
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

      <!-- BARCODE -->
<div class="qr-wrapper">
    <img
        src="https://barcode.tec-it.com/barcode.ashx?data={{ urlencode($order->waybill_number ?? '123456789') }}&code=Code128&dpi=96"
        alt="Barcode"
        style="height:60px; width:220px;"
    >

    <div class="waybill-number">
        {{ $order->waybill_number ?? '123456789' }}
    </div>
</div>

    </div>

   <!-- SUMMARY -->
<table style="
    width:100%;
    border:1px solid #000;
    border-bottom:none;
    border-collapse:collapse;
    font-weight:bold;
    font-size:11px;
    background:#f7f7f7;
">
    <tr>
        <td style="
            padding:5px 6px;
            text-align:left;
            width:50%;
        ">
            {{ strtoupper($order->service_type ?? 'DELIVER') }}
        </td>

        <td style="
            padding:5px 6px;
            text-align:right;
            width:50%;
        ">
            {{ __('COD') }}:
            {{ number_format($order->cod_amount, 2) }}
            {{ __('EGP') }}
        </td>
    </tr>
</table>

    <!-- RECEIVER INFO -->
    <div class="section">

        <div class="section-title">
            {{ __('Receiver Information') }}
        </div>

        <table class="info-table">
            <tr>
                <td class="label">{{ __('Shipper') }}</td>
                <td class="value">{!! $shipperName !!}</td>
            </tr>

            <tr>
                <td class="label">{{ __('Name') }}</td>
                <td class="value">{!! $receiverName !!}</td>
            </tr>

            <tr>
                <td class="label">{{ __('Mobile 1') }}</td>
                <td class="value">{{ $order->receiver_mobile_1 }}</td>
            </tr>

            <tr>
                <td class="label">{{ __('Mobile 2') }}</td>
                <td class="value">{{ $order->receiver_mobile_2 ?? 'N/A' }}</td>
            </tr>

            <tr>
                <td class="label">{{ __('Address') }}</td>
                <td class="value">{!! $receiverAddress !!}</td>
            </tr>

            <tr>
                <td class="label">{{ __('Area') }}</td>
                <td class="value">{!! $areaName !!}</td>
            </tr>

            <tr>
                <td class="label">{{ __('City') }}</td>
                <td class="value">{!! $cityName !!}</td>
            </tr>
        </table>

    </div>

    <!-- SHIPMENT -->
    <div class="section">

        <div class="section-title">
            {{ __('Shipment Details') }}
        </div>

        <table class="info-table">

            <tr>
                <td class="label">{{ __('Item') }}</td>
                <td class="value">{{ $order->item_name }}</td>
            </tr>

            <tr>
                <td class="label">{{ __('Description') }}</td>
                <td class="value">{{ $order->description ?? 'N/A' }}</td>
            </tr>

            <tr>
                <td class="label">{{ __('Service') }}</td>
                <td class="value">{{ ucfirst(str_replace('_', ' ', $order->service_type)) }}</td>
            </tr>

            <tr>
                <td class="label">{{ __('Weight') }}</td>
                <td class="value">{{ $order->weight }} kg</td>
            </tr>

            <tr>
                <td class="label">{{ __('Size') }}</td>
                <td class="value">{{ $order->size }}</td>
            </tr>

            <tr>
                <td class="label">{{ __('Quantity') }}</td>
                <td class="value">{{ $order->quantity }}</td>
            </tr>

            <tr>
                <td class="label">{{ __('Status') }}</td>
                <td class="value">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</td>
            </tr>

            <tr>
                <td class="label">{{ __('Open Package') }}</td>
                <td class="value">{{ ucfirst($order->open_package) }}</td>
            </tr>

            @if($order->status === 'undelivered')
            <tr>
                <td class="label">{{ __('Reason') }}</td>
                <td class="value">
                    {{ ucfirst(str_replace('_', ' ', $order->undelivered_reason)) }}
                </td>
            </tr>
            @endif

        </table>

    </div>

<!-- FOOTER -->
<div class="footer">

    <table style="width:100%; font-size:8px;">
        <tr>
            <td style="text-align:left;">
                {{ __('Order Ref') }}:
                {{ $order->reference ?? '-' }}
            </td>

            <td style="text-align:right;">
                <strong>{{ __('Generated') }}:</strong>
                {{ now()->format('Y-m-d H:i') }}
            </td>
        </tr>
    </table>

</div>

</div>

@endforeach

</body>
</html>