{{-- Minimal block preview used by the workbench nested-builder demo to force blocks to
     render DETACHED (Filament static-renders the preview instead of mounting the schema),
     which is the case the editable title has to keep working for. --}}
<div class="fbt-block-preview">
    {{ $title ?? $body ?? 'Preview' }}
</div>
