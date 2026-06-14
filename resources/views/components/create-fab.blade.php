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
     * Termin-Kontext: analog das Sheet `create-event` (Phase 5), besessen von
     * <livewire:event-editor>, geöffnet per `open-event-editor`-Event.
     */
    $connected = app(PortalAuth::class)->hasToken();

    $context = match (true) {
        request()->routeIs('events') => [
            'label' => __('Termin anlegen'),
            'modal' => 'create-event',
            'icon' => 'calendar-days',
            'event' => 'open-event-editor',
        ],
        request()->routeIs('meetups', 'meetups.show', 'mine') => [
            'label' => __('Meetup anlegen'),
            'modal' => 'create-meetup',
            'icon' => 'user-group',
            'event' => 'open-meetup-editor',
        ],
        default => null,
    };
@endphp

@if ($connected && $context)
    {{-- Das jeweilige Editor-Sheet (`create-meetup`/`create-event`) besitzt die
         eingebettete Livewire-Komponente im Layout; der FAB triggert es nur und
         setzt es per Event in den Anlegen-Modus. --}}
    <flux:modal.trigger :name="$context['modal']">
        <button
            type="button"
            x-on:click="$haptic('medium'); Livewire.dispatch('{{ $context['event'] }}')"
            aria-label="{{ $context['label'] }}"
            class="fab-enter pressable fixed end-4 bottom-[calc(env(safe-area-inset-bottom)+4.75rem)] z-30 flex h-14 items-center gap-2 rounded-full bg-accent px-5 text-accent-foreground shadow-pop"
        >
            <flux:icon name="plus" class="size-6"/>
            <span class="text-sm font-semibold">{{ $context['label'] }}</span>
        </button>
    </flux:modal.trigger>
@endif
