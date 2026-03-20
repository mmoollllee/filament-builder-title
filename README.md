# Filament Builder Title

Editable title input directly in Filament Builder block headers. Instead of a static label, users can type a title right in the header bar — no need to open the block.

![Screenshot](docs/screenshot.png)

## Requirements

- PHP 8.2+
- Filament v5

## Installation

```bash
composer require mmoollllee/filament-builder-title
```

## Usage

Add `->title()` to any Builder Block. The referenced field must exist as `Hidden::make()` in the block's schema:

```php
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;

Block::make('section_header')
    ->schema([
        Hidden::make('heading'),
        TextInput::make('subheading'),
    ])
    ->title('heading', placeholder: 'Section Title', suffix: '– Header')
```

This renders an inline text input in the block header that writes directly to the `heading` field.

### Parameters

```php
->title(
    field: 'heading',           // Required – field name to bind to
    placeholder: 'Enter title', // Optional – shown when empty (defaults to humanized block name)
    suffix: '– Header',         // Optional – static text after the input
)
```

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `field` | `string` | — | The field name in the block schema to bind the input to. |
| `placeholder` | `?string` | Block name | Placeholder text when the input is empty. |
| `suffix` | `?string` | `null` | Static label displayed after the input. Clicking it focuses the input. |

### Important

- The `field` must be declared as `Hidden::make('field_name')` in the block's `->schema()`. Using `TextInput::make()->hidden()` will not work — Filament excludes hidden fields from state dehydration.
- `->title()` replaces `->label()`. Do not use both on the same block.

## Features

- **Inline editing** — Title is editable directly in the block header, even when collapsed or with enabled previews
- **Auto-resize** — Input width grows with its content (via `field-sizing: content` if supported)
- **Suffix label/Block Label** — Optional static text after the input (clickable, focuses input)
- **No view override** — Uses Filament's `Htmlable` label support, no Builder Blade override needed
- **Collapse-safe** — Click events are stopped so typing doesn't toggle block collapse

## How It Works

The package registers a `title()` macro on `Filament\Forms\Components\Builder\Block` via Filament's `Macroable` trait.

The macro sets a `->label()` closure that returns an `HtmlString` containing an `<input>` element. The input is bound to Livewire state via Alpine's `$wire.$entangle()`, which provides two-way data binding without triggering extra network requests.

The state path is resolved by navigating the component tree: `Block → getContainer() → getParentComponent() → getStatePath()` to find the parent Builder's state path, then constructing the full path as `{builderStatePath}.{itemKey}.data.{field}`.

## Local Development & Live-Demo

To test the plugin locally with a full Filament panel, the package includes an [Orchestra Testbench](https://packages.tools/testbench) workbench setup.

```bash
# Install dependencies
composer install

# Start the development server (runs migrations, seeds, and publishes assets automatically)
composer serve
```

Open [http://localhost:8000/login](http://localhost:8000/login) and log in with prefilled credentials.

The "Builder Title Demo" page in the sidebar shows a Builder with `->title()` blocks.

### Running Tests

```bash
composer test
```

## Publishing Views

```bash
php artisan vendor:publish --tag=filament-builder-title-views
```

## License

MIT
