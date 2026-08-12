<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
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
                    TextInput::make('phone')
                    ->tel()
                    // Same Australian phone format rule used on the customer-facing
                    // registration/profile forms — optional +61 or leading 0, then
                    // 9-11 digits/spaces. Keeps admin-entered data just as clean and
                    // valid as what customers enter themselves, rather than letting
                    // admin edits bypass the format check entirely.
                    ->rule('regex:/^(\+?61|0)[0-9\s]{9,11}$/')
                    ->validationMessages([
                        'regex' => 'Please enter a valid Australian phone number, e.g. 0400 000 000.',
                    ])
                    ->nullable(),
                TextInput::make('address_street')
                    ->label('Street address'),
                TextInput::make('address_suburb')
                    ->label('Suburb'),
                Select::make('address_state')
                    ->label('State')
                    // Fixed dropdown of real Australian states/territories —
                    // matches the same list used on the customer-facing
                    // profile/checkout forms, so data stays consistent
                    // whether it was entered by a customer or fixed by an admin.
                    ->options([
                        'NSW' => 'NSW', 'VIC' => 'VIC', 'QLD' => 'QLD', 'WA' => 'WA',
                        'SA' => 'SA', 'TAS' => 'TAS', 'ACT' => 'ACT', 'NT' => 'NT',
                    ]),
                TextInput::make('address_postcode')
                    ->label('Postcode'),

                TextInput::make('password')
                    ->password()
                    ->label('New password')
                    ->helperText('Leave blank to keep the current password unchanged.')
                    // dehydrateStateUsing transforms the value right before
                    // saving — this is where the plain-text password typed
                    // into the form gets hashed, so it's never stored raw.
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    // dehydrated() controls whether this field is saved at
                    // all — if the admin left it blank, we skip saving it
                    // entirely so the user's existing password stays intact
                    // rather than being overwritten with an empty value.
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    // Only required when CREATING a new user directly in
                    // admin (rare — most users register themselves) — never
                    // required when editing, since leaving it blank is the
                    // normal "don't change the password" case.
                    ->required(fn (string $operation): bool => $operation === 'create'),

                // Toggle::make('is_admin')
                //     ->label('Admin access')
                //     ->helperText('Grants access to this admin panel. Leave off for regular customers.'),
            ]);
    }
}