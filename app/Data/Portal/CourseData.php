<?php

namespace App\Data\Portal;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * Kurs aus GET /api/courses. Verschachtelt in Kurs-Events
 * (GET /api/course-events) fehlt das image, daher Optional.
 */
final class CourseData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string|Optional $image,
    ) {}
}
