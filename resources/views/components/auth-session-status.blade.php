@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-teal-deep bg-[#E1F5EE] px-3 py-2 rounded-lg']) }}>
        {{ $status }}
    </div>
@endif
