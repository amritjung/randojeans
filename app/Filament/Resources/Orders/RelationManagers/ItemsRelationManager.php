<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    // Tells Filament which relationship on the Order model this tab represents —
    // matches the items() method we defined on the Order model.
    protected static string $relationship = 'items';

    // No add/edit form — order items are a historical snapshot of what was
    // actually purchased, so they should never be editable after the fact.
    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    // Pulls the product's current primary image URL for this
                    // order line, computed live from the relationship chain
                    // (order item -> variant -> product -> primary image),
                    // rather than being stored as its own database column.
                    ->getStateUsing(fn ($record) => $record->productVariant?->product?->primaryImage()?->url())
                    ->square()
                    // Makes the thumbnail clickable, opening it full-size in
                    // a popup overlay via the lightbox plugin we installed.
                    ->lightbox(),
                TextColumn::make('product_name')
                    ->label('Product'),
                TextColumn::make('variant_label')
                    ->label('Size'),
                TextColumn::make('quantity')
                    ->numeric(),
                TextColumn::make('unit_price')
                    ->money('AUD'),
            ])
            // No header/record/toolbar actions — read-only, matches the
            // "receipt, not editable data" intent above.
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}