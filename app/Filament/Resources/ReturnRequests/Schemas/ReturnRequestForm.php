<?php

namespace App\Filament\Resources\ReturnRequests\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;

class ReturnRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    // Shows the order_number instead of the raw ID, and
                    // disabled — the linked order is set at creation time
                    // by the customer's request and should never change.
                    ->relationship('order', 'order_number')
                    ->disabled(),
                TextInput::make('reference_number')
                    ->label('Reference Number')
                    ->disabled(),    

                TextInput::make('reason')
                    ->disabled(),

                Textarea::make('notes')
                    ->label('Customer notes')
                    ->disabled()
                    ->columnSpanFull(),

                    Select::make('status')
                    ->options([
                        'pending' => 'Pending Review',
                        'approved' => 'Approved — awaiting item',
                        'rejected' => 'Rejected',
                        'refunded' => 'Refunded',
                    ])
                    ->required()
                    ->live()
                        // Once a real Stripe refund has actually happened (status =
                        // 'refunded'), the status can no longer be changed from this
                        // dropdown — the money has genuinely moved, so this becomes a
                        // locked historical record rather than something editable.
                        // $record is null when creating a new one, so ?-> and the
                        // fallback `?? false` handle that case safely.
                    ->disabled(fn ($record) => $record?->status === 'refunded')
                    ->helperText(fn ($record) => $record?->status === 'refunded'
                            ? 'This refund has already been processed via Stripe and can no longer be changed here.'
                            : 'Once approved, use the "Process Refund" button on the list once the returned item has physically arrived — don\'t set this to Refunded manually, that skips the actual Stripe refund.'
                        ),

                Textarea::make('admin_response')
                    ->label('Response to customer (shown in the rejection email)')
                    ->columnSpanFull()
                    // $get('status') reads the CURRENT value of the status field as
                    // the admin is actively editing it — thanks to ->live() above,
                    // this re-evaluates instantly when they change the dropdown,
                    // without needing to save the form first.
                    ->visible(fn (Get $get) => $get('status') === 'rejected')
                    ->disabled(fn ($record) => $record?->status === 'refunded')
                    ->helperText('Explain why, so the customer isn\'t left guessing.'),
                TextInput::make('refund_amount')
                    ->numeric()
                    ->prefix('$')
                    ->disabled()
                    ->helperText('Set automatically when a refund is processed.'),

                TextInput::make('stripe_refund_id')
                    ->label('Stripe Refund ID')
                    ->disabled(),
            ]);
    }
}