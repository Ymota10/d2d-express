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

        .info-row {
            margin: 3px 0;
            direction: {{ ($language ?? 'ar') == 'ar' ? 'rtl' : 'ltr' }};
            text-align: {{ ($language ?? 'ar') == 'ar' ? 'right' : 'left' }};
            unicode-bidi: bidi-override;
            word-break: break-word;
        }

        .info-row strong {
            display: inline-block;
            min-width: 140px;
        }

        .amount {
            border: 2px solid #000;
            padding: 5px;
            font-weight: bold;
            font-size: 13px;
            text-align: center;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            border-top: 2px solid #000;
            padding-top: 6px;
            margin-top: 8px;
            font-size: 10px;
            direction: {{ ($language ?? 'ar') == 'ar' ? 'rtl' : 'ltr' }};
            text-align: {{ ($language ?? 'ar') == 'ar' ? 'right' : 'left' }};
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
@foreach ($orders as $order)
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
                <div class="info-row"><strong>{{ __('Shipper Name') }}:</strong> {{ $order->user->name ?? 'N/A' }}</div>
                <div class="info-row"><strong>{{ __('Receiver Name') }}:</strong> {{ $order->receiver_name }}</div>
                <div class="info-row"><strong>{{ __('Mobile 1') }}:</strong> {{ $order->receiver_mobile_1 }}</div>
                <div class="info-row"><strong>{{ __('Mobile 2') }}:</strong> {{ $order->receiver_mobile_2 ?? 'N/A' }}</div>
                <div class="info-row"><strong>{{ __('Address') }}:</strong> <span dir="auto">{{ $order->receiver_address }}</span></div>
                <div class="info-row"><strong>{{ __('Area') }}:</strong> {{ $order->area->name ?? 'N/A' }}</div>
                <div class="info-row"><strong>{{ __('City') }}:</strong> {{ $order->city->name ?? 'N/A' }}</div>
                <div class="info-row"><strong>{{ __('Item Name') }}:</strong> {{ $order->item_name }}</div>
                <div class="info-row"><strong>{{ __('Description') }}:</strong> {{ $order->description ?? 'N/A' }}</div>
                <div class="info-row"><strong>{{ __('Service Type') }}:</strong> {{ ucfirst(str_replace('_', ' ', $order->service_type)) }}</div>
                <div class="info-row"><strong>{{ __('Weight') }}:</strong> {{ $order->weight }} kg</div>
                <div class="info-row"><strong>{{ __('Size') }}:</strong> {{ $order->size }}</div>
                <div class="info-row"><strong>{{ __('Quantity') }}:</strong> {{ $order->quantity }}</div>
                <div class="info-row"><strong>{{ __('Status') }}:</strong> {{ ucfirst(str_replace('_', ' ', $order->status)) }}</div>
                <div class="info-row"><strong>{{ __('Open Package') }}:</strong> {{ ucfirst($order->open_package) }}</div>
                @if($order->status === 'undelivered')
                    <div class="info-row"><strong>{{ __('Undelivered Reason') }}:</strong> {{ ucfirst(str_replace('_', ' ', $order->undelivered_reason)) }}</div>
                @endif
            </div>

            <div class="right-col">
                <div class="amount">{{ __('COD') }}: {{ number_format($order->cod_amount, 2) }} {{ __('EGP') }}</div>
            </div>
        </div>

        <div class="footer">
            <div>{{ __('Notes') }}: -</div>
            <div>{{ __('Order Ref') }}: {{ $order->reference ?? '-' }}</div>
            <div class="info-row"><strong>{{ __('Generated') }}:</strong> {{ now()->format('Y-m-d H:i') }}</div>
        </div>

        <div class="bottom-barcode">
            <div class="barcode">|| || ||| ||| || |</div>
            <div>{{ $order->waybill_number ?? '123456789' }}</div>
        </div>
    </div>
@endforeach
</body>
</html>
