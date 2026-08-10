<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('path')
                ->label('Image or video')
                ->disk('public')
                ->directory('products')
                ->acceptedFileTypes(['image/*', 'video/mp4', 'video/quicktime'])
                ->required(),

            Select::make('type')
                ->options(['image' => 'Image', 'video' => 'Video'])
                ->required()
                ->default('image'),

            TextInput::make('alt_text')
                ->label('Alt text (describe the image for accessibility/SEO)'),

            Toggle::make('is_primary')
                ->label('Use as main product photo'),

            TextInput::make('sort_order')
                ->numeric()
                ->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('alt_text')
            ->columns([
                ImageColumn::make('path')->disk('public'),
                TextColumn::make('type')->badge(),
                TextColumn::make('alt_text'),
                IconColumn::make('is_primary')->boolean(),
                TextColumn::make('sort_order'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order');
    }
}
