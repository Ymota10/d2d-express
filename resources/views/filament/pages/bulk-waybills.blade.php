<!DOCTYPE html>
<html lang="{{ $language ?? 'ar' }}" dir="{{ ($language ?? 'ar') == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>D2D Express Waybill</title>

    <style>

        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        @font-face {
            font-family: 'Amiri';
            src: url('{{ public_path("fonts/Amiri-Regular.ttf") }}') format('truetype');
        }

        body {
            font-family: 'Amiri', DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background: #fff;
            direction: {{ ($language ?? 'ar') == 'ar' ? 'rtl' : 'ltr' }};
        }

        .waybill {
            width: 100%;
            margin: 0 auto 12px auto;
            border: 2px solid #000;
            border-radius: 6px;
            padding: 8px;
            box-sizing: border-box;

            page-break-after: always;
            page-break-inside: avoid;
        }

        .waybill:last-child {
            page-break-after: auto;
        }

        /* HEADER */
        .top-row {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .barcode-block img {
            width: 110px;
            height: 110px;
        }

        .number {
            font-size: 13px;
            font-weight: bold;
            margin-top: 4px;
        }

        /* MAIN TABLE */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
        }

        .main-table td {
            vertical-align: top;
        }

        /* LEFT COLUMN */
        .left-col {
            width: 78%;
            border-{{ ($language ?? 'ar') == 'ar' ? 'left' : 'right' }}: 2px solid #000;
            padding: 10px 12px;
        }

        /* RIGHT COLUMN */
        .right-col {
            width: 22%;
            padding: 10px;
        }

        /* INFO TABLE */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 5px 0;
            vertical-align: top;
            line-height: 1.5;
        }

        .label {
            width: 180px;
            font-weight: bold;
            white-space: nowrap;
        }

        .value {
            word-break: break-word;
            font-family: 'Amiri', DejaVu Sans, sans-serif;
        }

        /* COD BOX */
        .amount {
            border: 2px solid #000;
            padding: 14px 10px;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin-top: 5px;
            line-height: 1.8;
        }

        /* FOOTER */
        .footer {
            border-top: 2px solid #000;
            margin-top: 6px;
            padding-top: 4px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        .footer-table td {
            width: 33.33%;
            vertical-align: top;
            padding: 0;
        }

        .footer-left {
            text-align: left;
        }

        .footer-center {
            text-align: center;
        }

        .footer-right {
            text-align: right;
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
    $shipperName = $arabic->utf8Glyphs($order->user->name ?? '');
    $receiverName = $arabic->utf8Glyphs($order->receiver_name ?? '');
    $receiverAddress = $arabic->utf8Glyphs($order->receiver_address ?? '');
    $areaName = $arabic->utf8Glyphs($order->area->name ?? '');
    $cityName = $arabic->utf8Glyphs($order->city->name ?? '');
@endphp

<div class="waybill">

    <!-- HEADER -->
    <div class="top-row">

        <div class="barcode-block">

            <img
                src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($order->waybill_number ?? '123456789') }}"
                alt="QR Code"
            >

            <div class="number">
                {{ $order->waybill_number ?? '123456789' }}
            </div>

        </div>

    </div>

    <!-- MAIN -->
    <table class="main-table">

        <tr>

            <!-- LEFT -->
            <td class="left-col">

                <table class="info-table">

                    <tr>
                        <td class="label">{{ __('Shipper Name') }}:</td>
                        <td class="value">{!! $shipperName !!}</td>
                    </tr>

                    <tr>
                        <td class="label">{{ __('Receiver Name') }}:</td>
                        <td class="value">{!! $receiverName !!}</td>
                    </tr>

                    <tr>
                        <td class="label">{{ __('Mobile 1') }}:</td>
                        <td class="value">{{ $order->receiver_mobile_1 }}</td>
                    </tr>

                    <tr>
                        <td class="label">{{ __('Mobile 2') }}:</td>
                        <td class="value">{{ $order->receiver_mobile_2 ?? 'N/A' }}</td>
                    </tr>

                    <tr>
                        <td class="label">{{ __('Address') }}:</td>
                        <td class="value">{!! $receiverAddress !!}</td>
                    </tr>

                    <tr>
                        <td class="label">{{ __('Area') }}:</td>
                        <td class="value">{!! $areaName !!}</td>
                    </tr>

                    <tr>
                        <td class="label">{{ __('City') }}:</td>
                        <td class="value">{!! $cityName !!}</td>
                    </tr>

                    <tr>
                        <td class="label">{{ __('Item Name') }}:</td>
                        <td class="value">{{ $order->item_name }}</td>
                    </tr>

                    <tr>
                        <td class="label">{{ __('Description') }}:</td>
                        <td class="value">{{ $order->description ?? 'N/A' }}</td>
                    </tr>

                    <tr>
                        <td class="label">{{ __('Service Type') }}:</td>
                        <td class="value">
                            {{ strtoupper(str_replace('_', ' ', $order->service_type)) }}
                        </td>
                    </tr>

                    <tr>
                        <td class="label">{{ __('Weight') }}:</td>
                        <td class="value">{{ $order->weight }} kg</td>
                    </tr>

                    <tr>
                        <td class="label">{{ __('Size') }}:</td>
                        <td class="value">{{ $order->size }}</td>
                    </tr>

                    <tr>
                        <td class="label">{{ __('Quantity') }}:</td>
                        <td class="value">{{ $order->quantity }}</td>
                    </tr>

                    <tr>
                        <td class="label">{{ __('Status') }}:</td>
                        <td class="value">
                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                        </td>
                    </tr>

                    <tr>
                        <td class="label">{{ __('Open Package') }}:</td>
                        <td class="value">{{ ucfirst($order->open_package) }}</td>
                    </tr>

                    @if($order->status === 'undelivered')
                    <tr>
                        <td class="label">{{ __('Undelivered Reason') }}:</td>
                        <td class="value">
                            {{ ucfirst(str_replace('_', ' ', $order->undelivered_reason)) }}
                        </td>
                    </tr>
                    @endif

                </table>

            </td>

            <!-- RIGHT -->
            <td class="right-col">

                <div class="amount">

                    {{ strtoupper($order->service_type ?? 'DELIVER') }}

                    <br><br>

                    {{ __('COD') }}:
                    {{ number_format($order->cod_amount, 2) }}
                    {{ __('EGP') }}

                </div>

            </td>

        </tr>

    </table>

    <!-- FOOTER -->
    <div class="footer">

        <table class="footer-table">

            <tr>

                <td class="footer-left">
                    {{ __('Notes') }}: -
                </td>

                <td class="footer-center">
                    {{ __('Order Ref') }}:
                    {{ $order->reference ?? '-' }}
                </td>

                <td class="footer-right">
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