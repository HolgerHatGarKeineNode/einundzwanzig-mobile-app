<?php

namespace App\Data\Portal;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * Veranstaltungsort aus GET /api/venues. Verschachtelt in Kurs-Events
 * (GET /api/course-events) liefert das Portal nur id und name, daher
 * sind die übrigen Felder Optional.
 */
final class VenueData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public int|Optional $city_id,
        public string|Optional $flag,
        public string|Optional $description,
        public CityData|Optional $city,
    ) {}
}
