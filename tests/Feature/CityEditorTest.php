<?php

use App\Http\Integrations\Portal\Requests\CreateCityRequest;
use App\Http\Integrations\Portal\Requests\GetCountriesRequest;
use App\Http\Integrations\Portal\Requests\GetMyCitiesRequest;
use App\Http\Integrations\Portal\Requests\UpdateCityRequest;
use Livewire\Livewire;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

afterEach(fn () => MockClient::destroyGlobal());

it('creates a city with country and coordinates and sends the payload', function () {
    withPortalToken();
    MockClient::global([
        CreateCityRequest::class => MockResponse::make(['data' => myCityFixture(['id' => 99])], 201),
    ]);

    Livewire::test('city-editor')
        ->call('open')
        ->set('form.name', 'Musterstadt')
        ->call('selectCountry', 1, 'Germany')
        ->set('form.latitude', 48.21)
        ->set('form.longitude', 16.37)
        ->set('form.population', 120000)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('city-saved', id: 99, name: 'Musterstadt')
        ->assertDispatched('places-changed')
        ->assertSet('editingId', null);

    MockClient::global()->assertSent(fn (Request $request): bool => $request instanceof CreateCityRequest
        && $request->body()->all()['name'] === 'Musterstadt'
        && $request->body()->all()['country_id'] === 1
        && $request->body()->all()['latitude'] === 48.21
        && $request->body()->all()['longitude'] === 16.37
        && $request->body()->all()['population'] === 120000);
});

it('requires a name, country and coordinates before sending', function () {
    withPortalToken();

    Livewire::test('city-editor')
        ->call('open')
        ->call('save')
        ->assertHasErrors([
            'form.name' => 'required',
            'form.country_id' => 'required',
            'form.latitude' => 'required',
            'form.longitude' => 'required',
        ])
        ->assertNotDispatched('places-changed');
});

it('rejects coordinates outside the valid range', function () {
    withPortalToken();

    Livewire::test('city-editor')
        ->call('open')
        ->set('form.name', 'Musterstadt')
        ->call('selectCountry', 1, 'Germany')
        ->set('form.latitude', 200)
        ->set('form.longitude', 16.37)
        ->call('save')
        ->assertHasErrors(['form.latitude' => 'between'])
        ->assertNotDispatched('places-changed');
});

it('adopts a coordinate from the map picker', function () {
    withPortalToken();

    Livewire::test('city-editor')
        ->call('open')
        ->call('setCoordinates', 48.123456789, 16.987654321)
        ->assertSet('form.latitude', 48.123457)
        ->assertSet('form.longitude', 16.987654);
});

it('loads an own city for editing and sends an update', function () {
    withPortalToken();
    MockClient::global([
        GetMyCitiesRequest::class => MockResponse::make(['data' => [myCityFixture(['id' => 80, 'country_id' => 1])]]),
        GetCountriesRequest::class => MockResponse::make([countryFixture(['id' => 1, 'name' => 'Germany'])]),
        UpdateCityRequest::class => MockResponse::make(['data' => myCityFixture(['id' => 80])], 200),
    ]);

    Livewire::test('city-editor')
        ->call('open', 80)
        ->assertSet('editingId', 80)
        ->assertSet('form.name', 'Regensburg')
        ->assertSet('form.countryName', 'Germany')
        ->assertSet('form.latitude', 49.013432)
        ->set('form.name', 'Regensburg (Stadt)')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('places-changed')
        ->assertNotDispatched('city-saved');

    MockClient::global()->assertSent(fn (Request $request): bool => $request instanceof UpdateCityRequest
        && $request->resolveEndpoint() === '/cities/80'
        && $request->body()->all()['name'] === 'Regensburg (Stadt)');
});

it('keeps the editor open and reports a 403 when editing a foreign city', function () {
    withPortalToken();
    MockClient::global([
        GetMyCitiesRequest::class => MockResponse::make(['data' => [myCityFixture(['id' => 80, 'country_id' => 1])]]),
        GetCountriesRequest::class => MockResponse::make([countryFixture(['id' => 1])]),
        UpdateCityRequest::class => MockResponse::make(['message' => 'This action is unauthorized.'], 403),
    ]);

    Livewire::test('city-editor')
        ->call('open', 80)
        ->set('form.name', 'Geändert')
        ->call('save')
        ->assertNotDispatched('places-changed')
        ->assertSet('editingId', 80);
});

it('searches countries for the picker from two characters', function () {
    withPortalToken();
    MockClient::global([
        GetCountriesRequest::class => MockResponse::make([countryFixture(['name' => 'Germany'])]),
    ]);

    Livewire::test('city-editor')
        ->call('open')
        ->set('countryQuery', 'Ger')
        ->assertSee('Germany');
});

it('prefills the name when opened from an inline create flow', function () {
    withPortalToken();

    Livewire::test('city-editor')
        ->call('open', null, 'Neustadt')
        ->assertSet('form.name', 'Neustadt')
        ->assertSet('editingId', null);
});

it('does not send a write without a portal token', function () {
    withoutPortalToken();

    Livewire::test('city-editor')
        ->call('open')
        ->set('form.name', 'Musterstadt')
        ->call('selectCountry', 1, 'Germany')
        ->set('form.latitude', 48.21)
        ->set('form.longitude', 16.37)
        ->call('save')
        ->assertNotDispatched('places-changed');
});
