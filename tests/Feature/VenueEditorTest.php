<?php

use App\Http\Integrations\Portal\Requests\CreateVenueRequest;
use App\Http\Integrations\Portal\Requests\GetCitiesRequest;
use App\Http\Integrations\Portal\Requests\GetMyVenuesRequest;
use App\Http\Integrations\Portal\Requests\UpdateVenueRequest;
use Livewire\Livewire;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

afterEach(fn () => MockClient::destroyGlobal());

it('creates a venue with the selected city and sends the payload', function () {
    withPortalToken();
    MockClient::global([
        CreateVenueRequest::class => MockResponse::make(['data' => myVenueFixture(['id' => 99])], 201),
    ]);

    Livewire::test('venue-editor')
        ->call('open')
        ->set('form.name', 'Bitcoin-Bar')
        ->set('form.street', 'Musterstraße 21')
        ->call('selectCity', 80, 'Regensburg')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('places-changed')
        ->assertSet('editingId', null);

    MockClient::global()->assertSent(fn (Request $request): bool => $request instanceof CreateVenueRequest
        && $request->body()->all()['name'] === 'Bitcoin-Bar'
        && $request->body()->all()['street'] === 'Musterstraße 21'
        && $request->body()->all()['city_id'] === 80);
});

it('requires a name, street and city before sending', function () {
    withPortalToken();

    Livewire::test('venue-editor')
        ->call('open')
        ->call('save')
        ->assertHasErrors([
            'form.name' => 'required',
            'form.street' => 'required',
            'form.city_id' => 'required',
        ])
        ->assertNotDispatched('places-changed');
});

it('loads an own venue for editing and sends an update', function () {
    withPortalToken();
    MockClient::global([
        GetMyVenuesRequest::class => MockResponse::make(['data' => [myVenueFixture(['id' => 131, 'city_id' => 80])]]),
        GetCitiesRequest::class => MockResponse::make([cityFixture(['id' => 80, 'name' => 'Regensburg'])]),
        UpdateVenueRequest::class => MockResponse::make(['data' => myVenueFixture(['id' => 131])], 200),
    ]);

    Livewire::test('venue-editor')
        ->call('open', 131)
        ->assertSet('editingId', 131)
        ->assertSet('form.name', 'Bitcoin-Bar')
        ->assertSet('form.cityName', 'Regensburg')
        ->set('form.street', 'Neue Straße 2')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('places-changed');

    MockClient::global()->assertSent(fn (Request $request): bool => $request instanceof UpdateVenueRequest
        && $request->resolveEndpoint() === '/venues/131'
        && $request->body()->all()['street'] === 'Neue Straße 2');
});

it('keeps the editor open and reports a 403 when editing a foreign venue', function () {
    withPortalToken();
    MockClient::global([
        GetMyVenuesRequest::class => MockResponse::make(['data' => [myVenueFixture(['id' => 131, 'city_id' => 80])]]),
        GetCitiesRequest::class => MockResponse::make([cityFixture(['id' => 80])]),
        UpdateVenueRequest::class => MockResponse::make(['message' => 'This action is unauthorized.'], 403),
    ]);

    Livewire::test('venue-editor')
        ->call('open', 131)
        ->set('form.name', 'Geändert')
        ->call('save')
        ->assertNotDispatched('places-changed')
        ->assertSet('editingId', 131);
});

it('maps a 422 response back onto the form fields', function () {
    withPortalToken();
    MockClient::global([
        CreateVenueRequest::class => MockResponse::make([
            'message' => 'The given data was invalid.',
            'errors' => ['street' => ['Die Straße ist ungültig.']],
        ], 422),
    ]);

    Livewire::test('venue-editor')
        ->call('open')
        ->set('form.name', 'Bitcoin-Bar')
        ->set('form.street', 'x')
        ->call('selectCity', 80, 'Regensburg')
        ->call('save')
        ->assertHasErrors('form.street')
        ->assertSee('Die Straße ist ungültig.')
        ->assertNotDispatched('places-changed');
});

it('searches cities for the picker from two characters', function () {
    withPortalToken();
    MockClient::global([
        GetCitiesRequest::class => MockResponse::make([cityFixture()]),
    ]);

    Livewire::test('venue-editor')
        ->call('open')
        ->set('cityQuery', 'Reg')
        ->assertSee('Regensburg')
        ->assertSee('Germany');
});

it('adopts a city created inline via the city-saved event', function () {
    withPortalToken();

    Livewire::test('venue-editor')
        ->call('open')
        ->dispatch('city-saved', id: 80, name: 'Regensburg')
        ->assertSet('form.city_id', 80)
        ->assertSet('form.cityName', 'Regensburg');
});

it('does not overwrite an already chosen city when a city is saved', function () {
    withPortalToken();

    Livewire::test('venue-editor')
        ->call('open')
        ->call('selectCity', 5, 'Wien')
        ->dispatch('city-saved', id: 80, name: 'Regensburg')
        ->assertSet('form.city_id', 5)
        ->assertSet('form.cityName', 'Wien');
});

it('does not send a write without a portal token', function () {
    withoutPortalToken();

    Livewire::test('venue-editor')
        ->call('open')
        ->set('form.name', 'Bitcoin-Bar')
        ->set('form.street', 'Musterstraße 21')
        ->call('selectCity', 80, 'Regensburg')
        ->call('save')
        ->assertNotDispatched('places-changed');
});
