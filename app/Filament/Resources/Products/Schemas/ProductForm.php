<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Product')
                ->columnSpanFull()
                ->tabs([

                    // ── TAB 1: General (existing fields) ─────────────
                    Tabs\Tab::make('General')->schema([
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $state, callable $set) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Textarea::make('description')
                            ->columnSpanFull()
                            ->rows(3),

                        Textarea::make('story')
                            ->label('Story / fabric & fit details')
                            ->columnSpanFull()
                            ->rows(4)
                            ->helperText('Shown in the care-tag block on the product page.'),

                        TextInput::make('base_price')
                            ->label('Base price (AUD)')
                            ->required()
                            ->numeric()
                            ->prefix('$'),

                        TextInput::make('sale_price')
                            ->label('Sale price (optional)')
                            ->numeric()
                            ->prefix('$')
                            ->helperText('Leave blank for no sale. Must be lower than the base price to actually show as on sale.')
                            ->lt('base_price'),

                        Select::make('status')
                            ->options([
                                'draft'    => 'Draft',
                                'active'   => 'Active',
                                'archived' => 'Archived',
                            ])
                            ->required()
                            ->default('draft'),
                    ]),

                    // ── TAB 2: Product page ───────────────────────────
                    Tabs\Tab::make('Product page')->schema([
                        Grid::make(2)->schema([
                            TextInput::make('art_number')
                                ->label('Art number')
                                ->placeholder('d23699-d315-001'),

                            TextInput::make('model_note')
                                ->label('Model note')
                                ->placeholder('Model is 1.87m wearing size 32/34'),
                        ]),

                        Grid::make(2)->schema([
                            Toggle::make('low_in_stock')
                                ->label('Low in stock')
                                ->helperText('Shows warning badge on product page'),

                            Grid::make(2)->schema([
                                TextInput::make('shipping_carrier')
                                    ->label('Shipping carrier')
                                    ->placeholder('Australia Post'),

                                TextInput::make('shipping_note')
                                    ->label('Shipping note')
                                    ->placeholder('FREE'),
                            ]),
                        ]),

                        TextInput::make('returns_days')
                            ->label('Returns window (days)')
                            ->numeric()
                            ->default(60)
                            ->suffix('days'),

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
                                ->collapsed()
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
                                ->collapsed()
                                ->itemLabel(fn (array $state) => $state['label'] ?? 'New group'),
                        ]),
                    ]),

                    // ── TAB 3: Content ────────────────────────────────
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
                                ->maxItems(8),
                        ]),
                    ]),

                    // ── TAB 4: Custom sections ────────────────────────
                    Tabs\Tab::make('Custom sections')->schema([
                        Section::make()
                            ->description('Each section renders as an expandable accordion on the product page.')
                            ->schema([
                                Repeater::make('custom_sections')
                                    ->label('')
                                    ->schema([
                                        TextInput::make('title')
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

                    // ── TAB 5: Sustainability ─────────────────────────
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
}
