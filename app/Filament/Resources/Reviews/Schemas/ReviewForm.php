<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->disabled(),

                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Reviewer')
                    ->disabled(),

                TextInput::make('rating')
                    ->numeric()
                    ->disabled(),

                TextInput::make('title')
                    ->disabled(),

                Textarea::make('body')
                    ->label('Review')
                    ->disabled()
                    ->columnSpanFull(),

                // The one actually-editable field — this is the real
                // moderation action, everything else above is just
                // context for making that decision.
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved (public)',
                        'rejected' => 'Rejected (hidden)',
                    ])
                    ->required(),
            ]);
    }
}