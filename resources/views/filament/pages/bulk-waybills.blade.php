<!DOCTYPE html>
<html lang="{{ $language ?? 'ar' }}" dir="{{ ($language ?? 'ar') == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>D2D Express Waybill</title>
    <style>
        @font-face {
            font-family: 'Amiri';
            src: url('{{ public_path("fonts/Amiri-Regular.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: 'Amiri', DejaVu Sans, sans-serif;
            font-size: 12px;
            direction: {{ ($language ?? 'ar') == 'ar' ? 'rtl' : 'ltr' }};
            text-align: {{ ($language ?? 'ar') == 'ar' ? 'right' : 'left' }};
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        .waybill {
            width: 700px;
            margin: 15px auto;
            border: 2px solid #000;
            border-radius: 6px;
            padding: 10px 12px;
            page-break-inside: avoid;
        }

        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
        }

        .logo img {
            height: 100px;
            width: auto;
            object-fit: contain;
        }

        .barcode-block {
            text-align: center;
        }

        .barcode-block .barcode {
            font-weight: bold;
            font-size: 14px;
            letter-spacing: 4px;
            margin-bottom: 2px;
        }

        .barcode-block .number {
            font-size: 11px;
            font-weight: bold;
        }

        .main-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            margin-top: 6px;
            padding: 6px 0;
        }

        .left-col {
            border-{{ ($language ?? 'ar') == 'ar' ? 'left' : 'right' }}: 2px solid #000;
            padding: 0 10px;
        }

        /* ✅ Fixed alignment for info rows */
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin: 4px 0;
            gap: 10px;
            direction: {{ ($language ?? 'ar') == 'ar' ? 'rtl' : 'ltr' }};
            unicode-bidi: bidi-override;
            word-break: break-word;
        }

        .info-label {
            font-weight: bold;
            min-width: 160px;
            text-align: {{ ($language ?? 'ar') == 'ar' ? 'right' : 'left' }};
        }

        .info-value {
            flex: 1;
            text-align: {{ ($language ?? 'ar') == 'ar' ? 'right' : 'left' }};
            font-family: 'Amiri', DejaVu Sans, sans-serif;
            line-height: 1.4;
        }

        .amount {
            border: 2px solid #000;
            padding: 8px;
            font-weight: bold;
            font-size: 13px;
            text-align: center;
            font-family: 'Amiri', DejaVu Sans, sans-serif;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            border-top: 2px solid #000;
            padding-top: 6px;
            margin-top: 8px;
            font-size: 10px;
            font-family: 'Amiri', DejaVu Sans, sans-serif;
        }

        .bottom-barcode {
            text-align: center;
            margin-top: 8px;
        }

        .bottom-barcode .barcode {
            font-weight: bold;
            letter-spacing: 3px;
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
        <div class="top-row">
            <div class="logo">
                <img src="{{ public_path('images/d2d_MAIN_LOGO-removebg-preview.png') }}" alt="D2D Express">
            </div>
            <div class="barcode-block">
                <div class="barcode">|| || ||| ||| || |</div>
                <div class="number">{{ $order->waybill_number ?? '123456789' }}</div>
            </div>
        </div>

        <div class="main-grid">
            <div class="left-col">
                <div class="info-row">
                    <div class="info-label">{{ __('Shipper Name') }}:</div>
                    <div class="info-value">{!! $shipperName !!}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">{{ __('Receiver Name') }}:</div>
                    <div class="info-value">{!! $receiverName !!}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">{{ __('Mobile 1') }}:</div>
                    <div class="info-value">{{ $order->receiver_mobile_1 }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">{{ __('Mobile 2') }}:</div>
                    <div class="info-value">{{ $order->receiver_mobile_2 ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">{{ __('Address') }}:</div>
                    <div class="info-value">{!! $receiverAddress !!}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">{{ __('Area') }}:</div>
                    <div class="info-value">{!! $areaName !!}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">{{ __('City') }}:</div>
                    <div class="info-value">{!! $cityName !!}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">{{ __('Item Name') }}:</div>
                    <div class="info-value">{{ $order->item_name }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">{{ __('Description') }}:</div>
                    <div class="info-value">{{ $order->description ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">{{ __('Service Type') }}:</div>
                    <div class="info-value">{{ ucfirst(str_replace('_', ' ', $order->service_type)) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">{{ __('Weight') }}:</div>
                    <div class="info-value">{{ $order->weight }} kg</div>
                </div>
                <div class="info-row">
                    <div class="info-label">{{ __('Size') }}:</div>
                    <div class="info-value">{{ $order->size }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">{{ __('Quantity') }}:</div>
                    <div class="info-value">{{ $order->quantity }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">{{ __('Status') }}:</div>
                    <div class="info-value">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">{{ __('Open Package') }}:</div>
                    <div class="info-value">{{ ucfirst($order->open_package) }}</div>
                </div>

                @if($order->status === 'undelivered')
                    <div class="info-row">
                        <div class="info-label">{{ __('Undelivered Reason') }}:</div>
                        <div class="info-value">{{ ucfirst(str_replace('_', ' ', $order->undelivered_reason)) }}</div>
                    </div>
                @endif
            </div>

            <div class="right-col">
                <div class="amount">{{ __('COD') }}: {{ number_format($order->cod_amount, 2) }} {{ __('EGP') }}</div>
            </div>
        </div>

        <div class="footer">
            <div>{{ __('Notes') }}: -</div>
            <div>{{ __('Order Ref') }}: {{ $order->reference ?? '-' }}</div>
            <div><strong>{{ __('Generated') }}:</strong> {{ now()->format('Y-m-d H:i') }}</div>
        </div>

        <div class="bottom-barcode">
            <div class="barcode">|| || ||| ||| || |</div>
            <div>{{ $order->waybill_number ?? '123456789' }}</div>
        </div>
    </div>
@endforeach
</body>
</html>
