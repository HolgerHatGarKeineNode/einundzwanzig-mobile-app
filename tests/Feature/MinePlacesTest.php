<?php

use App\Http\Integrations\Portal\Requests\GetCitiesRequest;
use App\Http\Integrations\Portal\Requests\GetCountriesRequest;
use App\Http\Integrations\Portal\Requests\GetMyCitiesRequest;
use App\Http\Integrations\Portal\Requests\GetMyVenuesRequest;
use Livewire\Livewire;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

afterEach(fn () => MockClient::destroyGlobal());

it('shows the connect CTA for guests', function () {
    withoutPortalToken();

    Livewire::test('pages::mine.places')
        ->assertSee(__('Mit Portal verbinden'))
        ->assertSee(__('Konto verbinden'))
        ->assertDontSee(__('Stadt bearbeiten'));
});

it('lists own cities with the resolved country and an edit affordance', function () {
    withPortalToken();
    MockClient::global([
        GetMyCitiesRequest::class => MockResponse::make(['data' => [myCityFixture(['id' => 80, 'country_id' => 1, 'name' => 'Regensburg'])]]),
        GetCountriesRequest::class => MockResponse::make([countryFixture(['id' => 1, 'name' => 'Germany'])]),
    ]);

    Livewire::test('pages::mine.places')
        ->assertSee('Regensburg')
        ->assertSee('Germany')
        ->assertSee(__('Stadt anlegen'))
        ->assertSee(__('Stadt bearbeiten'));
});

it('lists own venues with the resolved city and street on the venues tab', function () {
    withPortalToken();
    MockClient::global([
        GetMyVenuesRequest::class => MockResponse::make(['data' => [myVenueFixture(['id' => 131, 'city_id' => 80, 'name' => 'Bitcoin-Bar', 'street' => 'Hauptstraße 1'])]]),
        GetCitiesRequest::class => MockResponse::make([cityFixture(['id' => 80, 'name' => 'Regensburg'])]),
    ]);

    Livewire::test('pages::mine.places', ['tab' => 'orte'])
        ->assertSee('Bitcoin-Bar')
        ->assertSee('Regensburg')
        ->assertSee('Hauptstraße 1')
        ->assertSee(__('Ort bearbeiten'));
});

it('shows the empty-state create CTA when the user has no cities', function () {
    withPortalToken();
    MockClient::global([
        GetMyCitiesRequest::class => MockResponse::make(['data' => []]),
    ]);

    Livewire::test('pages::mine.places')
        ->assertSee(__('Noch keine eigenen Städte'))
        ->assertSee(__('Stadt anlegen'));
});

it('refreshes the lists when places change', function () {
    withPortalToken();
    MockClient::global([
        GetMyCitiesRequest::class => MockResponse::make(['data' => [myCityFixture(['name' => 'Regensburg'])]]),
        GetCountriesRequest::class => MockResponse::make([countryFixture(['id' => 1, 'name' => 'Germany'])]),
    ]);

    Livewire::test('pages::mine.places')
        ->assertSee('Regensburg')
        ->call('refreshLists')
        ->assertSee('Regensburg');
});
