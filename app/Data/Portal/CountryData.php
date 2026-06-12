<?php

namespace App\Data\Portal;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * Land aus GET /api/countries. Als verschachteltes Objekt (z. B. city.country
 * bei GET /api/cities) liefert das Portal nur id und name, daher sind code
 * und flag Optional.
 */
final class CountryData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string|Optional $code,
        public string|Optional $flag,
    ) {}
}
