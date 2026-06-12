<?php

namespace App\Livewire;

use App\Services\AppPreferences;
use Illuminate\Support\Str;
use Livewire\Component;
use Native\Mobile\Facades\Browser;

/**
 * Basisklasse der Portal-Modulseiten (SFC unter resources/views/pages/):
 * bündelt Actions, die alle Module teilen.
 */
abstract class PortalPage extends Component
{
    /**
     * Startwert für die `$country`-Url-Property der Seite: ein expliziter
     * country-Query-Param gewinnt (geteilte/gespeicherte Links mit Filter),
     * sonst gilt die Onboarding-Region.
     */
    protected function defaultCountry(): string
    {
        if (request()->query->has('country')) {
            return (string) request()->query('country');
        }

        return app(AppPreferences::class)->country();
    }

    /**
     * Externe Links im System-Browser öffnen, damit z. B. Telegram-Links
     * direkt in der passenden App landen. Nur http(s) wird geöffnet — die
     * URLs stammen aus Portal-Daten, andere Schemes (nostrsigner:,
     * intent: …) wären Intent-Injection.
     */
    public function openLink(string $url): void
    {
        if (Str::startsWith($url, ['https://', 'http://'])) {
            Browser::open($url);
        }
    }
}
