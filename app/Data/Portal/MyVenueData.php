<?php

namespace App\Data\Portal;

use Spatie\LaravelData\Data;

/**
 * Eigener Veranstaltungsort aus GET /api/my-venues (VenueResource, flache
 * Schreib-/Eigentums-Sicht mit id + city_id + street, im data-Wrapper).
 * Anders als das öffentliche {@see VenueData} (verschachtelte Stadt, ohne
 * street) trägt diese Sicht die Felder, die der Editor zum Bearbeiten
 * braucht; den Stadt-Anzeigenamen löst der Aufrufer netzwerkfrei über die
 * city_id auf (wie die Stadt-Auflösung beim Meetup).
 */
final class MyVenueData extends Data
{
    public function __construct(
        public int $id,
        public int $city_id,
        public string $name,
        public string $slug,
        public string $street,
    ) {}
}
