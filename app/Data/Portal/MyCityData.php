<?php

namespace App\Data\Portal;

use Spatie\LaravelData\Data;

/**
 * Eigene Stadt aus GET /api/my-cities (CityResource, flache Schreib-/
 * Eigentums-Sicht mit id + country_id + Geo, im data-Wrapper). Anders als
 * das öffentliche {@see CityData} (verschachteltes country-Objekt, ohne
 * Geo) trägt diese Sicht die Felder, die der Editor zum Bearbeiten braucht;
 * den Landes-Anzeigenamen löst der Aufrufer über die country_id auf.
 */
final class MyCityData extends Data
{
    public function __construct(
        public int $id,
        public int $country_id,
        public string $name,
        public string $slug,
        public float $longitude,
        public float $latitude,
        public ?int $population,
    ) {}
}
