<?php

namespace Mmoollllee\FilamentBuilderTitle;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
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
        $this->registerRepeaterTitleMacro();
    }

    /**
     * Render the inline title <input> that stands in for a Builder block / Repeater row
     * header label. When the wire path is known server-side (`wireModel`) it is two-way bound
     * via Alpine `$entangle`; when it is null (a detached Builder block — see the macro), the
     * view reconstructs the path from the builder item's DOM and syncs via `$wire.get/set`.
     *
     * @param  array<string, mixed>  $data
     */
    public static function titleInput(array $data): HtmlString
    {
        return new HtmlString(
            view('filament-builder-title::title-input', $data)->render()
        );
    }

    /**
     * Look up the first validation error for a wire path off the given component's error bag,
     * swallowing any error-resolution failure. Shared by both title macros.
     */
    public static function firstError(object $component, string $wireModel): ?string
    {
        try {
            return $component->getLivewire()->getErrorBag()->first($wireModel) ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function registerBlockTitleMacro(): void
    {
        Block::macro('title', function (string $field, ?string $placeholder = null, ?string $suffix = null) {
            /** @var Block $this */
            $this->label(function (?array $state, ?string $key) use ($field, $placeholder, $suffix): string|Htmlable {
                $fallback = (string) str($this->getName())
                    ->kebab()
                    ->replace(['-', '_'], ' ')
                    ->ucfirst();

                // Block picker context: no per-item state/key available → plain label.
                if ($state === null || $key === null) {
                    return $fallback;
                }

                // Resolve the wire path server-side when the block is MOUNTED (Block →
                // BuilderChildSchema → Builder). A block that renders a preview is DETACHED —
                // Filament static-renders it via renderPreview() without mounting the schema,
                // so `$this->container` is uninitialized. Guard isset() because the typed
                // property throws "must not be accessed before initialization" on access (the
                // ?-> chain does not catch that). In the detached case we pass a null wire
                // model and the view rebuilds the path client-side from the builder item's DOM,
                // so the editable input still renders (incl. inside nested builders).
                $wireModel = null;

                if (isset($this->container)) {
                    $builderStatePath = $this->getContainer()
                        ?->getParentComponent()
                        ?->getStatePath();

                    if ($builderStatePath !== null) {
                        $wireModel = "{$builderStatePath}.{$key}.data.{$field}";
                    }
                }

                $error = $wireModel === null ? null : FilamentBuilderTitleServiceProvider::firstError($this, $wireModel);

                return FilamentBuilderTitleServiceProvider::titleInput([
                    'wireModel' => $wireModel,
                    'field' => $field,
                    'placeholder' => $placeholder ?? $fallback,
                    'suffix' => $suffix,
                    'error' => $error,
                ]);
            });

            return $this;
        });
    }

    /**
     * Repeater equivalent of the Block `title()` macro: renders an inline, editable input
     * for one of the row's fields directly in the row header (in place of the read-only
     * `itemLabel`). Bind the field with `Hidden::make($field)` in the schema so it is edited
     * only in the header. Repeater rows are flat, so the wire path is `{row}.{field}` (no
     * `.data.` wrapper the Builder needs). Repeater rows are always mounted, so the path is
     * resolved server-side here.
     */
    protected function registerRepeaterTitleMacro(): void
    {
        Repeater::macro('title', function (string $field, ?string $placeholder = null, ?string $suffix = null) {
            /** @var Repeater $this */
            $this->itemLabel(function ($container) use ($field, $placeholder, $suffix): string|Htmlable|null {
                // $container is the row's child schema; its state path is
                // "{repeaterStatePath}.{uuid}", so the field lives one level below.
                $rowStatePath = $container?->getStatePath();

                if (blank($rowStatePath)) {
                    return null;
                }

                $wireModel = "{$rowStatePath}.{$field}";

                $error = FilamentBuilderTitleServiceProvider::firstError($container, $wireModel);

                // Humanize the field name the same way the Block macro derives its fallback,
                // so both macros default the placeholder to the same casing.
                $fallback = (string) str($field)
                    ->kebab()
                    ->replace(['-', '_'], ' ')
                    ->ucfirst();

                return FilamentBuilderTitleServiceProvider::titleInput([
                    'wireModel' => $wireModel,
                    'field' => $field,
                    'placeholder' => $placeholder ?? $fallback,
                    'suffix' => $suffix,
                    'error' => $error,
                ]);
            });

            return $this;
        });
    }
}
