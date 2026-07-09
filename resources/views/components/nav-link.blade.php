@props(['active' => false])

@php
    $classes = $active
        ? 'inline-flex items-center rounded-full bg-brand-50 px-4 py-2 text-brand-700'
        : 'inline-flex items-center rounded-full px-4 py-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900';
@endphp

<a {{ $attributes->class($classes) }}>
    {{ $slot }}
</a>

