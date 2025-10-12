<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    protected array $ids;

    public function __construct(array $ids = [])
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        $query = Order::with(['user', 'area', 'city']);
        if (! empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        return $query->get();
    }

    public function map($order): array
    {
        return [
            $order->id,
            $order->waybill_number,
            $order->user?->name,
            $order->area?->name,
            $order->city?->name,
            $order->receiver_name,
            $order->receiver_mobile_1,
            $order->receiver_mobile_2,
            $order->receiver_address,
            $order->item_name,
            $order->quantity,
            $order->size,
            $order->weight,
            $order->cod_amount,
            $order->delivery_cost,
            $order->open_package_fee,
            $order->status,
            $order->service_type,
            $order->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Waybill Number',
            'Shipper',
            'Area',
            'City',
            'Receiver Name',
            'Receiver Mobile 1',
            'Receiver Mobile 2',
            'Address',
            'Item Name',
            'Quantity',
            'Size',
            'Weight',
            'COD Amount',
            'Delivery Cost',
            'Open Package Fees',
            'Status',
            'Service Type',
            'Created At',
        ];
    }
}
