@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-paper border-line text-ink placeholder-muted focus:border-teal focus:ring-teal rounded-lg shadow-sm text-sm']) }}>
