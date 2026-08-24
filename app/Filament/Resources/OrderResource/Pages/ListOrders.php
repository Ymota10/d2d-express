<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\ListBarStats::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),

            'pickup_request' => Tab::make('Pickup Request')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pickup_request')
                ),

            'warehouse_received' => Tab::make('Warehouse Received')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'warehouse_received')
                ),

            'out_for_delivery' => Tab::make('Out for Delivery')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'out_for_delivery')
                ),

            'success_delivery' => Tab::make('Successful Delivery')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'success_delivery')
                ),

            'partial_return' => Tab::make('Partial Delivery')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'partial_return')
                ),

            // 'partial_return_2' => Tab::make('Partial Return')
            //     ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'partial_return_2')
            //     ),

            'time_scheduled' => Tab::make('Time Scheduled')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'time_scheduled')
                ),

            'failed_attempt' => Tab::make('Failed Attempt')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'failed_attempt')
                ),

            'undelivered' => Tab::make('Undelivered')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'undelivered')
                ),

            'returned_and_cost_paid' => Tab::make('Returned & Cost Paid')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'returned_and_cost_paid')
                ),

            'returned_to_warehouse' => Tab::make('Returned to Warehouse')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'returned_to_warehouse')
                ),

            'returned_to_shipper' => Tab::make('Returned to Shipper')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'returned_to_shipper')
                ),
        ];
    }
}
