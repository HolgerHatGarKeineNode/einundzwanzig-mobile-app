<?php

use App\Http\Integrations\Portal\Requests\GetMapMeetupsRequest;
use App\Models\User;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

afterEach(fn () => MockClient::destroyGlobal());

it('redirects the start route to the meetups page', function () {
    $this->get(route('home'))->assertRedirect(route('meetups'));
});

it('shows the bottom navigation and the hamburger menu on a page', function () {
    withoutPortalToken();
    MockClient::global([
        GetMapMeetupsRequest::class => MockResponse::make([]),
    ]);

    $this->get(route('meetups'))
        ->assertOk()
        // Bottom-Nav: Meetups / Termine / Karte / Profil
        ->assertSee(route('events'))
        ->assertSee(route('map'))
        ->assertSee(route('profile'))
        ->assertSee(__('Termine'))
        ->assertSee(__('Karte'))
        ->assertSee(__('Profil'))
        // Hamburger-Menü: Kurse, Referenten, Städte & Orte, Einstellungen
        ->assertSee(route('courses'))
        ->assertSee(__('Kurse'))
        ->assertSee(__('Referenten'))
        ->assertSee(__('Städte & Orte'))
        ->assertSee(__('Einstellungen'));
});

it('redirects guests from settings to the login page', function () {
    $response = $this->get(route('settings'));

    $response->assertRedirect(route('login'));
});

it('redirects authenticated users from settings to the profile page', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('settings'));

    $response->assertRedirect(route('profile.edit'));
});
