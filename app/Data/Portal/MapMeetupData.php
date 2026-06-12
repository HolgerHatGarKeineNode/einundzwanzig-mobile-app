<?php

namespace App\Data\Portal;

use Spatie\LaravelData\Data;

/**
 * Meetup im Karten-Format aus GET /api/meetups (MeetupMapController).
 * intro und logo sind nur bei den Presence-Flags withIntro/withLogos
 * gefüllt, sonst null. top/left/state stammen aus historischen
 * GitHub-Daten für die SVG-Karte der Website.
 */
final class MapMeetupData extends Data
{
    public function __construct(
        public string $name,
        public string $portalLink,
        public ?string $url,
        public float|int|string|null $top,
        public float|int|string|null $left,
        public string $country,
        public ?string $state,
        public string $city,
        public float $longitude,
        public float $latitude,
        public ?string $twitter_username,
        public ?string $website,
        public ?string $simplex,
        public ?string $signal,
        public ?string $nostr,
        public ?NextEventData $next_event,
        public ?string $intro,
        public ?string $logo,
    ) {}
}
