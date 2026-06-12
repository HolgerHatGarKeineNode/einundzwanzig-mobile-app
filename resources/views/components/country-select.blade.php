@props(['countries'])

{{-- Regionsauswahl (Onboarding + Profil): Länder mit Meetups + „Alle Länder“. --}}
<flux:select {{ $attributes }}>
    @foreach ($countries as $option)
        <flux:select.option value="{{ $option['code'] }}">
            {{ \App\Services\CountryOptions::flagEmoji($option['code']) }} {{ $option['name'] }}
        </flux:select.option>
    @endforeach
    <flux:select.option value="">🌍 {{ __('Alle Länder') }}</flux:select.option>
</flux:select>
