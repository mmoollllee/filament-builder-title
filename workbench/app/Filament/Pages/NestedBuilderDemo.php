<?php

namespace Workbench\App\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

/**
 * Exercises the hard case for the ->title() macro: a Builder with block PREVIEWS (blocks
 * render detached — Filament static-renders renderPreview() instead of mounting the schema)
 * AND a NESTED Builder inside a block, so the child blocks live at a deeper state path
 * (`content.{section}.data.blocks.{child}`). The editable title must render and bind to the
 * correct — including nested — wire path in all of these, which is what the tests assert.
 */
class NestedBuilderDemo extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static ?string $title = 'Nested Builder Title Demo';

    protected string $view = 'filament-builder-demo';

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
                            ['type' => 'text', 'data' => ['title' => 'Nested text', 'body' => 'Lorem']],
                            ['type' => 'image', 'data' => ['title' => 'Nested image', 'url' => 'https://example.test/a.jpg']],
                        ],
                    ],
                ],
                ['type' => 'cta', 'data' => ['title' => 'A top-level CTA', 'url' => 'https://example.test']],
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
                        // Parent block WITHOUT a preview → stays mounted, so its NESTED builder
                        // renders and its own title resolves server-side.
                        Block::make('section')
                            ->schema([
                                TextInput::make('title'),
                                Builder::make('blocks')
                                    ->blockPreviews()
                                    ->blocks([
                                        Block::make('text')
                                            ->schema([
                                                TextInput::make('title'),
                                                TextInput::make('body'),
                                            ])
                                            ->title('title', placeholder: 'Text title…', suffix: 'Text')
                                            ->preview('block-preview'),
                                        Block::make('image')
                                            ->schema([
                                                TextInput::make('title'),
                                                TextInput::make('url'),
                                            ])
                                            ->title('title', placeholder: 'Image title…', suffix: 'Image')
                                            ->preview('block-preview'),
                                    ])
                                    ->blockNumbers(false)
                                    ->collapsible(),
                            ])
                            ->title('title', placeholder: 'Section title…', suffix: 'Section'),

                        // Top-level block WITH a preview → detached; title must rebuild its path
                        // client-side from the item DOM.
                        Block::make('cta')
                            ->schema([
                                TextInput::make('title'),
                                TextInput::make('url'),
                            ])
                            ->title('title', placeholder: 'CTA title…', suffix: 'CTA')
                            ->preview('block-preview'),
                    ])
                    ->blockNumbers(false)
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $this->form->getState();
    }
}
