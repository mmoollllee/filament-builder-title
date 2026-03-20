<?php

namespace Workbench\App\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class BuilderDemo extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $title = 'Builder Title Demo';

    protected string $view = 'filament-builder-demo';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Builder::make('content')
                    ->blocks([
                        Block::make('heading')
                            ->schema([
                                TextInput::make('title')
                                    ->hint('Here a TextInput. Could be a Hidden-Field too.')
                                    ->required(),
                                Select::make('level')
                                    ->options([
                                        'h1' => 'H1',
                                        'h2' => 'H2',
                                        'h3' => 'H3',
                                    ])
                                    ->default('h2'),
                            ])
                            ->title('title', placeholder: 'Enter heading...'),

                        Block::make('call_to_action')
                            ->schema([
                                Hidden::make('button_text')
                                    ->required(),
                                TextInput::make('url')
                                    ->url()
                                    ->required(),
                            ])
                            ->title('button_text', placeholder: 'Button label...', suffix: ' Button'),
                    ])
                    ->reorderable()
                    ->blockNumbers(false)
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
    }
}
