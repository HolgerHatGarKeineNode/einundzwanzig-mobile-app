<?php

use App\Data\Portal\CountryData;
use App\Data\Portal\MyCityData;
use App\Livewire\Concerns\HandlesPortalWriteFeedback;
use App\Livewire\Forms\CityForm;
use App\Services\PortalApi;
use App\Services\PortalWriter;
use App\Services\WriteResult;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * City-Editor (Phase 6.2): das Create-/Edit-Formular für Städte. Wie der
 * Meetup-/Termin-/Venue-Editor einmal im Layout eingebettet, besitzt es das
 * Bottom-Sheet `create-city`. Geöffnet über das `open-city-editor`-Event
 * (ohne Argument = Anlegen, mit `name` = Anlegen mit Namensvorschlag aus dem
 * inline-Flow, mit `id` = eigene Stadt bearbeiten).
 *
 * Felder: Name, Land (per Namen gesucht → country_id), Geo-Koordinaten über den
 * Karten-Picker (Phase 6.3) und optional Einwohnerzahl. Schreibt über den
 * {@see PortalWriter}; nach einem Anlegen meldet `city-saved` die neue Stadt an
 * die Editoren (inline-Auswahl), `places-changed` aktualisiert die Hub-Listen.
 *
 * Das `ready`-Gate verhindert, dass der global eingebettete Karten-Picker schon
 * beim Seiten-Render eine Leaflet-Karte aufbaut — er entsteht erst beim Öffnen.
 */
new class extends Component {
    use HandlesPortalWriteFeedback;

    public CityForm $form;

    /** Null = Anlegen, sonst die ID der bearbeiteten eigenen Stadt. */
    public ?int $editingId = null;

    /** Erst nach dem ersten Öffnen true (Karten-Picker erst dann aufbauen). */
    public bool $ready = false;

    /** Suchbegriff für die Land-Auswahl (eigenes Feld, nicht Teil der Payload). */
    public string $countryQuery = '';

    #[On('open-city-editor')]
    public function open(?int $id = null, ?string $name = null): void
    {
        $this->ready = true;
        $this->resetEditor();

        if ($id !== null) {
            $this->loadForEdit($id);

            return;
        }

        if ($name !== null) {
            $this->form->name = $name;
        }
    }

    private function resetEditor(): void
    {
        $this->form->reset();
        $this->editingId = null;
        $this->countryQuery = '';
        $this->resetErrorBag();
    }

    /**
     * Eigene Stadt zum Bearbeiten laden. Den Landesnamen lösen wir über die
     * country_id auf (countries(selected) hebt das 10er-Limit für genau dieses
     * Land auf) — die CityResource liefert nur die id.
     */
    private function loadForEdit(int $id): void
    {
        $city = app(PortalApi::class)
            ->myCities()
            ->first(fn (MyCityData $candidate): bool => $candidate->id === $id);

        if ($city === null) {
            Flux::toast(text: __('Diese Stadt konnte nicht geladen werden.'), variant: 'danger');

            return;
        }

        $countryName = app(PortalApi::class)
            ->countries(selected: [$city->country_id])
            ->first(fn (CountryData $country): bool => $country->id === $city->country_id)
            ?->name ?? '';

        $this->editingId = $city->id;
        $this->form->setCity($city, $countryName);
    }

    /**
     * Länder-Treffer für die Auswahl (ab 2 Zeichen, debounced).
     *
     * @return Collection<int, CountryData>
     */
    #[Computed]
    public function countryResults(): Collection
    {
        $query = trim($this->countryQuery);

        if (mb_strlen($query) < 2) {
            return collect();
        }

        return app(PortalApi::class)
            ->countries($query)
            ->take(8)
            ->values();
    }

    public function selectCountry(int $id, string $name): void
    {
        $this->form->country_id = $id;
        $this->form->countryName = $name;
        $this->countryQuery = '';
        $this->resetErrorBag('form.country_id');
        unset($this->countryResults);
    }

    public function clearCountry(): void
    {
        $this->form->country_id = null;
        $this->form->countryName = '';
    }

    /**
     * Vom Karten-Picker (Phase 6.3) gesetzte Koordinate übernehmen.
     */
    public function setCoordinates(float $latitude, float $longitude): void
    {
        $this->form->latitude = round($latitude, 6);
        $this->form->longitude = round($longitude, 6);
        $this->resetErrorBag(['form.latitude', 'form.longitude']);
    }

    public function save(): void
    {
        $payload = $this->form->payload();

        $writer = app(PortalWriter::class);

        $result = $this->editingId === null
            ? $writer->createCity($payload)
            : $writer->updateCity($this->editingId, $payload);

        if ($result->successful()) {
            $this->handleSuccess($result);

            return;
        }

        $this->reportWriteFailure($result, __('Du darfst diese Stadt nicht bearbeiten.'));
    }

    private function handleSuccess(WriteResult $result): void
    {
        $created = $this->editingId === null;

        Flux::modal('create-city')->close();
        Flux::toast(
            text: $created ? __('Stadt angelegt.') : __('Stadt aktualisiert.'),
            variant: 'success',
        );

        // Beim Anlegen die neue Stadt an die offenen Editoren melden (inline-
        // Auswahl, Phase 6.2); die Hub-Listen lauschen separat auf places-changed.
        if ($created) {
            $newId = $result->data['data']['id'] ?? null;

            if (is_int($newId)) {
                $this->dispatch('city-saved', id: $newId, name: $this->form->name);
            }
        }

        $this->dispatch('places-changed');
        $this->js("window.haptic && window.haptic('success')");
        $this->resetEditor();
    }
};
?>

<x-sheet name="create-city" :heading="$ready ? ($editingId ? __('Stadt bearbeiten') : __('Stadt anlegen')) : ''">
    @if (! $ready)
        {{-- Vor dem ersten Öffnen kein Karten-Aufbau (global im Layout eingebettet). --}}
    @else
        <form wire:submit="save" class="flex flex-col gap-5">
            <flux:input
                wire:model="form.name"
                :label="__('Name')"
                :placeholder="__('z. B. Musterstadt')"
                required
            />

            {{-- Land: gewähltes Land als Chip, sonst Suche. --}}
            <div class="flex flex-col gap-2">
                <flux:label>{{ __('Land') }}</flux:label>

                @if ($form->country_id)
                    <div class="flex items-center justify-between gap-3 rounded-tile border border-zinc-200 px-4 py-3 dark:border-zinc-800">
                        <span class="flex min-w-0 items-center gap-2">
                            <flux:icon name="flag" class="size-5 shrink-0 text-brand-600 dark:text-brand-400"/>
                            <span class="truncate font-semibold">{{ $form->countryName !== '' ? $form->countryName : __('Land gewählt') }}</span>
                        </span>
                        <flux:button wire:click="clearCountry" type="button" size="xs" variant="ghost" icon="x-mark" :aria-label="__('Land ändern')" class="cursor-pointer"/>
                    </div>
                @else
                    <flux:input
                        wire:model.live.debounce.300ms="countryQuery"
                        type="search"
                        icon="magnifying-glass"
                        :placeholder="__('Land suchen …')"
                    />

                    @error('form.country_id')
                        <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                    @enderror

                    @if ($this->countryResults->isNotEmpty())
                        <div class="flex flex-col gap-1 rounded-tile border border-zinc-200 p-1 dark:border-zinc-800">
                            @foreach ($this->countryResults as $country)
                                <button
                                    type="button"
                                    wire:click="selectCountry({{ $country->id }}, @js($country->name))"
                                    x-on:click="$haptic('medium')"
                                    wire:key="country-{{ $country->id }}"
                                    class="pressable flex items-center gap-2 rounded-md px-3 py-2 text-start active:bg-zinc-100 dark:active:bg-zinc-800"
                                >
                                    <flux:icon name="flag" class="size-4 shrink-0 text-zinc-400"/>
                                    <span class="truncate text-sm font-medium">{{ $country->name }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>

            {{-- Geo-Koordinaten: Karten-Picker (Tap/Drag) + manuelle Felder als Fallback. --}}
            <div class="flex flex-col gap-2">
                <flux:label>{{ __('Standort') }}</flux:label>
                <flux:text class="text-sm">{{ __('Tippe auf die Karte, um den Standort zu setzen.') }}</flux:text>

                <x-map-picker :latitude="$form->latitude" :longitude="$form->longitude"/>

                <div class="flex gap-3">
                    <div class="flex flex-1 flex-col gap-1">
                        <flux:input wire:model="form.latitude" type="number" step="any" :label="__('Breitengrad')" :placeholder="__('z. B. 48.21')"/>
                        @error('form.latitude')
                            <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                        @enderror
                    </div>
                    <div class="flex flex-1 flex-col gap-1">
                        <flux:input wire:model="form.longitude" type="number" step="any" :label="__('Längengrad')" :placeholder="__('z. B. 16.37')"/>
                        @error('form.longitude')
                            <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                        @enderror
                    </div>
                </div>
            </div>

            <flux:input
                wire:model="form.population"
                type="number"
                :label="__('Einwohner (optional)')"
                :placeholder="__('z. B. 120000')"
            />

            <div class="flex gap-2 pt-1">
                <flux:spacer/>
                <flux:modal.close>
                    <flux:button type="button" variant="ghost" class="cursor-pointer">{{ __('Abbrechen') }}</flux:button>
                </flux:modal.close>
                <flux:button
                    type="submit"
                    variant="primary"
                    icon="check"
                    x-on:click="$haptic('medium')"
                    class="cursor-pointer"
                    wire:loading.attr="disabled"
                    wire:target="save"
                >
                    {{ $editingId ? __('Speichern') : __('Anlegen') }}
                </flux:button>
            </div>
        </form>
    @endif
</x-sheet>
