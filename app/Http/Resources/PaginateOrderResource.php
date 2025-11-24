<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaginateOrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'waybill_number' => $this->waybill_number,
            'status' => $this->status,
            'service_type' => $this->service_type,
            'open_package' => $this->open_package,
            'open_package_fee' => $this->open_package_fee, // new field
            'cod_amount' => $this->cod_amount,
            'delivery_cost' => $this->delivery_cost,
            'receiver_name' => $this->receiver_name,
            'receiver_mobile_1' => $this->receiver_mobile_1,
            'receiver_mobile_2' => $this->receiver_mobile_2,
            'receiver_address' => $this->receiver_address,
            'area' => $this->area?->name,
            'city' => $this->city?->name,
            'shipper' => $this->user?->name,
            'item_name' => $this->item_name,
            'description' => $this->description,
            'order_id' => $this->order_id,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
