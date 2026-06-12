<?php

use App\Services\AppPreferences;
use App\Services\CountryOptions;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::mobile', ['title' => 'Willkommen', 'chrome' => false])] class extends Component {
    public string $locale = AppPreferences::DEFAULT_LOCALE;

    public string $country = AppPreferences::DEFAULT_COUNTRY;

    public function mount(AppPreferences $preferences): void
    {
        if ($preferences->isOnboarded()) {
            $this->redirectRoute('meetups', navigate: true);
        }
    }

    /**
     * @return Collection<int, array{code: string, name: string}>
     */
    #[Computed]
    public function countries(): Collection
    {
        return app(CountryOptions::class)->all();
    }

    public function finish(AppPreferences $preferences, CountryOptions $countryOptions): void
    {
        $this->validate([
            'locale' => ['required', 'in:'.implode(',', AppPreferences::SUPPORTED_LOCALES)],
            'country' => ['in:'.implode(',', $countryOptions->validCodes())],
        ]);

        $preferences->completeOnboarding($this->locale, $this->country);

        $this->redirectRoute('meetups', navigate: true);
    }
};
?>

<div class="pt-safe pb-safe px-safe relative flex min-h-dvh flex-col overflow-hidden">
    <div class="pointer-events-none absolute -top-24 start-1/2 size-72 -translate-x-1/2 rounded-full bg-brand-500/15 blur-3xl"></div>

    <div class="flex flex-1 flex-col justify-center gap-8 p-6">
        <div class="flex flex-col items-center gap-4 text-center">
            <x-brand-logo aria-hidden="true" class="size-20 text-zinc-900 dark:text-zinc-100"/>
            <div>
                <flux:heading size="xl" level="1" class="tracking-wide">EINUNDZWANZIG</flux:heading>
                <flux:text class="mt-2 max-w-xs">
                    {{ __('Meetups, Termine und Kurse der Bitcoin-Community — direkt in deiner Tasche.') }}
                </flux:text>
            </div>
        </div>

        <x-locale-radio-group wire:model="locale"/>

        <flux:field>
            <flux:label>{{ __('Deine Region') }}</flux:label>
            <x-country-select :countries="$this->countries" wire:model="country"/>
            <flux:description>
                {{ __('Meetups und Termine werden zuerst für deine Region angezeigt. Das lässt sich jederzeit im Profil ändern.') }}
            </flux:description>
            <flux:error name="country"/>
        </flux:field>
    </div>

    <div class="p-6 pt-0">
        <flux:button wire:click="finish" variant="primary" class="w-full cursor-pointer">
            {{ __('Los geht’s') }}
        </flux:button>
    </div>
</div>
