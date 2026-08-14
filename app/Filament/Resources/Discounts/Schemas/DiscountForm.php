<?php

namespace App\Filament\Resources\Discounts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class DiscountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    // Automatically uppercases whatever the admin types,
                    // right before it's saved — matches how the checkout
                    // form matches codes case-insensitively, so "save10"
                    // typed here becomes "SAVE10" consistently everywhere.
                    ->dehydrateStateUsing(fn (string $state) => strtoupper(trim($state)))
                    ->helperText('Stored uppercase automatically, e.g. "SAVE10".'),

                TextInput::make('percentage')
                    ->label('Discount percentage')
                    ->required()
                    ->numeric()
                    ->suffix('%')
                    ->minValue(1)
                    ->maxValue(100),

                Toggle::make('is_active')
                    ->default(true)
                    ->helperText('Turn off to disable a code without deleting it.'),

                DateTimePicker::make('expires_at')
                    ->label('Expires at')
                    ->helperText('Leave blank for a code that never expires.'),

                TextInput::make('max_uses')
                    ->label('Maximum total uses')
                    ->numeric()
                    ->helperText('Leave blank for unlimited uses (still single-use per customer regardless).'),
            ]);
    }
}