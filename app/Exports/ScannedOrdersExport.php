<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ScannedOrdersExport implements FromCollection, WithHeadings
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function collection()
    {
        return collect($this->orders)->map(function ($order) {

            return [
                'Waybill' => $order['waybill_number'],
                'Order ID' => $order['order_id'],
                'Consignee' => $order['receiver_name'] ?? 'N/A',
                'Receiver Mobile' => $order['receiver_mobile_1'] ?? 'N/A',
                'Item Name' => $order['item_name'] ?? 'N/A',
                'Shipper' => $order['user']['name'] ?? 'N/A',
                'Status' => $order['status'],
                'Area' => $order['area']['name'] ?? 'N/A',
                'COD' => $order['cod_amount'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Waybill',
            'Order ID',
            'Consignee',
            'Receiver Mobile',
            'Item Name',
            'Shipper',
            'Status',
            'Area',
            'COD',
        ];
    }
}
