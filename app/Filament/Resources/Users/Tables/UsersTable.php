<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                IconColumn::make('email_verified_at')
                    ->label('Verified')
                    // Boolean column driven by whether email_verified_at
                    // has a value at all, not its actual timestamp content —
                    // shows a clean check/cross icon instead of a date.
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->email_verified_at !== null),
                IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // Ternary filter gives three states: all / admins only /
                // customers only — useful once your user list grows past
                // a handful of accounts.
                TernaryFilter::make('is_admin')
                    ->label('Account type')
                    ->trueLabel('Admins only')
                    ->falseLabel('Customers only')
                    ->placeholder('All users'),
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