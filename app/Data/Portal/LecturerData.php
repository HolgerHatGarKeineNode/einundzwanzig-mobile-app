<?php

namespace App\Data\Portal;

use Spatie\LaravelData\Data;

/**
 * Referent aus GET /api/lecturers (öffentliche Picker-Liste:
 * id, name und Avatar-Thumbnail).
 */
final class LecturerData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $image,
    ) {}
}
