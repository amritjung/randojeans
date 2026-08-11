<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

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
                        'fulfilled' => 'Fulfilled',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required(),

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