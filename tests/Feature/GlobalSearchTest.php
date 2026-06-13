<?php

use App\Http\Integrations\Portal\Requests\GetCoursesRequest;
use App\Http\Integrations\Portal\Requests\GetLecturersRequest;
use App\Http\Integrations\Portal\Requests\GetMapMeetupsRequest;
use Livewire\Livewire;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

afterEach(fn () => MockClient::destroyGlobal());

it('does not search until at least two characters are entered', function () {
    withoutPortalToken();

    Livewire::test('global-search')
        ->assertSee(__('Tippe mindestens zwei Zeichen, um zu suchen.'))
        ->set('term', 'a')
        ->assertSee(__('Tippe mindestens zwei Zeichen, um zu suchen.'));
});

it('searches meetups, courses and lecturers and links to their detail pages', function () {
    withoutPortalToken();
    MockClient::global([
        GetMapMeetupsRequest::class => MockResponse::make([mapMeetupFixture()]),
        GetCoursesRequest::class => MockResponse::make([detailedCourseFixture()]),
        GetLecturersRequest::class => MockResponse::make([detailedLecturerFixture()]),
    ]);

    Livewire::test('global-search')
        // Meetup-Treffer (Name/Stadt)
        ->set('term', 'asch')
        ->assertSee('Einundzwanzig Aschaffenburg')
        ->assertSee(route('meetups.show', 'aschaffenburg'))
        // Kurs-Treffer
        ->set('term', 'bitcoin')
        ->assertSee('Bitcoin, Blockchain und Geld')
        ->assertSee(route('courses.show', 5))
        // Referenten-Treffer
        ->set('term', 'toni')
        ->assertSee('Toni Stack')
        ->assertSee(route('lecturers.show', 3));
});

it('shows an empty state when nothing matches', function () {
    withoutPortalToken();
    MockClient::global([
        GetMapMeetupsRequest::class => MockResponse::make([mapMeetupFixture()]),
        GetCoursesRequest::class => MockResponse::make([detailedCourseFixture()]),
        GetLecturersRequest::class => MockResponse::make([detailedLecturerFixture()]),
    ]);

    Livewire::test('global-search')
        ->set('term', 'zzzznope')
        ->assertSee(__('Keine Treffer für „:term“.', ['term' => 'zzzznope']));
});
