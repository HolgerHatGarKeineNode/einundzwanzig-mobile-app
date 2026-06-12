<?php

use App\Http\Integrations\Portal\Requests\GetCountriesRequest;
use App\Http\Integrations\Portal\Requests\GetMapMeetupsRequest;
use App\Services\AppPreferences;
use Livewire\Livewire;
use Native\Mobile\Facades\SecureStorage;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

afterEach(fn () => MockClient::destroyGlobal());

it('redirects to the onboarding until it is completed', function () {
    resetOnboarding();

    $this->get(route('home'))->assertRedirect(route('onboarding'));
    $this->get(route('meetups'))->assertRedirect(route('onboarding'));
    $this->get(route('profile'))->assertRedirect(route('onboarding'));
});

it('keeps the deep-link auth callbacks outside the onboarding gate', function () {
    resetOnboarding();
    SecureStorage::shouldReceive('set')
        ->once()
        ->with('portal_api_token', '12|secrettoken')
        ->andReturnTrue();

    // Niemals zum Onboarding umleiten — sonst ginge der Token verloren.
    $this->get('/auth?token='.urlencode('12|secrettoken'))
        ->assertRedirect(route('profile'));
});

it('renders the onboarding with german preselected and a region picker', function () {
    resetOnboarding();
    withoutPortalToken();
    MockClient::global([
        GetMapMeetupsRequest::class => MockResponse::make([
            mapMeetupFixture(),
            mapMeetupFixture(['name' => 'Einundzwanzig Wien', 'country' => 'AT', 'city' => 'Wien']),
        ]),
        GetCountriesRequest::class => MockResponse::make([
            ['id' => 1, 'name' => 'Deutschland', 'code' => 'de', 'flag' => 'https://example.test/de.svg'],
            ['id' => 2, 'name' => 'Österreich', 'code' => 'at', 'flag' => 'https://example.test/at.svg'],
        ]),
    ]);

    $this->get(route('onboarding'))
        ->assertOk()
        ->assertSee(__('Sprache'))
        ->assertSee(__('Deutsch'))
        ->assertSee(__('Deine Region'))
        ->assertSee('Deutschland')
        ->assertSee('Österreich')
        ->assertSee(__('Alle Länder'))
        ->assertSee(__('Los geht’s'));
});

it('offers only countries that actually have meetups, with a dach fallback when offline', function () {
    resetOnboarding();
    withoutPortalToken();
    MockClient::global([
        GetMapMeetupsRequest::class => MockResponse::make([], 500),
    ]);

    $this->get(route('onboarding'))
        ->assertOk()
        ->assertSee('Deutschland')
        ->assertSee('Österreich')
        ->assertSee('Schweiz');
});

it('stores the selection and redirects to the start page when finishing', function () {
    resetOnboarding();
    withoutPortalToken();
    MockClient::global([
        GetMapMeetupsRequest::class => MockResponse::make([]),
    ]);

    Livewire::test('pages::onboarding.index')
        ->assertSet('locale', 'de')
        ->assertSet('country', 'de')
        ->set('country', 'at')
        ->call('finish')
        ->assertRedirect(route('meetups'));

    $preferences = app(AppPreferences::class);

    expect($preferences->isOnboarded())->toBeTrue()
        ->and($preferences->locale())->toBe('de')
        ->and($preferences->country())->toBe('at');
});

it('rejects an unknown region', function () {
    resetOnboarding();
    withoutPortalToken();
    MockClient::global([
        GetMapMeetupsRequest::class => MockResponse::make([]),
    ]);

    Livewire::test('pages::onboarding.index')
        ->set('country', 'xx')
        ->call('finish')
        ->assertHasErrors(['country']);

    expect(app(AppPreferences::class)->isOnboarded())->toBeFalse();
});

it('redirects onboarded users away from the onboarding', function () {
    withoutPortalToken();
    MockClient::global([
        GetMapMeetupsRequest::class => MockResponse::make([]),
    ]);

    $this->get(route('onboarding'))->assertRedirect(route('meetups'));
});
