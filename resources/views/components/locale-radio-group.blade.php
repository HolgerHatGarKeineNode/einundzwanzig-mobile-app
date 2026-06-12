{{-- Sprach-Auswahl (Onboarding + Profil): v1 nur Deutsch, en folgt mit 7.4. --}}
<flux:radio.group {{ $attributes }} :label="__('Sprache')" variant="segmented">
    <flux:radio value="de" :label="__('Deutsch')"/>
    <flux:radio value="en" label="{{ __('English') }} · {{ __('bald') }}" disabled/>
</flux:radio.group>
