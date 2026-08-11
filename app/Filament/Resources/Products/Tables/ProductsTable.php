<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    // Pulls each product's primary image URL live from its images
                    // relation, rather than storing a duplicate copy on the product
                    // itself — one source of truth for "what is this product's photo."
                    ->getStateUsing(fn ($record) => $record->primaryImage()?->url())
                    ->square()
                    // Clickable to view full-size, same lightbox behaviour as the
                    // Images tab and the Order items thumbnail.
                    ->lightbox(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('base_price')
                    ->money('AUD')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'draft' => 'gray',
                        'archived' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('variants_count')
                    ->counts('variants')
                    ->label('Variants'),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'draft' => 'Draft',
                    'active' => 'Active',
                    'archived' => 'Archived',
                ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
