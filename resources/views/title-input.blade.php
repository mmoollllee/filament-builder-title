<label
    x-data="{
        state: @if ($wireModel) $wire.$entangle('{{ $wireModel }}') @else '' @endif,
        _wirePath: null,
        inputWidth: {{ mb_strlen($placeholder)}},
        resize() {
            const input = this.$refs.input;
            this.inputWidth = (input.value || input.placeholder || '').length;
        },
        sync() {
            if (this._wirePath) $wire.set(this._wirePath, this.state);
        },
        @unless ($wireModel)
        init() {
            const item = this.$el.closest('[x-sortable-item]');
            if (! item) return;
            const handler = item.getAttribute('x-on:builder-expand.window') || '';
            const match = handler.match(/'([^']+)'/);
            if (! match) return;
            this._wirePath = match[1] + '.' + item.getAttribute('x-sortable-item') + '.data.{{ $field }}';
            this.state = $wire.get(this._wirePath) ?? '';
            this.$nextTick(() => this.resize());
        },
        @endunless
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
        name="fi-builder-title-{{ uniqid() }}"
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
