@props([
    'name' => 'switch',
    'id' => null,
    'label' => '',
    'checked' => false,
    'divClass' => 'mb-3',
])

<div class="single-input mt-3">
    @if ($label)
        <label for="{{ $id ?? $name }}" class="label-title">{{ $label }}</label>
    @endif
    <div class="form-check form-switch px-0">
        <input 
            class="d-none form-check-input styled-switch" 
            type="checkbox" 
            role="switch" 
            name="{{ $name }}" 
            id="{{ $id ?? $name }}"
            {{ $checked ? 'checked' : '' }} 
            value="1">
        <label class="form-check-label toggle-label" for="{{ $id ?? $name }}">
            {{ $slot }}
            <span class="custom-switch-new knob"></span>
        </label>
    </div>
</div>
