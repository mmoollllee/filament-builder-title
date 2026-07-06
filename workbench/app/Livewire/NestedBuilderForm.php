<?php

namespace Workbench\App\Livewire;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Livewire\Component;

/**
 * Panel-free host used by the render test: mounts a Builder that has block previews (blocks
 * render DETACHED) and a NESTED Builder inside a block, so the rendered HTML can be asserted
 * without spinning up a full Filament panel page.
 */
class NestedBuilderForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'content' => [
                [
                    'type' => 'section',
                    'data' => [
                        'title' => 'A section',
                        'blocks' => [
                            ['type' => 'text', 'data' => ['title' => 'Nested text']],
                        ],
                    ],
                ],
                ['type' => 'cta', 'data' => ['title' => 'A CTA']],
            ],
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Builder::make('content')
                    ->blockPreviews()
                    ->blocks([
                        Block::make('section')
                            ->schema([
                                TextInput::make('title'),
                                Builder::make('blocks')
                                    ->blockPreviews()
                                    ->blocks([
                                        Block::make('text')
                                            ->schema([TextInput::make('title')])
                                            ->title('title', placeholder: 'Nested title…', suffix: 'Text')
                                            ->preview('block-preview'),
                                    ])
                                    ->blockNumbers(false)
                                    ->collapsible(),
                            ])
                            ->title('title', placeholder: 'Section title…', suffix: 'Section'),

                        Block::make('cta')
                            ->schema([TextInput::make('title')])
                            ->title('title', placeholder: 'CTA title…', suffix: 'CTA')
                            ->preview('block-preview'),
                    ])
                    ->blockNumbers(false)
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    public function render()
    {
        return view('plain-form');
    }

    // Bare (non-Filament-page) render harness: ensure a non-null error bag so Livewire's
    // validation render hook can share it with the form views.
    public function getErrorBag(): \Illuminate\Support\MessageBag
    {
        return new \Illuminate\Support\MessageBag;
    }
}
