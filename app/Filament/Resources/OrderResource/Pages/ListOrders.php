<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    // protected function getHeaderWidgets(): array
    // {
    //     return [
    //         \App\Filament\Widgets\ListBarStats::class,
    //     ];
    // }

    public function getTabs(): array
    {
        return [

            'all' => Tab::make('All')
                ->badge(fn () => Order::query()->count()),

            'pickup_request' => Tab::make('Pickup Request')
                ->badge(fn () => Order::where('status', 'pickup_request')->count())
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where('status', 'pickup_request')
                ),

            'warehouse_received' => Tab::make('Warehouse Received')
                ->badge(fn () => Order::where('status', 'warehouse_received')->count())
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where('status', 'warehouse_received')
                ),

            'out_for_delivery' => Tab::make('Out for Delivery')
                ->badge(fn () => Order::where('status', 'out_for_delivery')->count())
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where('status', 'out_for_delivery')
                ),

            'success_delivery' => Tab::make('Successful Delivery')
                ->badge(fn () => Order::where('status', 'success_delivery')->count())
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where('status', 'success_delivery')
                ),

            'partial_return' => Tab::make('Partial Delivery')
                ->badge(fn () => Order::where('status', 'partial_return')->count())
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where('status', 'partial_return')
                ),

            'time_scheduled' => Tab::make('Time Scheduled')
                ->badge(fn () => Order::where('status', 'time_scheduled')->count())
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where('status', 'time_scheduled')
                ),

            'failed_attempt' => Tab::make('Failed Attempt')
                ->badge(fn () => Order::where('status', 'failed_attempt')->count())
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where('status', 'failed_attempt')
                ),

            'undelivered' => Tab::make('Undelivered')
                ->badge(fn () => Order::where('status', 'undelivered')->count())
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where('status', 'undelivered')
                ),

            'returned_and_cost_paid' => Tab::make('Returned & Cost Paid')
                ->badge(fn () => Order::where('status', 'returned_and_cost_paid')->count())
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where('status', 'returned_and_cost_paid')
                ),

            'returned_to_warehouse' => Tab::make('Returned to Warehouse')
                ->badge(fn () => Order::where('status', 'returned_to_warehouse')->count())
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where('status', 'returned_to_warehouse')
                ),

            'returned_to_shipper' => Tab::make('Returned to Shipper')
                ->badge(fn () => Order::where('status', 'returned_to_shipper')->count())
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where('status', 'returned_to_shipper')
                ),
        ];
    }
}
