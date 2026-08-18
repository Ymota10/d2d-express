<?php

namespace App\Filament\Resources\FinancialAnalysisResource\Pages;

use App\Filament\Resources\FinancialAnalysisResource;
use App\Imports\FlextockInvoiceImport;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ListFinancialAnalyses extends ListRecords
{
    protected static string $resource = FinancialAnalysisResource::class;

    /**
     * IDs matched from the currently imported Flextock invoice.
     */
    public array $invoiceMatchedOrderIds = [];

    /**
     * Automatically selected records in the Filament table.
     */
    public array $selectedTableRecords = [];

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\FinancialSummary::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import_flextock_invoice')
                ->label('Import Invoice')
                ->icon('heroicon-o-document-arrow-up')
                ->color('success')
                ->visible(
                    fn () => auth()->user()?->isAdmin()
                )
                ->form([
                    FileUpload::make('invoice')
                        ->label('Invoice')
                        ->required()
                        ->disk('public')
                        ->directory('flextock-invoices')
                        ->rules(['mimes:xlsx,xls']),
                ])
                ->action(function (array $data) {

                    /*
                     * =====================================================
                     * 1. Get uploaded invoice
                     * =====================================================
                     */

                    $invoicePath = $data['invoice'] ?? null;

                    if (! $invoicePath) {
                        Notification::make()
                            ->title('Invoice Upload Error')
                            ->danger()
                            ->body(
                                'The uploaded invoice file was not stored correctly.'
                            )
                            ->send();

                        return;
                    }

                    /*
                     * =====================================================
                     * 2. Resolve actual file path
                     * =====================================================
                     */

                    $path = Storage::disk('public')->path($invoicePath);

                    if (! file_exists($path)) {
                        Notification::make()
                            ->title('Invoice File Not Found')
                            ->danger()
                            ->body(
                                'The uploaded invoice file could not be found.'
                            )
                            ->send();

                        return;
                    }

                    /*
                     * =====================================================
                     * 3. Read Flextock invoice
                     * =====================================================
                     */

                    $import = new FlextockInvoiceImport;

                    Excel::import($import, $path);

                    $rows = $import->rows;

                    if ($rows->isEmpty()) {
                        Notification::make()
                            ->title('Empty Invoice')
                            ->danger()
                            ->body(
                                'The uploaded Excel file does not contain any rows.'
                            )
                            ->send();

                        return;
                    }

                    /*
                     * =====================================================
                     * 4. Read order_code
                     * =====================================================
                     */

                    $orderCodes = $rows
                        ->pluck('order_code')
                        ->filter()
                        ->map(function ($code) {
                            return trim((string) $code);
                        })
                        ->unique()
                        ->values();

                    /*
                     * =====================================================
                     * 5. Match order_code against waybill_number
                     * =====================================================
                     */

                    $orders = Order::query()
                        ->whereIn('waybill_number', $orderCodes)
                        ->get()
                        ->keyBy(function ($order) {
                            return trim((string) $order->waybill_number);
                        });

                    $matched = collect();
                    $alreadyCollected = collect();
                    $notFound = collect();

                    foreach ($orderCodes as $orderCode) {

                        $order = $orders->get($orderCode);

                        /*
                         * Not found
                         */
                        if (! $order) {
                            $notFound->push($orderCode);

                            continue;
                        }

                        /*
                         * Valid order to select
                         */
                        $matched->push($order);
                    }

                    /*
                     * =====================================================
                     * 6. Store matched IDs in session
                     *
                     * This session key does TWO things:
                     *
                     * 1. FinancialAnalysisResource filters the table
                     * 2. ListFinancialAnalyses selects these records
                     *
                     * IMPORTANT:
                     * We store an empty array too.
                     *
                     * That means:
                     *
                     * - No session key = normal Financial Analysis
                     * - [] = invoice uploaded but zero matches
                     * - [1, 5, 8] = show only those orders
                     * =====================================================
                     */

                    $matchedIds = $matched
                        ->pluck('id')
                        ->map(fn ($id) => (string) $id)
                        ->values()
                        ->toArray();

                    session()->put(
                        'flextock_invoice_selected_ids',
                        $matchedIds
                    );

                    /*
                     * =====================================================
                     * 7. Notification
                     * =====================================================
                     */

                    Notification::make()
                        ->title('Flextock Invoice Analyzed')
                        ->success()
                        ->body(
                            'Invoice rows: '.$rows->count().
                            ' | Order codes: '.$orderCodes->count().
                            ' | Matched: '.$matched->count().
                            ' | Already collected: '.$alreadyCollected->count().
                            ' | Not found: '.$notFound->count()
                        )
                        ->send();

                    /*
                     * =====================================================
                     * 8. Redirect back to Financial Analysis
                     * =====================================================
                     */

                    return redirect()->to(
                        static::getResource()::getUrl('index', [
                            'invoice_filter' => 1,
                        ])
                    );
                }),
        ];
    }

    /**
     * Load invoice matched IDs when the page mounts.
     *
     * We intentionally do NOT use hydrate().
     */
    public function mount(): void
    {
        parent::mount();

        if (request()->boolean('invoice_filter')) {
            $this->invoiceMatchedOrderIds = session(
                'flextock_invoice_selected_ids',
                []
            );

            $this->selectedTableRecords = $this->invoiceMatchedOrderIds;
        } else {
            $this->invoiceMatchedOrderIds = [];
            $this->selectedTableRecords = [];
        }
    }
}
