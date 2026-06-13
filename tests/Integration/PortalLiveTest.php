<?php

use App\Data\Portal\CityData;
use App\Data\Portal\MapMeetupData;
use App\Services\PortalApi;
use App\Services\PortalWriter;
use App\Services\WriteStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Native\Mobile\Facades\SecureStorage;

/**
 * Manuelle Integrationstests gegen ein lokal laufendes Portal-Dev.
 *
 * NICHT Teil der Standard-Suite — Start ausschließlich über die eigene
 * Konfiguration:
 *
 *   composer test:integration
 *   # oder:
 *   vendor/bin/pest -c phpunit.integration.xml
 *
 * Die Schreibtests legen echte Datensätze im lokalen Portal an und laufen nur,
 * wenn ein gültiges Sanctum-Token gesetzt ist:
 *
 *   PORTAL_TEST_TOKEN="<token>" composer test:integration
 *
 * Ist das Portal nicht erreichbar, werden die Tests übersprungen (nicht rot).
 */
function portalBaseUrl(): string
{
    return rtrim((string) config('services.portal.url'), '/');
}

function skipUnlessPortalReachable(): void
{
    $base = portalBaseUrl();
    $host = parse_url($base, PHP_URL_HOST) ?: '127.0.0.1';
    $port = parse_url($base, PHP_URL_PORT) ?: (str_starts_with($base, 'https') ? 443 : 80);

    $socket = @fsockopen($host, (int) $port, $errno, $errstr, 1.0);

    if ($socket === false) {
        test()->markTestSkipped("Portal nicht erreichbar unter {$base} ({$errstr}).");
    }

    fclose($socket);
}

beforeEach(function () {
    skipUnlessPortalReachable();
    // Frische Calls statt Treffer aus einem vorherigen Lauf.
    Cache::flush();
});

it('reads live map meetups and maps them to DTOs', function () {
    withoutPortalToken();

    $meetups = app(PortalApi::class)->mapMeetups(withIntro: true, withLogos: true);

    expect($meetups)->toBeInstanceOf(Collection::class)
        ->and(app(PortalApi::class)->isOffline())->toBeFalse();

    if ($meetups->isNotEmpty()) {
        expect($meetups->first())->toBeInstanceOf(MapMeetupData::class)
            ->and($meetups->first()->name)->toBeString();
    }
})->group('integration');

it('reads live cities with details', function () {
    withoutPortalToken();

    $cities = app(PortalApi::class)->cities(withDetails: true);

    expect($cities)->toBeInstanceOf(Collection::class);

    if ($cities->isNotEmpty()) {
        expect($cities->first())->toBeInstanceOf(CityData::class)
            ->and($cities->first()->country->name)->toBeString();
    }
})->group('integration');

it('creates (idempotent) and updates a meetup against the live portal', function () {
    $token = env('PORTAL_TEST_TOKEN');

    if (blank($token)) {
        test()->markTestSkipped('PORTAL_TEST_TOKEN nicht gesetzt — Schreibtest übersprungen.');
    }

    withPortalToken((string) $token);
    SecureStorage::shouldReceive('set')->andReturnTrue();

    // Idempotent: einen festen Test-Datensatz wiederverwenden statt bei jedem
    // Lauf einen neuen anzulegen (sonst wächst die lokale DB unbegrenzt).
    $name = 'Integrationstest Meetup (mobile)';
    $existing = app(PortalApi::class)->myMeetups()->firstWhere('name', $name);

    if ($existing === null) {
        // Stadt OHNE Suche holen (search nutzt portalseitig ilike → bricht auf
        // einer SQLite-Portal-DB; siehe README der Integrationssuite).
        $city = app(PortalApi::class)->cities(withDetails: true)->first();
        expect($city)->not->toBeNull('Keine Stadt im lokalen Portal vorhanden — bitte seeden.');

        $created = app(PortalWriter::class)->createMeetup([
            'name' => $name,
            'city_id' => $city->id,
            'intro' => 'Automatisch durch den Integrationstest angelegt.',
            'visible_on_map' => false,
            'is_active' => true,
        ]);

        expect($created->status)->toBe(WriteStatus::Success)
            ->and($created->successful())->toBeTrue();

        Cache::flush();
        $existing = app(PortalApi::class)->myMeetups()->firstWhere('name', $name);
    }

    expect($existing)->not->toBeNull('Angelegtes Meetup nicht in my-meetups gefunden.');

    $updated = app(PortalWriter::class)->updateMeetup($existing->id, [
        'intro' => 'Aktualisiert durch den Integrationstest am '.now()->toDateTimeString().'.',
    ]);

    expect($updated->status)->toBe(WriteStatus::Success);
})->group('integration');

it('rejects an invalid create with structured 422 field errors', function () {
    $token = env('PORTAL_TEST_TOKEN');

    if (blank($token)) {
        test()->markTestSkipped('PORTAL_TEST_TOKEN nicht gesetzt — Schreibtest übersprungen.');
    }

    withPortalToken((string) $token);

    // Ohne Name/Stadt muss das Portal mit 422 + Feldfehlern antworten.
    $result = app(PortalWriter::class)->createMeetup([]);

    expect($result->status)->toBe(WriteStatus::ValidationError)
        ->and($result->hasValidationErrors())->toBeTrue()
        ->and($result->errors)->not->toBeEmpty();
})->group('integration');
