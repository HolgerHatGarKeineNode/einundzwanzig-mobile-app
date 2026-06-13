@php
    use App\Services\PortalAuth;

    /**
     * Kontextsensitiver Create-FAB (Phase 2.1): schwebt über der Bottom-Nav und
     * passt seine Aktion an die aktuelle Seite an. Nur für verbundene Nutzer
     * sichtbar — Schreiben braucht ein Portal-Token. Der Button öffnet das
     * passende Create-Sheet, das hier kontextgebunden mitgerendert wird (das
     * Formular folgt in Phase 4 für Meetups bzw. Phase 5 für Termine).
     */
    $connected = app(PortalAuth::class)->hasToken();

    $context = match (true) {
        request()->routeIs('events') => [
            'label' => __('Termin anlegen'),
            'modal' => 'create-event',
            'icon' => 'calendar-days',
            'hint' => __('Der Termin-Editor kommt in Kürze. Dann planst du hier Termine deiner Meetups.'),
        ],
        request()->routeIs('meetups', 'meetups.show', 'mine') => [
            'label' => __('Meetup anlegen'),
            'modal' => 'create-meetup',
            'icon' => 'user-group',
            'hint' => __('Der Meetup-Editor kommt in Kürze. Dann legst du hier dein eigenes Meetup an.'),
        ],
        default => null,
    };
@endphp

@if ($connected && $context)
    <flux:modal.trigger :name="$context['modal']">
        <button
            type="button"
            x-on:click="$haptic('medium')"
            aria-label="{{ $context['label'] }}"
            class="fab-enter pressable fixed end-4 bottom-[calc(env(safe-area-inset-bottom)+4.75rem)] z-30 flex h-14 items-center gap-2 rounded-full bg-accent px-5 text-accent-foreground shadow-pop"
        >
            <flux:icon name="plus" class="size-6"/>
            <span class="text-sm font-semibold">{{ $context['label'] }}</span>
        </button>
    </flux:modal.trigger>

    <x-sheet :name="$context['modal']" :heading="$context['label']">
        <div class="flex flex-col items-center gap-3 py-6 text-center">
            <span class="flex size-14 items-center justify-center rounded-tile bg-brand-500/10 text-brand-600 dark:text-brand-400">
                <flux:icon :name="$context['icon']" class="size-7"/>
            </span>
            <flux:text class="max-w-xs">{{ $context['hint'] }}</flux:text>
        </div>
    </x-sheet>
@endif
