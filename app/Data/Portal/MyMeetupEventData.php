<?php

namespace App\Data\Portal;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

/**
 * Eigener Meetup-Termin aus GET /api/my-meetup-events (MeetupEventResource,
 * data-Wrapper). Anders als {@see MeetupEventData} (öffentlicher Feed mit
 * verschachteltem meetup-Objekt) ist dies die flache Schreib-/Eigentums-Sicht:
 * sie trägt die `id` (zum Bearbeiten) und `meetup_id` (Zuordnung), aber keine
 * Meetup-Detaildaten — der Anzeigename wird netzwerkfrei aus myMeetups()
 * aufgelöst (analog zur Stadt-Auflösung im Meetup-Editor).
 *
 * `start` kommt hier als ISO-8601 (Carbon-Default der Resource), nicht im
 * "Y-m-d H:i"-Format des öffentlichen Endpunkts — beide Formate deckt die
 * date_format-Liste in config/data.php ab. Die `recurrence_*`-Felder der
 * Resource werden bewusst nicht gemappt (erst mit Phase 5.4 relevant).
 */
final class MyMeetupEventData extends Data
{
    public function __construct(
        public int $id,
        public int $meetup_id,
        public CarbonImmutable $start,
        public ?string $location,
        public ?string $description,
        public ?string $link,
    ) {}
}
