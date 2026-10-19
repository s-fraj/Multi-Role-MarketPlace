@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-teal text-start text-base font-medium text-teal-deep bg-[#E1F5EE] focus:outline-none focus:text-teal-deep focus:bg-[#E1F5EE] focus:border-teal-deep transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-muted hover:text-ink hover:bg-paper hover:border-line focus:outline-none focus:text-ink focus:bg-paper focus:border-line transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
