<?php

namespace Mmoollllee\FilamentBuilderTitle\Tests;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\HtmlString;

class BlockTitleMacroTest extends TestCase
{
    public function test_block_has_title_macro(): void
    {
        $this->assertTrue(Block::hasMacro('title'));
    }

    public function test_title_macro_returns_block_instance(): void
    {
        $block = Block::make('test')
            ->schema([TextInput::make('heading')])
            ->title('heading');

        $this->assertInstanceOf(Block::class, $block);
    }

    public function test_label_returns_fallback_without_state(): void
    {
        $block = Block::make('hero_section')
            ->schema([TextInput::make('heading')])
            ->title('heading');

        $label = $block->getLabel(null, null);

        $this->assertSame('Hero section', $label);
    }

    /**
     * A block used with ->preview() renders DETACHED — Filament static-renders the preview
     * instead of mounting the block schema, so the label closure runs with no bound container
     * and cannot resolve the wire path server-side. The title must STILL render an editable
     * input, rebuilding its wire path client-side from the builder item's DOM. Regression
     * guard: v0.2.0 dropped this and only a plain-text fallback rendered for preview blocks.
     */
    public function test_label_renders_editable_input_for_detached_preview_block(): void
    {
        // A freshly-made block has no container bound — the same detached state a preview
        // block is in when its header label is rendered.
        $block = Block::make('text')
            ->schema([TextInput::make('title')])
            ->title('title', placeholder: 'Enter title', suffix: 'Text');

        $label = $block->getLabel(['title' => 'Hello'], 'item-uuid');

        // The editable input is an HtmlString; the plain-text fallback would be a string.
        $this->assertInstanceOf(HtmlString::class, $label);

        $html = (string) $label;

        $this->assertStringContainsString('fi-builder-title-input', $html);
        $this->assertStringContainsString('placeholder="Enter title"', $html);

        // Client-side reconstruction: reads the item's own builder state path + item key from
        // the DOM and builds the Builder-shaped path (`{statePath}.{itemKey}.data.{field}`) —
        // this is what makes it correct for nested builders too.
        $this->assertStringContainsString('x-sortable-item', $html);
        $this->assertStringContainsString('builder-expand.window', $html);
        $this->assertStringContainsString(".data.title", $html);

        // No server-resolved entangle path is emitted for the detached case.
        $this->assertStringNotContainsString('$entangle(', $html);
    }
}
