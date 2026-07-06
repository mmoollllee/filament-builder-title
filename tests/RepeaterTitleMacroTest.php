<?php

namespace Mmoollllee\FilamentBuilderTitle\Tests;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\HtmlString;
use Illuminate\Support\MessageBag;
use ReflectionProperty;

class RepeaterTitleMacroTest extends TestCase
{
    public function test_repeater_has_title_macro(): void
    {
        $this->assertTrue(Repeater::hasMacro('title'));
    }

    public function test_title_macro_returns_repeater_instance(): void
    {
        $repeater = Repeater::make('specs')
            ->schema([TextInput::make('beschriftung')])
            ->title('beschriftung');

        $this->assertInstanceOf(Repeater::class, $repeater);
    }

    public function test_item_label_renders_editable_input_bound_to_flat_row_path(): void
    {
        $repeater = Repeater::make('specs')
            ->schema([Hidden::make('beschriftung')])
            ->title('beschriftung', placeholder: 'Bezeichnung');

        // The macro installs an itemLabel closure. Invoke it with a fake row container
        // (state path "{repeater}.{uuid}") to inspect the rendered header input.
        $property = new ReflectionProperty(Repeater::class, 'itemLabel');
        $itemLabel = $property->getValue($repeater);

        $container = new class
        {
            public function getStatePath(): string
            {
                return 'data.specs.abc-123';
            }

            public function getLivewire(): object
            {
                return new class
                {
                    public function getErrorBag(): MessageBag
                    {
                        return new MessageBag;
                    }
                };
            }
        };

        $label = $itemLabel($container);

        $this->assertInstanceOf(HtmlString::class, $label);

        $html = (string) $label;

        $this->assertStringContainsString('fi-builder-title-wrapper', $html);
        $this->assertStringContainsString('placeholder="Bezeichnung"', $html);

        // Two-way bound to the FLAT repeater-row path "{repeater}.{uuid}.{field}",
        // NOT the Builder's "{...}.{uuid}.data.{field}" shape.
        $this->assertStringContainsString("\$entangle('data.specs.abc-123.beschriftung')", $html);
        $this->assertStringNotContainsString('.data.beschriftung', $html);
    }

    public function test_item_label_is_null_when_row_has_no_state_path(): void
    {
        $repeater = Repeater::make('specs')
            ->schema([Hidden::make('beschriftung')])
            ->title('beschriftung');

        $property = new ReflectionProperty(Repeater::class, 'itemLabel');
        $itemLabel = $property->getValue($repeater);

        $container = new class
        {
            public function getStatePath(): string
            {
                return '';
            }
        };

        $this->assertNull($itemLabel($container));
    }
}
