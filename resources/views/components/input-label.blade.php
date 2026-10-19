@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-ink mb-1']) }}>
    {{ $value ?? $slot }}
</label>
