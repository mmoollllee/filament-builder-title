<?php

namespace Mmoollllee\FilamentBuilderTitle\Tests;

use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Livewire\LivewireServiceProvider;
use Mmoollllee\FilamentBuilderTitle\FilamentBuilderTitleServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            SupportServiceProvider::class,
            FormsServiceProvider::class,
            FilamentServiceProvider::class,
            FilamentBuilderTitleServiceProvider::class,
        ];
    }
}
