<?php

namespace App\Filament\Resources\Admins\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class AdminForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),

                TextInput::make('password')
                    ->password()
                    ->label('New password')
                    ->helperText('Leave blank to keep the current password unchanged.')
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),

                // Defaults to checked when creating a new admin here (this
                // resource is specifically for managing admin accounts),
                // but still toggleable off — e.g. to demote someone back
                // to a regular customer without deleting their account.
                Toggle::make('is_admin')
                    ->label('Admin access')
                    ->default(true)
                    ->required(),
            ]);
    }
}