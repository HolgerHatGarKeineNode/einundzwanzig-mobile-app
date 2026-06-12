<?php

namespace App\Data\Portal;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * Referent aus GET /api/lecturers (öffentliche Picker-Liste:
 * id, name und Avatar-Thumbnail). subtitle und future_events_count
 * liefert das Portal nur mit dem Presence-Flag withDetails; in der
 * Kurs-Detail-Antwort fehlt future_events_count — daher Optional.
 */
final class LecturerData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $image,
        public string|Optional|null $subtitle,
        public int|Optional $future_events_count,
    ) {}

    public function subtitleOrNull(): ?string
    {
        return $this->subtitle instanceof Optional ? null : $this->subtitle;
    }

    public function futureEventsCount(): int
    {
        return $this->future_events_count instanceof Optional ? 0 : $this->future_events_count;
    }
}
