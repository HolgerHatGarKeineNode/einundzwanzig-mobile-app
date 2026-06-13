@php
    use App\Services\PortalAuth;

    /**
     * Kontextsensitiver Create-FAB (Phase 2.1): schwebt über der Bottom-Nav und
     * passt seine Aktion an die aktuelle Seite an. Nur für verbundene Nutzer
     * sichtbar — Schreiben braucht ein Portal-Token.
     *
     * Meetup-Kontext: öffnet das Editor-Sheet `create-meetup` (Phase 4), das
     * von <livewire:meetup-editor> im Layout besessen wird — der FAB triggert
     * es nur und setzt es per `open-meetup-editor`-Event in den Anlegen-Modus.
     * Termin-Kontext: noch ein Platzhalter-Sheet (Editor folgt in Phase 5).
     */
    $connected = app(PortalAuth::class)->hasToken();

    $context = match (true) {
        request()->routeIs('events') => [
            'label' => __('Termin anlegen'),
            'modal' => 'create-event',
            'icon' => 'calendar-days',
            'editor' => false,
            'hint' => __('Der Termin-Editor kommt in Kürze. Dann planst du hier Termine deiner Meetups.'),
        ],
        request()->routeIs('meetups', 'meetups.show', 'mine') => [
            'label' => __('Meetup anlegen'),
            'modal' => 'create-meetup',
            'icon' => 'user-group',
            'editor' => true,
            'hint' => null,
        ],
        default => null,
    };
@endphp

@if ($connected && $context)
    <flux:modal.trigger :name="$context['modal']">
        <button
            type="button"
            @if ($context['editor'])
                x-on:click="$haptic('medium'); Livewire.dispatch('open-meetup-editor')"
            @else
                x-on:click="$haptic('medium')"
            @endif
            aria-label="{{ $context['label'] }}"
            class="fab-enter pressable fixed end-4 bottom-[calc(env(safe-area-inset-bottom)+4.75rem)] z-30 flex h-14 items-center gap-2 rounded-full bg-accent px-5 text-accent-foreground shadow-pop"
        >
            <flux:icon name="plus" class="size-6"/>
            <span class="text-sm font-semibold">{{ $context['label'] }}</span>
        </button>
    </flux:modal.trigger>

    {{-- Platzhalter-Sheet nur für noch nicht gebaute Editoren (Termine, Phase 5).
         Den Meetup-Editor liefert <livewire:meetup-editor>. --}}
    @unless ($context['editor'])
        <x-sheet :name="$context['modal']" :heading="$context['label']">
            <div class="flex flex-col items-center gap-3 py-6 text-center">
                <span class="flex size-14 items-center justify-center rounded-tile bg-brand-500/10 text-brand-600 dark:text-brand-400">
                    <flux:icon :name="$context['icon']" class="size-7"/>
                </span>
                <flux:text class="max-w-xs">{{ $context['hint'] }}</flux:text>
            </div>
        </x-sheet>
    @endunless
@endif
