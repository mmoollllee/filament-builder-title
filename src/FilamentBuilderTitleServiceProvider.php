<?php

namespace Mmoollllee\FilamentBuilderTitle;

use Filament\Forms\Components\Builder\Block;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentBuilderTitleServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-builder-title';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasViews(static::$name);
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            Css::make(static::$name, __DIR__.'/../resources/css/builder-title.css'),
        ], package: 'mmoollllee/filament-builder-title');

        $this->registerBlockTitleMacro();
    }

    protected function registerBlockTitleMacro(): void
    {
        Block::macro('title', function (string $field, ?string $placeholder = null, ?string $suffix = null): static {
            /** @var Block $this */
            $this->meta('titleField', $field);
            $this->meta('titleSuffix', $suffix);

            $this->label(function (?array $state, ?string $key) use ($field, $placeholder, $suffix): string|Htmlable {
                $fallback = (string) str($this->getName())
                    ->kebab()
                    ->replace(['-', '_'], ' ')
                    ->ucfirst();

                $displayPlaceholder = $placeholder ?? $fallback;

                // Block picker context: no state or key available
                if ($state === null || $key === null) {
                    return $fallback;
                }

                // Navigate: Block → BuilderChildSchema → Builder
                $builderStatePath = null;

                if (isset($this->container)) {
                    $builderStatePath = $this->getContainer()
                        ?->getParentComponent()
                        ?->getStatePath();
                }

                $wireModel = $builderStatePath !== null
                    ? "{$builderStatePath}.{$key}.data.{$field}"
                    : null;

                $error = null;
                if ($wireModel !== null) {
                    try {
                        $errorBag = $this->getLivewire()->getErrorBag();
                        $error = $errorBag->first($wireModel) ?: null;
                    } catch (\Throwable) {
                    }
                }

                return new HtmlString(
                    view('filament-builder-title::title-input', [
                        'wireModel' => $wireModel,
                        'field' => $field,
                        'placeholder' => $displayPlaceholder,
                        'suffix' => $suffix,
                        'error' => $error,
                    ])->render()
                );
            });

            return $this;
        });
    }
}
