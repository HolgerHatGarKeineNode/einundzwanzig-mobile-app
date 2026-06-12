{{-- Verzeichnis-Karte für Städte/Orte: Flagge, Name, optionaler Untertitel (nicht klickbar). --}}
@props([
    'flag' => null,
    'name',
    'subtitle' => null,
])

<div {{ $attributes->class('flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900') }}>
    @if (is_string($flag))
        <img src="{{ $flag }}" alt="" class="h-4 w-6 shrink-0 rounded-[2px] object-cover"/>
    @endif
    <span class="flex min-w-0 flex-col gap-0.5">
        <span class="truncate font-semibold">{{ $name }}</span>
        @if ($subtitle)
            <flux:text class="truncate text-sm">{{ $subtitle }}</flux:text>
        @endif
    </span>
</div>
