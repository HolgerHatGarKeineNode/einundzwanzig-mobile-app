<?php

namespace App\Data\Portal;

use App\Data\Portal\Concerns\RendersMarkdown;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

/**
 * Kurs-Detail aus GET /api/courses/{id}: Kurs mit Beschreibung, Logo,
 * Referent und allen kommenden Kurs-Events (inkl. Venue und Stadt),
 * aufsteigend nach Beginn sortiert.
 */
final class CourseDetailData extends Data
{
    use RendersMarkdown;

    /**
     * @param  array<int, CourseEventData>  $events
     */
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public string $image,
        public string $portalLink,
        public ?LecturerData $lecturer,
        #[DataCollectionOf(CourseEventData::class)]
        public array $events,
    ) {}

    public function descriptionHtml(): ?string
    {
        return $this->markdownToHtml($this->description);
    }
}
