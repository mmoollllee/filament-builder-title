<?php

namespace Mmoollllee\FilamentBuilderTitle\Tests;

use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use Workbench\App\Livewire\NestedBuilderForm;

class NestedBuilderRenderTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
    }

    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            \BladeUI\Icons\BladeIconsServiceProvider::class,
            \BladeUI\Heroicons\BladeHeroiconsServiceProvider::class,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        View::addLocation(dirname(__DIR__).'/workbench/resources/views');
    }

    public function test_detached_and_nested_block_titles_render_editable_inputs(): void
    {
        $html = Livewire::test(NestedBuilderForm::class)->html();

        // All three blocks render an editable title input (not a plain-text fallback). In this
        // bare Livewire render the top-level "section" and "cta" items are mounted (their
        // container resolves, so the path is bound server-side via $entangle), while the NESTED
        // "text" block is detached and rebuilds its path client-side. Before the fix, the
        // detached block fell back to a plain label.
        $this->assertSame(3, substr_count($html, 'fi-builder-title-input'));

        // The mounted items bind via a server-resolved $entangle path.
        $this->assertStringContainsString('$entangle', $html);

        // The detached (nested) block emits the client-side reconstruction that reads the builder
        // item's own state path from the DOM (`x-sortable-item` + `x-on:builder-expand.window`).
        $this->assertStringContainsString('builder-expand.window', $html);

        // Filament renders every builder item with ITS builder's state path, so the NESTED
        // builder's items carry the deep path `data.content.{section}.data.blocks`. That is
        // exactly what the client-side reconstruction reads, which is why nested titles bind to
        // the correct wire model (the case this test exists to lock in).
        $this->assertMatchesRegularExpression('/data\.content\.[A-Za-z0-9-]+\.data\.blocks/', $html);
    }
}
