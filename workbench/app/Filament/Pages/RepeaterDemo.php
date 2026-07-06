<?php

namespace Workbench\App\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class RepeaterDemo extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $title = 'Repeater Title Demo';

    protected string $view = 'filament-builder-demo';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'specs' => [
                ['beschriftung' => 'Working height', 'wert' => '8', 'einheit' => 'm'],
                ['beschriftung' => 'Weight', 'wert' => '1200', 'einheit' => 'kg'],
            ],
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Repeater::make('specs')
                    ->schema([
                        Hidden::make('beschriftung'),
                        TextInput::make('wert')->label('Value'),
                        TextInput::make('einheit')->label('Unit'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->title('beschriftung', placeholder: 'Row label...'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $this->form->getState();
    }
}
