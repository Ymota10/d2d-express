<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrdersDemoExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return collect([
            [
                'Area' => 'Nasr City',
                'City' => 'Cairo',
                'Receiver Name' => 'Ahmed Ali',
                'Receiver Mobile 1' => '01012345678',
                'Receiver Mobile 2' => '01123456789',
                'Address' => '12 Abbas El Akkad St, Nasr City, Cairo',
                'Item Name' => 'T-Shirt',
                'Quantity' => 2,
                'Size' => 'L',
                'Weight' => 0.5,
                'COD Amount' => 670.00,
                'Service Type' => 'normal_cod',
                'Open Package' => 'yes',
                'Open Package Fee' => 5,
                'Delivery Cost' => 70.00,
            ],
            [
                'Area' => 'Smoha',
                'City' => 'Alexandria',
                'Receiver Name' => 'Sara Mahmoud',
                'Receiver Mobile 1' => '01098765432',
                'Receiver Mobile 2' => '',
                'Address' => '45 Fouad Street, Smoha, Alexandria',
                'Item Name' => 'Jeans',
                'Quantity' => 1,
                'Size' => 'M',
                'Weight' => 0.8,
                'COD Amount' => 980.00,
                'Service Type' => 'replacement',
                'Open Package' => 'no',
                'Open Package Fee' => 0.00,
                'Delivery Cost' => 80.00,
            ],
        ]);
    }

    public function headings(): array
    {
        return [
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
            'Service Type',
            'Open Package',
            'Open Package Fee',
            'Delivery Cost',
        ];
    }
}
