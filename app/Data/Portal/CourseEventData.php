<?php

namespace App\Data\Portal;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * Kurs-Event aus GET /api/course-events (eigene Kurs-Events des
 * angemeldeten Referenten, inkl. Kurs- und Venue-Kurzinfo) sowie aus den
 * events der Kurs-Detail-Antwort GET /api/courses/{id} — dort fehlen
 * created_by/created_at/updated_at, daher die null-Defaults.
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
        public ?int $created_by = null,
        public ?CarbonImmutable $created_at = null,
        public ?CarbonImmutable $updated_at = null,
        public ?CourseData $course = null,
        public ?VenueData $venue = null,
    ) {}

    /**
     * Ort als „Venue · Stadt“, je nachdem wie viel die API mitliefert.
     */
    public function locationLabel(): ?string
    {
        if ($this->venue === null) {
            return null;
        }

        $city = $this->venue->city instanceof Optional ? null : $this->venue->city;

        return $city === null ? $this->venue->name : $this->venue->name.' · '.$city->name;
    }
}
