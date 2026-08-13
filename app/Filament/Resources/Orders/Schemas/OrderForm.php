<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Forms\Components\DateTimePicker;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->disabled(),

                TextInput::make('email')
                    ->label('Email address')
                    ->disabled(),

                TextInput::make('shipping_name')
                    ->label('Customer name')
                    ->disabled(),

                Textarea::make('shipping_address')
                    ->disabled()
                    ->columnSpanFull(),

                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'fulfilled' => 'Ready to Ship',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required(),

                    Select::make('carrier')
                    ->label('Carrier')
                    ->options([
                        'Australia Post' => 'Australia Post',
                        'Sendle' => 'Sendle',
                        'Shippit' => 'Shippit',
                        'Other' => 'Other',
                    ]),
                
                TextInput::make('tracking_number')
                    ->label('Tracking number'),
                
                DateTimePicker::make('shipped_at')
                    ->label('Shipped at')
                    ->disabled()
                    ->helperText('Set automatically when status changes to Shipped.'),
                
                DateTimePicker::make('delivered_at')
                    ->label('Delivered at')
                    ->disabled()
                    ->helperText('Set automatically when status changes to Delivered.'),

                TextInput::make('subtotal')
                    ->numeric()
                    ->prefix('$')
                    ->disabled(),

                TextInput::make('shipping_cost')
                    ->numeric()
                    ->prefix('$')
                    ->disabled(),

                TextInput::make('gst')
                    ->label('GST (incl.)')
                    ->numeric()
                    ->prefix('$')
                    ->disabled(),

                TextInput::make('total')
                    ->numeric()
                    ->prefix('$')
                    ->disabled(),

                TextInput::make('stripe_checkout_session_id')
                    ->label('Stripe session ID')
                    ->disabled(),
            ]);
    }
}