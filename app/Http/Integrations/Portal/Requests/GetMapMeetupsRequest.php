<?php

namespace App\Http\Integrations\Portal\Requests;

use App\Data\Portal\MapMeetupData;
use App\Http\Integrations\Portal\Requests\Concerns\CollectsDataFromResponse;
use Illuminate\Support\Collection;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/meetups — öffentliche Meetups im Karten-Format.
 * withIntro/withLogos sind Presence-Flags des Portals.
 */
class GetMapMeetupsRequest extends Request
{
    /** @use CollectsDataFromResponse<MapMeetupData> */
    use CollectsDataFromResponse;

    protected Method $method = Method::GET;

    public function __construct(
        private readonly bool $withIntro = false,
        private readonly bool $withLogos = false,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/meetups';
    }

    /**
     * @return array<string, string>
     */
    protected function defaultQuery(): array
    {
        return array_filter([
            'withIntro' => $this->withIntro ? '1' : null,
            'withLogos' => $this->withLogos ? '1' : null,
        ]);
    }

    /**
     * @param  array<int|string, mixed>  $json
     * @return Collection<int, MapMeetupData>
     */
    public static function collectData(array $json): Collection
    {
        return MapMeetupData::collect($json, Collection::class);
    }
}
