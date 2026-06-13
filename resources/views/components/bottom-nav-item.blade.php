@props([
    'route',
    'icon',
    'label',
    'match' => null,
])

@php
    $active = request()->routeIs(...explode(',', $match ?? $route));
@endphp

<a
    href="{{ route($route) }}"
    wire:navigate
    x-on:click="$haptic('light')"
    @if($active) aria-current="page" @endif
    {{ $attributes->class([
        'pressable relative flex flex-col items-center justify-center gap-1 py-2.5',
        'text-accent' => $active,
        'text-zinc-500 active:text-zinc-700 dark:text-zinc-400 dark:active:text-zinc-200' => ! $active,
    ]) }}
>
    @if ($active)
        {{-- Animierter Aktiv-Indikator (Phase 2.2): Pill am oberen Tab-Rand. --}}
        <span class="nav-pill absolute inset-x-0 top-0 mx-auto h-1 w-8 rounded-full bg-accent" aria-hidden="true"></span>
    @endif
    <flux:icon :name="$icon" :variant="$active ? 'solid' : 'outline'" class="size-6"/>
    <span class="text-[11px] font-semibold leading-none">{{ $label }}</span>
</a>
