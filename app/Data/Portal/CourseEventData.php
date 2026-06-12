<?php

namespace App\Data\Portal;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

/**
 * Kurs-Event aus GET /api/course-events (eigene Kurs-Events des
 * angemeldeten Referenten, inkl. Kurs- und Venue-Kurzinfo).
 */
final class CourseEventData extends Data
{
    public function __construct(
        public int $id,
        public int $course_id,
        public int $venue_id,
        public CarbonImmutable $from,
        public CarbonImmutable $to,
        public ?string $link,
        public ?int $created_by,
        public ?CarbonImmutable $created_at,
        public ?CarbonImmutable $updated_at,
        public ?CourseData $course = null,
        public ?VenueData $venue = null,
    ) {}
}
