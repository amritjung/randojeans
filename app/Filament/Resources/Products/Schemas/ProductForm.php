<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $state, callable $set) => $set('slug', Str::slug($state))),

                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),

                Textarea::make('description')
                    ->columnSpanFull()
                    ->rows(3),

                Textarea::make('story')
                    ->label('Story / fabric & fit details')
                    ->columnSpanFull()
                    ->rows(4)
                    ->helperText('Shown in the care-tag block on the product page.'),

                TextInput::make('base_price')
                    ->label('Base price (AUD)')
                    ->required()
                    ->numeric()
                    ->prefix('$'),

                TextInput::make('sale_price')
                    ->label('Sale price (optional)')
                    ->numeric()
                    ->prefix('$')
                    ->helperText('Leave blank for no sale. Must be lower than the base price to actually show as on sale.')
                    ->lt('base_price'), // built-in Filament validation: must be Less Than the base_price field

                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'archived' => 'Archived',
                    ])
                    ->required()
                    ->default('draft'),
            ]);
    }
}
