<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>D2D Express Waybill</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
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

        /* Header layout */
        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
        }

        .logo img {
    height: 100px; /* Increased from 40px */
    width: auto;   /* Keeps proportions */
    object-fit: contain; /* Prevents stretching */
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

        .hub-info {
            text-align: right;
            font-weight: bold;
            font-size: 12px;
        }

        /* Content grid (main area) */
        .main-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            border-bottom: 2px solid #000;
            border-top: 2px solid #000;
            margin-top: 6px;
            padding: 6px 0;
        }

        .left-col {
            border-right: 2px solid #000;
            padding-right: 10px;
        }

        .right-col {
            padding-left: 10px;
        }

        .info-row {
            margin: 3px 0;
        }

        .info-row strong {
            display: inline-block;
            width: 130px;
        }

        .amount {
            border: 2px solid #000;
            padding: 5px;
            font-weight: bold;
            font-size: 13px;
            text-align: right;
        }

        /* Footer section */
        .footer {
            display: flex;
            justify-content: space-between;
            border-top: 2px solid #000;
            padding-top: 6px;
            margin-top: 8px;
            font-size: 10px;
        }

        .bottom-barcode {
            text-align: right;
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
        <!-- Header -->
        <div class="top-row">
            <div class="logo">
                <img src="{{ public_path('images/d2d_MAIN_LOGO-removebg-preview.png') }}" alt="D2D Express">
            </div>
            <div class="barcode-block">
                <div class="barcode">|| || ||| ||| || |</div>
                <div class="number">{{ $order->waybill_number ?? '123456789' }}</div>
            </div>
        </div>

        <!-- Main Information -->
        <div class="main-grid">
            <div class="left-col">
                <div class="info-row"><strong>Shipper Name:</strong> {{ $order->user->name ?? 'N/A' }}</div>
                <div class="info-row"><strong>Receiver Name:</strong> {{ $order->receiver_name }}</div>
                <div class="info-row"><strong>Mobile 1:</strong> {{ $order->receiver_mobile_1 }}</div>
                <div class="info-row"><strong>Mobile 2:</strong> {{ $order->receiver_mobile_2 ?? 'N/A' }}</div>
                <div class="info-row"><strong>Address:</strong> {{ $order->receiver_address }}</div>
                <div class="info-row"><strong>Area:</strong> {{ $order->area->name ?? 'N/A' }}</div>
                <div class="info-row"><strong>City:</strong> {{ $order->city->name ?? 'N/A' }}</div>
                <div class="info-row"><strong>Item Name:</strong> {{ $order->item_name }}</div>
                <div class="info-row"><strong>Description:</strong> {{ $order->description ?? 'N/A' }}</div>
                <div class="info-row"><strong>Service Type:</strong> {{ ucfirst(str_replace('_', ' ', $order->service_type)) }}</div>
                <div class="info-row"><strong>Weight:</strong> {{ $order->weight }} kg</div>
                <div class="info-row"><strong>Size:</strong> {{ $order->size }}</div>
                <div class="info-row"><strong>Quantity:</strong> {{ $order->quantity }}</div>
                <div class="info-row"><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $order->status)) }}</div>
                <div class="info-row"><strong>Open Package:</strong> {{ ucfirst($order->open_package) }}</div>
                @if($order->status === 'undelivered')
                    <div class="info-row"><strong>Undelivered Reason:</strong> {{ ucfirst(str_replace('_', ' ', $order->undelivered_reason)) }}</div>
                @endif
            </div>

            <div class="right-col">
                <div class="amount">COD: {{ number_format($order->cod_amount, 2) }} EGP</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>Notes: -</div>
            <div>Order Ref: {{ $order->reference ?? '-' }}</div>
            <div class="info-row"><strong>Generated:</strong> {{ now()->format('Y-m-d H:i') }}</div>
        </div>

        <!-- Bottom Barcode -->
        <div class="bottom-barcode">
            <div class="barcode">|| || ||| ||| || |</div>
            <div>{{ $order->waybill_number ?? '123456789' }}</div>
        </div>
    </div>
@endforeach
</body>
</html>
