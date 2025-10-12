<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\Widget;

class OrderStatusTimelineWidget extends Widget
{
    protected static string $view = 'filament.widgets.order-status-timeline-widget';

    public ?Order $record = null;

    public function mount(): void
    {
        // Filament automatically provides $this->record when inside a ViewRecord page
        $this->record = $this->record ?? request()->route('record');
    }

    public function getSteps(): array
    {
        if (! $this->record instanceof Order) {
            return [];
        }

        $steps = [
            'pickup_request' => 'Created',
            'warehouse_received' => 'Received at Warehouse',
            'in_progress' => 'In Progress',
            'out_for_delivery' => 'Out for Delivery',
            'success_delivery' => 'Delivered',
        ];

        $completedKeys = $this->getCompletedKeys();
        $timeline = [];

        foreach ($steps as $key => $label) {
            $time = '-';

            if ($key === 'pickup_request') {
                $time = optional($this->record->created_at)->format('D, j M \\a\\t g:i A');
            } elseif (in_array($key, $completedKeys) && $key !== 'pickup_request') {
                $time = optional($this->record->updated_at)->format('D, j M \\a\\t g:i A');
            }

            $timeline[] = [
                'key' => $key,
                'label' => $label,
                'time' => $time,
                'completed' => in_array($key, $completedKeys),
            ];
        }

        return $timeline;
    }

    public function getCompletedKeys(): array
    {
        if (! $this->record instanceof Order) {
            return ['pickup_request'];
        }

        return match ($this->record->status) {
            'pickup_request' => ['pickup_request'],
            'warehouse_received' => ['pickup_request', 'warehouse_received'],
            'in_progress' => ['pickup_request', 'warehouse_received', 'in_progress'],
            'out_for_delivery', 'rescheduled' => ['pickup_request', 'warehouse_received', 'in_progress', 'out_for_delivery'],
            'success_delivery' => ['pickup_request', 'warehouse_received', 'in_progress', 'out_for_delivery', 'success_delivery'],
            default => ['pickup_request'],
        };
    }

    public function getTimeline(): array
    {
        if (! $this->record instanceof Order) {
            return [];
        }

        $userName = \Auth::user()?->name ?? 'System';
        $status = $this->record->status;
        $timeline = [];

        // ✅ 1. Order created
        $timeline[] = [
            'title' => 'Order is created',
            'details' => 'Action by: '.$userName,
            'date' => optional($this->record->created_at)->format('l, j F Y - g:i A'),
            'type' => 'created',
        ];

        // ✅ 2. AWB Printed
        if (in_array($status, ['pickup_request', 'warehouse_received', 'in_progress', 'out_for_delivery', 'success_delivery'])) {
            $timeline[] = [
                'title' => 'AWB Printed',
                'details' => 'Printed by '.$userName,
                // 'date' => optional($this->record->awb_printed_at ?? $this->record->created_at)->format('l, j F Y - g:i A'),
                'type' => 'awb_printed',
            ];
        }

        // ✅ 3. Warehouse received
        if (in_array($status, ['warehouse_received', 'in_progress', 'out_for_delivery', 'success_delivery'])) {
            // 3.1 Received at final distribution point
            $timeline[] = [
                'title' => 'The order has been successfully received at the central warehouse.',
                'details' => 'Cairo Sorting Facility',
                // 'date' => optional($this->record->warehouse_received_at ?? $this->record->updated_at)->format('l, j F Y - g:i A'),
                'type' => 'central_warehouse',
            ];

            // 3.2 Received at central warehouse
            $timeline[] = [
                'title' => 'The order has been successfully received at the final distribution point..',
                'details' => 'D2D Hubs',
                // 'date' => optional($this->record->central_received_at ?? $this->record->updated_at)->format('l, j F Y - g:i A'),
                'type' => 'final_hub',

            ];
        }

        // ✅ 4. In progress
        if (in_array($status, ['in_progress', 'out_for_delivery', 'success_delivery'])) {
            $timeline[] = [
                'title' => 'Order is in progress',
                'details' => null,
                'date' => optional($this->record->updated_at)->format('l, j F Y - g:i A'),
                'type' => 'in_progress',
            ];
        }

        // ✅ 5. Out for delivery
        if (in_array($status, ['out_for_delivery', 'success_delivery'])) {
            $timeline[] = [
                'title' => 'Order is out for delivery',
                'details' => null,
                'date' => optional($this->record->updated_at)->format('l, j F Y - g:i A'),
                'type' => 'out_for_delivery',
            ];
        }

        // ✅ 6. Rescheduled
        if ($status === 'time_scheduled') {
            $timeline[] = [
                'title' => 'Order is rescheduled - customer postponed delivery to '.optional($this->record->rescheduled_to)->format('l, j F Y'),
                'details' => null,
                'date' => optional($this->record->rescheduled_at ?? $this->record->updated_at)->format('l, j F Y - g:i A'),
                'type' => 'rescheduled',
            ];
        }

        // ✅ 7. Delivered
        if ($status === 'success_delivery') {
            $timeline[] = [
                'title' => 'Order is delivered',
                'details' => null,
                'date' => optional($this->record->updated_at)->format('l, j F Y - g:i A'),
                'type' => 'delivered',
            ];
        }

        return array_reverse($timeline); // newest first
    }
}
