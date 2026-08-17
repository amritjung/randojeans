<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Product')
                ->columnSpanFull()
                ->tabs([

                    // ── TAB 1: General ──────────────────────────────
                    Tabs\Tab::make('General')->schema([
                        Grid::make(3)->schema([

                            \Filament\Forms\Components\Group::make()->columnSpan(2)->schema([

                                Section::make('Basic info')->schema([
                                    TextInput::make('name')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn ($state, callable $set) =>
                                            $set('slug', Str::slug($state))),

                                    TextInput::make('slug')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->prefix('randojeans.com/products/'),

                                    Grid::make(2)->schema([
                                        TextInput::make('base_price')
                                            ->numeric()
                                            ->required()
                                            ->prefix('AU$')->label('Price'),

                                        TextInput::make('art_number')
                                            ->label('Art number')
                                            ->placeholder('d23699-d315-001'),
                                    ]),

                                    Textarea::make('model_note')
                                        ->label('Model note')
                                        ->rows(2)
                                        ->helperText('Shown beneath the colour selector on the product page.'),
                                ]),

                                Section::make('Colours')->schema([
                                    Repeater::make('colors')
                                        ->label('')
                                        ->schema([
                                            Grid::make(2)->schema([
                                                TextInput::make('name')
                                                    ->required()
                                                    ->placeholder('Dark blue'),
                                                TextInput::make('hex')
                                                    ->label('Hex value')
                                                    ->required()
                                                    ->placeholder('#1a2a4a'),
                                            ]),
                                            TextInput::make('detail_label')
                                                ->label('Colour detail label')
                                                ->placeholder('raw denim'),
                                        ])
                                        ->addActionLabel('Add colour')
                                        ->reorderable()
                                        ->collapsible()
                                        ->itemLabel(fn (array $state) => $state['name'] ?? 'New colour'),
                                ]),

                                Section::make('Size groups')->schema([
                                    Repeater::make('size_groups')
                                        ->label('')
                                        ->schema([
                                            TextInput::make('label')
                                                ->required()
                                                ->placeholder('Waist'),

                                            Repeater::make('sizes')
                                                ->label('Sizes')
                                                ->schema([
                                                    Grid::make(2)->schema([
                                                        TextInput::make('value')
                                                            ->required()
                                                            ->placeholder('32'),
                                                        Toggle::make('in_stock')
                                                            ->label('In stock')
                                                            ->default(true)
                                                            ->inline(false),
                                                    ]),
                                                ])
                                                ->addActionLabel('Add size')
                                                ->reorderable(),
                                        ])
                                        ->addActionLabel('Add size group')
                                        ->reorderable()
                                        ->collapsible()
                                        ->itemLabel(fn (array $state) => $state['label'] ?? 'New group'),
                                ]),

                                Section::make('Materials')->schema([
                                    Repeater::make('materials')
                                        ->label('')
                                        ->simple(
                                            TextInput::make('value')
                                                ->required()
                                                ->placeholder('100% Cotton')
                                        )
                                        ->addActionLabel('Add material')
                                        ->reorderable(),
                                ]),

                            ]),

                            \Filament\Forms\Components\Group::make()->columnSpan(1)->schema([

                                Section::make('Status')->schema([
                                    Toggle::make('is_published')
                                        ->label('Published')
                                        ->helperText('Visible on storefront'),

                                    Toggle::make('low_in_stock')
                                        ->label('Low in stock')
                                        ->helperText('Shows warning badge on product page'),
                                ]),

                                Section::make('Shipping & returns')->schema([
                                    TextInput::make('shipping_carrier')
                                        ->label('Carrier')
                                        ->placeholder('Australia Post'),

                                    TextInput::make('shipping_note')
                                        ->label('Shipping note')
                                        ->placeholder('FREE'),

                                    TextInput::make('returns_days')
                                        ->label('Returns window')
                                        ->numeric()
                                        ->suffix('days')
                                        ->default(60),
                                ]),

                            ]),
                        ]),
                    ]),

                    // ── TAB 2: Content ───────────────────────────────
                    Tabs\Tab::make('Content')->schema([

                        Section::make('Size & Fit')->schema([
                            Grid::make(2)->schema([
                                TextInput::make('size_fit_tag')
                                    ->label('Tag label')
                                    ->placeholder('Straight fit'),

                                Repeater::make('size_fit_chips')
                                    ->label('Fit chips')
                                    ->simple(
                                        TextInput::make('value')->placeholder('Mid waist')
                                    )
                                    ->addActionLabel('Add chip')
                                    ->reorderable(),
                            ]),

                            RichEditor::make('size_fit_body')
                                ->label('Body')
                                ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link'])
                                ->columnSpanFull(),
                        ]),

                        Section::make('Description & Care')->schema([
                            TextInput::make('description_tag')
                                ->label('Tag label')
                                ->placeholder('Raw denim'),

                            RichEditor::make('description_body')
                                ->label('Description')
                                ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link']),

                            Repeater::make('description_bullets')
                                ->label('Feature bullets')
                                ->simple(
                                    TextInput::make('value')->placeholder('Button closure')
                                )
                                ->addActionLabel('Add bullet')
                                ->reorderable()
                                ->helperText('Rendered as a bullet list below the description.'),

                            TextInput::make('care_instructions')
                                ->label('Care instructions text')
                                ->placeholder('Machine wash 30°C normal process.'),
                        ]),

                        Section::make('Washing instruction symbols')->schema([
                            Repeater::make('washing_instructions')
                                ->label('')
                                ->schema([
                                    Grid::make(2)->schema([
                                        Select::make('symbol')
                                            ->required()
                                            ->options([
                                                'machine_wash_30' => 'Machine wash 30°C',
                                                'no_bleach'       => 'Do not bleach',
                                                'tumble_dry_low'  => 'Tumble dry low heat',
                                                'no_steam_iron'   => 'Do not iron with steam',
                                                'dry_clean'       => 'Dry clean (P)',
                                                'hang_dry'        => 'Hang to dry in shade',
                                                'do_not_wash'     => 'Do not wash',
                                                'iron_low'        => 'Iron low heat',
                                                'iron_medium'     => 'Iron medium heat',
                                                'do_not_tumble'   => 'Do not tumble dry',
                                            ]),

                                        TextInput::make('label')
                                            ->required()
                                            ->placeholder('Machine wash 30°C normal'),
                                    ]),
                                ])
                                ->addActionLabel('Add symbol')
                                ->reorderable()
                                ->maxItems(8)
                                ->helperText('Each symbol renders as an SVG icon with a hover tooltip on the product page.'),
                        ]),
                    ]),

                    // ── TAB 3: Custom sections ────────────────────────
                    Tabs\Tab::make('Custom sections')->schema([
                        Section::make()
                            ->description('Each section renders as an expandable accordion on the product page.')
                            ->schema([
                                Repeater::make('custom_sections')
                                    ->label('')
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->placeholder('e.g. Fabric technology, The Elwood story…'),

                                        TextInput::make('tag_label')
                                            ->label('Tag label (optional)')
                                            ->placeholder('Short badge shown above body'),

                                        RichEditor::make('body')
                                            ->label('Body content')
                                            ->required()
                                            ->toolbarButtons([
                                                'bold', 'italic',
                                                'bulletList', 'orderedList',
                                                'link', 'blockquote',
                                            ])
                                            ->columnSpanFull(),
                                    ])
                                    ->addActionLabel('Add section')
                                    ->reorderable()
                                    ->collapsible()
                                    ->itemLabel(fn (array $state) => $state['title'] ?? 'Untitled section')
                                    ->helperText('Drag to reorder. Sections appear after the fixed accordions.'),
                            ]),
                    ]),

                    // ── TAB 4: Sustainability ─────────────────────────
                    Tabs\Tab::make('Sustainability')->schema([
                        Section::make('Sustainability & Traceability')->schema([
                            Grid::make(2)->schema([
                                TextInput::make('sustainability_tag')
                                    ->label('Tag label')
                                    ->placeholder('Made to last'),

                                TextInput::make('sustainability_message')
                                    ->label('Circularity message')
                                    ->placeholder('Rewear · Repair · Recycle'),
                            ]),

                            Repeater::make('manufacturing_origins')
                                ->label('Manufacturing origins')
                                ->schema([
                                    Grid::make(2)->schema([
                                        TextInput::make('stage')
                                            ->required()
                                            ->placeholder('Garment manufacturing'),
                                        TextInput::make('location')
                                            ->required()
                                            ->placeholder('Genesis Fashions Ltd., Bangladesh'),
                                    ]),
                                ])
                                ->addActionLabel('Add origin')
                                ->reorderable()
                                ->helperText('Renders as a traceability table on the product page.'),
                        ]),
                    ]),

                ]),
        ]);
    }

    // ─────────────────────────────────────────────
    // TABLE
    // ─────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('AUD')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),

                Tables\Columns\IconColumn::make('low_in_stock')
                    ->label('Low stock')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last updated')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')->label('Published'),
                Tables\Filters\TernaryFilter::make('low_in_stock')->label('Low stock'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // ─────────────────────────────────────────────
    // PAGES
    // ─────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
