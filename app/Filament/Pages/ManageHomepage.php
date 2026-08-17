<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class ManageHomepage extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.manage-homepage';

    protected static ?string $navigationLabel = 'Homepage';

    protected static string|\BackedEnum|null $navigationIcon = \Filament\Support\Icons\Heroicon::OutlinedHome;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(SiteSetting::current()->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('hero_image')
                    ->label('Hero image (full-bleed homepage banner)')
                    ->disk('public')
                    ->directory('site')
                    ->image()
                    ->helperText('Recommended: a tall/portrait lifestyle photo — this fills the full-width banner at the top of the homepage.'),

                FileUpload::make('feature_image')
                    ->label('Fabric feature image')
                    ->disk('public')
                    ->directory('site')
                    ->image()
                    ->helperText('Shown next to "Heavyweight. Honest. Built to Last." on the homepage.'),
                FileUpload::make('feature_video')
                    ->label('Feature video (background, muted/looping)')
                    ->disk('public')
                    ->directory('site')
                    ->acceptedFileTypes(['video/mp4', 'video/quicktime'])
                    ->helperText('Short looping clip works best — keep it under ~15-20MB for fast loading.'),

                TextInput::make('video_heading')
                        ->label('Video overlay heading'),

                Textarea::make('video_subtext')
                    ->label('Video overlay subtext')
                    ->rows(2),

                FileUpload::make('manifesto_1_image')
                    ->label('First manifesto section background')
                    ->disk('public')
                    ->directory('site')
                    ->image(),

                FileUpload::make('manifesto_2_image')
                    ->label('Second manifesto section background')
                    ->disk('public')
                    ->directory('site')
                    ->image(),

                FileUpload::make('story_image')
                    ->label('Our Story section background')
                    ->disk('public')
                    ->directory('site')
                    ->image(),
                FileUpload::make('product_page_bg')
                    ->label('Shop page background (subtle atmospheric texture)')
                    ->disk('public')
                    ->directory('site')
                    ->image()
                    ->helperText('Shown very subtly behind product pages — heavily darkened so it doesn\'t compete with the actual product photos.'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        SiteSetting::current()->update($this->form->getState());

        Notification::make()
            ->title('Homepage images updated')
            ->success()
            ->send();
    }
}