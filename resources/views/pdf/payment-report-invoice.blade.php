<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Report Invoice</title>

    <style>
        @page {
            margin: 30px 36px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1F2937;
            margin: 0;
            padding: 0;
        }

        /* ===== LETTERHEAD ===== */
        .brand-bar {
            width: 100%;
            border-bottom: 3px solid #2563EB;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }

        .brand-table {
            width: 100%;
            border-collapse: collapse;
        }

        .brand-table td {
            border: none;
            padding: 0;
            vertical-align: bottom;
        }

        .brand-mark {
            font-size: 24px;
            font-weight: bold;
            color: #0D1B3D;
            letter-spacing: 1px;
        }

        .brand-mark span {
            color: #2563EB;
        }

        .brand-sub {
            font-size: 9px;
            color: #6B7280;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 3px;
        }

        .doc-meta {
            text-align: right;
        }

        .doc-title {
            font-size: 15px;
            font-weight: bold;
            color: #0D1B3D;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 4px;
        }

        .doc-meta p {
            margin: 2px 0;
            font-size: 10px;
            color: #6B7280;
        }

        .doc-meta strong {
            color: #1F2937;
        }

        /* ===== REPORT INFO PANEL ===== */
        table.panel {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        table.panel td {
            padding: 8px 10px;
            border: 1px solid #E5E7EB;
            font-size: 10.5px;
        }

        table.panel .lbl {
            background: #F8FAFC;
            font-weight: bold;
            color: #0D1B3D;
            width: 22%;
        }

        table.panel .val {
            width: 28%;
        }

        table.panel .final .lbl,
        table.panel .final .val {
            background: #EFF4FE;
            color: #0D1B3D;
            font-size: 12.5px;
            font-weight: bold;
        }

        /* ===== ORDERS TABLE ===== */
        h2.section-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #0D1B3D;
            margin: 0 0 8px 0;
        }

        table.orders {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 22px;
        }

        table.orders thead th {
            background: #0D1B3D;
            color: #FFFFFF;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #0D1B3D;
        }

        table.orders td {
            padding: 7px 6px;
            border: 1px solid #E5E7EB;
            font-size: 10px;
        }

        table.orders tbody tr:nth-child(even) td {
            background: #F8FAFC;
        }

        .number {
            text-align: right;
        }

        .status-delivered { color: #2E9E6D; font-weight: bold; }
        .status-failed    { color: #D6483F; font-weight: bold; }
        .status-progress  { color: #2563EB; font-weight: bold; }

        /* ===== SUMMARY (no-report mode) ===== */
        table.summary {
            width: 56%;
            margin-left: auto;
            border-collapse: collapse;
        }

        table.summary td {
            padding: 8px 10px;
            border: 1px solid #E5E7EB;
            font-size: 10.5px;
        }

        table.summary .label {
            font-weight: bold;
            color: #0D1B3D;
        }

        table.summary tr.total td {
            background: #0D1B3D;
            color: #FFFFFF;
            font-size: 13px;
            font-weight: bold;
            border-color: #0D1B3D;
        }

        /* ===== FOOTER ===== */
        .footer-note {
            margin-top: 26px;
            padding-top: 10px;
            border-top: 1px solid #E5E7EB;
            font-size: 9px;
            color: #9CA3AF;
            text-align: center;
        }
    </style>
</head>

<body>

    {{-- LETTERHEAD --}}
    <div class="brand-bar">
        <table class="brand-table">
            <tr>
                <td style="width: 60%;">
                    <div class="brand-mark">LY<span>NK</span></div>
                    <div class="brand-sub">Logistics &amp; Delivery</div>
                </td>
                <td class="doc-meta" style="width: 40%;">
                    <div class="doc-title">Payment Report Invoice</div>

                    @if($paymentReport)
                        <p><strong>Report #</strong> {{ $paymentReport->id }}</p>
                        <p><strong>Date:</strong> {{ optional($paymentReport->created_at)->format('Y-m-d H:i') }}</p>
                    @else
                        <p><strong>Date:</strong> {{ now()->format('Y-m-d H:i') }}</p>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- REPORT INFO --}}
    @if($paymentReport)
        <table class="panel">
            <tr>
                <td class="lbl">Shipper</td>
                <td class="val">{{ $orders->first()?->user?->name ?? '-' }}</td>
                <td class="lbl">Orders</td>
                <td class="val">{{ $orders->count() }}</td>
            </tr>
            <tr>
                <td class="lbl">Total COD</td>
                <td class="val">{{ number_format($paymentReport->total_cod ?? 0, 2) }} EGP</td>
                <td class="lbl">Delivery Fees</td>
                <td class="val">{{ number_format($paymentReport->total_delivery_cost ?? 0, 2) }} EGP</td>
            </tr>
            <tr>
                <td class="lbl">Insurance Fees</td>
                <td class="val">{{ number_format($paymentReport->total_insurance_fees ?? 0, 2) }} EGP</td>
                <td class="lbl">Open Package Fees</td>
                <td class="val">{{ number_format($paymentReport->total_open_package_fees ?? 0, 2) }} EGP</td>
            </tr>
            <tr class="final">
                <td class="lbl">Extra Fees</td>
                <td class="val">{{ number_format($paymentReport->extra_fees ?? 0, 2) }} EGP</td>
                <td class="lbl">Final Amount</td>
                <td class="val">{{ number_format($paymentReport->final_amount ?? 0, 2) }} EGP</td>
            </tr>
        </table>
    @endif

    {{-- ORDERS --}}
    <h2 class="section-title">Order Details</h2>

    <table class="orders">
        <thead>
            <tr>
                <th>#</th>
                <th>Waybill</th>
                <th>Order ID</th>
                <th>Receiver</th>
                <th>Mobile</th>
                <th>City</th>
                <th>Area</th>
                <th>Status</th>
                <th class="number">COD</th>
                <th class="number">Delivery</th>
            </tr>
        </thead>

        <tbody>
            @foreach($orders as $index => $order)
                @php
                    $statusClass = 'status-progress';

                    if (in_array($order->status, ['success_delivery', 'partial_return'])) {
                        $statusClass = 'status-delivered';
                    } elseif (in_array($order->status, ['undelivered', 'returned_to_shipper', 'returned_and_cost_paid'])) {
                        $statusClass = 'status-failed';
                    }
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $order->waybill_number }}</td>
                    <td>{{ $order->order_id }}</td>
                    <td>{{ $order->receiver_name }}</td>
                    <td>{{ $order->receiver_mobile_1 }}</td>
                    <td>{{ $order->city?->name ?? '-' }}</td>
                    <td>{{ $order->area?->name ?? '-' }}</td>
                    <td class="{{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</td>
                    <td class="number">{{ number_format($order->cod_amount ?? 0, 2) }}</td>
                    <td class="number">{{ number_format($order->delivery_cost ?? 0, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- SUMMARY (only when this isn't tied to a saved payment report) --}}
    @if(!$paymentReport)
        <table class="summary">
            <tr>
                <td class="label">Total COD</td>
                <td class="number">{{ number_format($orders->sum('cod_amount'), 2) }} EGP</td>
            </tr>
            <tr>
                <td class="label">Total Delivery Fees</td>
                <td class="number">{{ number_format($orders->sum('delivery_cost'), 2) }} EGP</td>
            </tr>
            <tr class="total">
                <td>Net Amount</td>
                <td class="number">
                    {{
                        number_format(
                            $orders->sum('cod_amount')
                            - $orders->sum('delivery_cost'),
                            2
                        )
                    }} EGP
                </td>
            </tr>
        </table>
    @endif

    <div class="footer-note">Generated by Lynk &middot; This document is system-generated and does not require a signature.</div>

</body>
</html>