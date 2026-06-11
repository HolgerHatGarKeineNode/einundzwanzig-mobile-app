<?php

use App\Models\User;

it('shows the start screen with the bottom navigation', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Willkommen bei EINUNDZWANZIG')
        ->assertSee('Meetups')
        ->assertSee('Einstellungen')
        ->assertSee(route('meetups'));
});

it('shows the meetups placeholder screen', function () {
    $response = $this->get(route('meetups'));

    $response->assertOk()
        ->assertSee('Meetups kommen bald');
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
