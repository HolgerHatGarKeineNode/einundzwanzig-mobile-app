<?php

namespace App\Data\Portal;

use Spatie\LaravelData\Data;

/**
 * Einundzwanzig-Community im BTC-Map-Format aus GET /api/btc-map-communities.
 * tags enthält die GeoJSON-/Kontakt-Schlüssel ("icon:square",
 * "contact:telegram", "geo_json", …) unverändert.
 */
final class BtcMapCommunityData extends Data
{
    /**
     * @param  array<string, mixed>  $tags
     */
    public function __construct(
        public string $id,
        public array $tags,
    ) {}
}
