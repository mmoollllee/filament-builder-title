<?php

namespace Mmoollllee\FilamentBuilderTitle\Tests;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;

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
}
