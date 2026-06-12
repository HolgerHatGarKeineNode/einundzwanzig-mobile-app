<?php

namespace App\Data\Portal;

use Spatie\LaravelData\Data;

/**
 * Stadt aus GET /api/cities bzw. verschachtelt in Venues und Meetups.
 * Das Portal liefert das Land in allen Varianten mit.
 */
final class CityData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public int $country_id,
        public CountryData $country,
    ) {}
}
