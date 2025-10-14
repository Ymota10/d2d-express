<?php

namespace App\Imports;

use App\Models\Area;
use App\Models\City;
use App\Models\Order;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class OrdersImport implements SkipsOnFailure, ToCollection, WithHeadingRow, WithValidation
{
    use SkipsFailures;

    protected $successCount = 0;

    protected $skippedCount = 0;

    protected $errorDetails = []; // ✅ Store row errors

    public function collection(Collection $rows)
    {
        $user = auth()->user();

        foreach ($rows as $index => $row) {
            $missing = [];

            // ✅ Check required fields and collect missing ones
            foreach (['receiver_name', 'receiver_mobile_1', 'city', 'area'] as $field) {
                if (empty($row[$field])) {
                    $missing[] = ucfirst(str_replace('_', ' ', $field));
                }
            }

            if (! empty($missing)) {
                $this->skippedCount++;
                $this->errorDetails[] = [
                    'row' => $index + 2, // +2 because headings start at row 1
                    'missing_fields' => implode(', ', $missing),
                    'data' => $row,
                ];

                continue;
            }

            // ✅ Auto-create related models
            $city = City::firstOrCreate(
                ['name' => trim($row['city'])],
                ['company_id' => $user->company_id ?? null]
            );

            $area = Area::firstOrCreate(
                ['name' => trim($row['area']), 'city_id' => $city->id],
                ['company_id' => $user->company_id ?? null]
            );

            try {
                Order::create([
                    'users_id' => $user->id,
                    'shipper_id' => $user->id,
                    'city_id' => $city->id,
                    'area_id' => $area->id,
                    'receiver_name' => $row['receiver_name'],
                    'receiver_mobile_1' => $row['receiver_mobile_1'],
                    'receiver_mobile_2' => $row['receiver_mobile_2'] ?? null,
                    'receiver_address' => $row['address'],
                    'item_name' => $row['item_name'],
                    'quantity' => $row['quantity'] ?? 1,
                    'size' => $row['size'] ?? null,
                    'weight' => $row['weight'] ?? 0,
                    'cod_amount' => $row['cod_amount'] ?? 0,
                    'service_type' => $row['service_type'] ?? 'normal_cod',
                    'delivery_cost' => $row['delivery_cost'] ?? 0,
                    'open_package' => strtolower($row['open_package'] ?? 'no') === 'yes' ? 'yes' : 'no',
                    'open_package_fee' => $row['open_package_fee'] ?? 0,
                    'status' => 'pickup_request',
                ]);

                $this->successCount++;
            } catch (\Throwable $e) {
                $this->skippedCount++;
                $this->errorDetails[] = [
                    'row' => $index + 2,
                    'missing_fields' => 'Unexpected error: '.$e->getMessage(),
                    'data' => $row,
                ];
            }
        }
    }

    public function rules(): array
    {
        return [
            'receiver_name' => 'required|string',
            'receiver_mobile_1' => 'required|string',
            'city' => 'required|string',
            'area' => 'required|string',
        ];
    }

    public function getSummary(): array
    {
        return [
            'success' => $this->successCount,
            'skipped' => $this->skippedCount,
            'failures' => count($this->failures()),
            'errors' => $this->errorDetails, // ✅ Include detailed errors
        ];
    }
}
