<?php

namespace App\Filament\Resources\ReturnRequests\Tables;

use App\Models\ReturnRequestItem;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Stripe\Refund;
use Stripe\Stripe;
use App\Mail\RefundProcessed;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class ReturnRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_number')
                    ->label('Order')
                    ->searchable(),
                TextColumn::make('reference_number')
                    ->label('Reference')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('reason')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'info',
                        'rejected' => 'danger',
                        'refunded' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('refund_amount')
                    ->money('AUD')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                    'refunded' => 'Refunded',
                ]),
            ])
            ->recordActions([
                EditAction::make(),

                // The actual refund-processing button — only shown when
                // status is 'approved' (staff has already reviewed and
                // confirmed the item was returned), and does the real
                // Stripe API call to issue the refund.
                Action::make('processRefund')
                    ->label('Process Refund')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'approved')
                    // requiresConfirmation() shows a confirmation dialog
                    // before running — important for an action that
                    // moves real money, so a stray click can't trigger
                    // an actual refund.
                    ->requiresConfirmation()
                    ->modalDescription('This will refund the customer via Stripe for the returned item(s). This cannot be undone from here.')
                    ->action(function ($record) {
                        // Atomic lock, same pattern as checkout's duplicate-submission
                        // protection — if two clicks (or two staff members) hit this
                        // action for the same return request at nearly the same instant,
                        // only one actually acquires the lock and proceeds. The other
                        // exits immediately without issuing a second real Stripe refund.
                        $lock = Cache::lock('process-refund-' . $record->id, 15);
                    
                        if (! $lock->get()) {
                            Notification::make()
                                ->title('Already processing')
                                ->body('This refund is already being processed.')
                                ->warning()
                                ->send();
                            return;
                        }
                    
                        try {
                            // Re-fetch fresh from the database rather than trusting the
                            // $record passed in — guards against acting on stale data if
                            // something changed this return request's status between when
                            // the page loaded and when this button was actually clicked.
                            $record->refresh();
                    
                            if ($record->status !== 'approved') {
                                Notification::make()
                                    ->title('Cannot process')
                                    ->body('This return is no longer in an approved state — it may have already been refunded.')
                                    ->warning()
                                    ->send();
                                return;
                            }
                    
                            $returnItems = ReturnRequestItem::where('return_request_id', $record->id)
                                ->with('orderItem.productVariant')
                                ->get();
                    
                            $refundAmount = $returnItems->sum(fn ($item) => $item->orderItem->unit_price * $item->quantity);
                    
                            Stripe::setApiKey(config('services.stripe.secret'));
                    
                            $refund = Refund::create([
                                'payment_intent' => $record->order->stripe_payment_intent_id,
                                'amount' => (int) round($refundAmount * 100),
                            ]);
                    
                            $record->update([
                                'status' => 'refunded',
                                'refund_amount' => $refundAmount,
                                'stripe_refund_id' => $refund->id,
                            ]);
                    
                            // Restocking: add the returned quantity back to each variant's
                            // available stock, since the physical item is now back with
                            // you. This assumes the returned item is in resellable
                            // condition — if you ever receive something damaged/unsellable,
                            // you'd want to skip this and adjust stock manually instead.
                            foreach ($returnItems as $item) {
                                $item->orderItem->productVariant?->increment('stock_quantity', $item->quantity);
                            }
                    
                            Mail::to($record->order->email)->send(new RefundProcessed($record));
                    
                            Notification::make()
                                ->title('Refund processed')
                                ->body("\${$refundAmount} refunded via Stripe and stock restocked.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Refund failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        } finally {
                            $lock->release();
                        }
                    }),
            ])
            ->toolbarActions([]);
    }
}