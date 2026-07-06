@php
    // titleInput() is public, so default every optional key to keep external callers from
    // hitting an "undefined variable". The macros always pass wireModel (null for a detached
    // Builder block, whose path is rebuilt client-side) and field.
    $wireModel ??= null;
    $field ??= null;
    $placeholder ??= '';
    $suffix ??= null;
    $error ??= null;
@endphp
<label
    x-data="{
        state: @if ($wireModel) $wire.$entangle('{{ $wireModel }}') @else '' @endif,
        @unless ($wireModel)
        _wirePath: null,
        @endunless
        inputWidth: {{ mb_strlen($placeholder) }},
        resize() {
            const input = this.$refs.input;
            this.inputWidth = (input.value || input.placeholder || '').length;
        },
        @unless ($wireModel)
        // Two-way sync for a detached (preview) block via the reconstructed path (see init()).
        // LIVE (immediate) so editing the header title re-renders the server-side block preview
        // at once — a deferred set leaves the preview stale until the next unrelated request.
        sync() {
            if (this._wirePath) $wire.set(this._wirePath, this.state);
        },
        @endunless
        init() {
            @unless ($wireModel)
            // Detached (preview) block: the schema is not mounted, so the wire path could not
            // be resolved server-side. Rebuild it from the owning builder ITEM in the DOM — its
            // `x-on:builder-expand.window` handler carries that builder's state path (correct
            // even for nested builders) and `x-sortable-item` is the item key. $watch keeps the
            // header reactive to server-side changes to the same field.
            const item = this.$el.closest('[x-sortable-item]');
            if (item) {
                const handler = item.getAttribute('x-on:builder-expand.window') || '';
                const match = handler.match(/'([^']+)'/);
                if (match) {
                    this._wirePath = match[1] + '.' + item.getAttribute('x-sortable-item') + '.data.{{ $field }}';
                    this.state = $wire.get(this._wirePath) ?? '';
                    $wire.$watch(this._wirePath, value => { this.state = value ?? ''; });
                }
            }
            @endunless
            this.$nextTick(() => this.resize());
        },
    }"
    x-effect="state; resize()"
    x-on:click.stop
    class="fi-builder-title-wrapper"
>
    <input
        type="text"
        x-ref="input"
        x-model.lazy="state"
        x-on:input="resize()"
        @unless ($wireModel) x-on:change="sync()" @endunless
        x-on:focus.stop
        x-on:keydown.stop
        :style="'width: ' + inputWidth + 'ch'"
        placeholder="{{ $placeholder }}"
        autocomplete="off"
        role="presentation"
        @class([
            'fi-builder-title-input',
            'fi-builder-title-input-error' => ! empty($error),
        ])
        @if (! empty($error))
            title="{{ $error }}"
        @endif
    />
    @if ($suffix)
        <span class="fi-builder-title-suffix">{{ $suffix }}</span>
    @endif
    @if (! empty($error))
        <span class="fi-builder-title-error">{{ $error }}</span>
    @endif
</label>
