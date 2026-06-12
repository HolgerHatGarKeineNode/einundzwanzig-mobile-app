<?php

namespace App\Data\Portal;

use Spatie\LaravelData\Data;

/**
 * Profil des Token-Inhabers aus GET /api/user.
 */
final class UserProfileData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $email,
        public ?string $nostr,
        public bool $is_lecturer,
        public bool $is_leader,
        public ?string $avatar,
    ) {}
}
