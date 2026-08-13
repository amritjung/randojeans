<?php

namespace App\Filament\Resources\ReturnRequests\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('orderItem.product_name')
                    ->label('Product'),
                TextColumn::make('orderItem.variant_label')
                    ->label('Size'),
                TextColumn::make('quantity')
                    ->label('Qty returning')
                    ->numeric(),
                TextColumn::make('orderItem.unit_price')
                    ->label('Unit price')
                    ->money('AUD'),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}