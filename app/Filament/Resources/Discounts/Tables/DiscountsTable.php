<?php

namespace App\Filament\Resources\Discounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DiscountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('percentage')
                    ->formatStateUsing(fn ($state) => "{$state}%")
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime()
                    ->placeholder('Never')
                    ->sortable(),
                // Live count of actual usages, not the max_uses cap —
                // this is "how many times has this code actually been
                // redeemed so far."
                TextColumn::make('usages_count')
                    ->label('Times used')
                    ->counts('usages'),
                TextColumn::make('max_uses')
                    ->label('Max uses')
                    ->placeholder('Unlimited'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
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