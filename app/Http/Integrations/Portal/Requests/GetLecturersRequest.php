<?php

namespace App\Http\Integrations\Portal\Requests;

use App\Data\Portal\LecturerData;
use App\Http\Integrations\Portal\Requests\Concerns\CollectsDataFromResponse;
use Illuminate\Support\Collection;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/lecturers — öffentliche Referenten-Liste (id, name, image).
 * Ohne selected begrenzt das Portal auf 10 Einträge; mit dem
 * Presence-Flag withDetails entfällt das Limit und jeder Referent
 * enthält zusätzlich subtitle und future_events_count.
 */
class GetLecturersRequest extends Request
{
    /** @use CollectsDataFromResponse<LecturerData> */
    use CollectsDataFromResponse;

    protected Method $method = Method::GET;

    /**
     * @param  list<int>  $selected
     */
    public function __construct(
        private readonly ?string $search = null,
        private readonly array $selected = [],
        private readonly bool $withDetails = false,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/lecturers';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return array_filter([
            'search' => $this->search,
            'selected' => $this->selected !== [] ? $this->selected : null,
            'withDetails' => $this->withDetails ? '1' : null,
        ]);
    }

    /**
     * @param  array<int|string, mixed>  $json
     * @return Collection<int, LecturerData>
     */
    public static function collectData(array $json): Collection
    {
        return LecturerData::collect($json, Collection::class);
    }
}
