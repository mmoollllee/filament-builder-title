<label
    x-data="{
        state: $wire.$entangle('{{ $wireModel }}'),
        inputWidth: {{ mb_strlen($placeholder) }},
        // Width fallback for browsers without `field-sizing: content` (Safari < 17.4).
        // Modern browsers ignore this via the @supports rule in builder-title.css.
        init() {
            this.$nextTick(() => this.resize());
        },
        resize() {
            const input = this.$refs.input;
            this.inputWidth = (input.value || input.placeholder || '').length;
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
