<?php

namespace App\Data\Portal;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * Stadt aus GET /api/cities bzw. verschachtelt in Venues und Meetups.
 * Das Portal liefert das Land in allen Varianten mit; die Flaggen-URL
 * kommt nur mit dem Presence-Flag withDetails, daher Optional.
 */
final class CityData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public int $country_id,
        public CountryData $country,
        public string|Optional $flag,
    ) {}
}
