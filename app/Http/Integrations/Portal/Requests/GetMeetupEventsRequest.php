<?php

namespace App\Http\Integrations\Portal\Requests;

use App\Data\Portal\MeetupEventData;
use App\Http\Integrations\Portal\Requests\Concerns\CollectsDataFromResponse;
use Illuminate\Support\Collection;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/meetup-events/{date?} — öffentliche Meetup-Termine.
 * Mit Datum (Y-m-d) wird auf den Monat dieses Datums gefiltert.
 */
class GetMeetupEventsRequest extends Request
{
    /** @use CollectsDataFromResponse<MeetupEventData> */
    use CollectsDataFromResponse;

    protected Method $method = Method::GET;

    public function __construct(private readonly ?string $date = null) {}

    public function resolveEndpoint(): string
    {
        return '/meetup-events'.($this->date !== null ? '/'.$this->date : '');
    }

    /**
     * @param  array<int|string, mixed>  $json
     * @return Collection<int, MeetupEventData>
     */
    public static function collectData(array $json): Collection
    {
        return MeetupEventData::collect($json, Collection::class);
    }
}
