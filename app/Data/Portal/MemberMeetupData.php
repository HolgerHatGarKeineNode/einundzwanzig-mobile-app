<?php

namespace App\Data\Portal;

use Spatie\LaravelData\Data;

/**
 * Meetup-Mitgliedschaft aus GET /api/meetup (Picker-Liste der Meetups,
 * denen der angemeldete Nutzer beigetreten ist).
 */
final class MemberMeetupData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public int $city_id,
        public string $profile_image,
        public CityData $city,
    ) {}
}
