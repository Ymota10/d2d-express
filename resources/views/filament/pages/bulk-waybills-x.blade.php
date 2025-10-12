<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>D2D Express Waybill</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
            background: #fff;
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
            height: 60px; /* Bigger logo */
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
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 3px 5px;
            font-size: 10px;
            vertical-align: top;
            border-bottom: 1px solid #ddd;
        }
        .info-table td.label {
            font-weight: bold;
            width: 35%;
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
        }
    </style>
</head>
<body>
@foreach ($orders as $order)
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
            <div>DELIVER</div>
            <div>COD: {{ number_format($order->cod_amount, 2) }}</div>
        </div>

        <!-- RECEIVER INFO -->
        <div class="section">
            <div class="section-title">Receiver Information</div>
            <table class="info-table">
                <tr><td class="label">Name</td><td>{{ $order->receiver_name }}</td></tr>
                <tr><td class="label">Mobile 1</td><td>{{ $order->receiver_mobile_1 }}</td></tr>
                <tr><td class="label">Mobile 2</td><td>{{ $order->receiver_mobile_2 ?? 'N/A' }}</td></tr>
                <tr><td class="label">Address</td><td>{{ $order->receiver_address }}</td></tr>
                <tr><td class="label">Area</td><td>{{ $order->area->name ?? 'N/A' }}</td></tr>
                <tr><td class="label">City</td><td>{{ $order->city->name ?? 'N/A' }}</td></tr>
            </table>
        </div>

        <!-- SHIPMENT DETAILS -->
        <div class="section">
            <div class="section-title">Shipment Details</div>
            <table class="info-table">
                <tr><td class="label">Item</td><td>{{ $order->item_name }}</td></tr>
                <tr><td class="label">Description</td><td>{{ $order->description ?? 'N/A' }}</td></tr>
                <tr><td class="label">Service</td><td>{{ ucfirst(str_replace('_', ' ', $order->service_type)) }}</td></tr>
                <tr><td class="label">Weight</td><td>{{ $order->weight }} kg</td></tr>
                <tr><td class="label">Size</td><td>{{ $order->size }}</td></tr>
                <tr><td class="label">Quantity</td><td>{{ $order->quantity }}</td></tr>
                <tr><td class="label">Status</td><td>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</td></tr>
                <tr><td class="label">Open Package</td><td>{{ ucfirst($order->open_package) }}</td></tr>
                @if($order->status === 'undelivered')
                    <tr><td class="label">Reason</td><td>{{ ucfirst(str_replace('_', ' ', $order->undelivered_reason)) }}</td></tr>
                @endif
            </table>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            <div class="footer-row">
                <div>Order Ref: {{ $order->reference ?? '-' }}</div>
                <div>{{ now()->format('Y-m-d H:i') }}</div>
            </div>
        </div>
    </div>
@endforeach
</body>
</html>
