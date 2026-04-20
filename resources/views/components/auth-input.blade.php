@props([
    'id',
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'autocomplete' => null,
    'placeholder' => null,
    'required' => false,
    'autofocus' => false,
    'error' => null,
])

<div class="mb-3">
    <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    <input id="{{ $id }}" type="{{ $type }}" name="{{ $name }}"
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($required) required @endif @if ($autofocus) autofocus @endif
        value="{{ old($name, $value) }}" {{ $attributes->class(['form-control', 'is-invalid' => $error]) }}>

    @if ($error)
        <div class="invalid-feedback">{{ $error }}</div>
    @endif
</div>
