<?php

namespace App\Http\Integrations\Portal\Requests;

use App\Data\Portal\CourseData;
use App\Http\Integrations\Portal\Requests\Concerns\CollectsDataFromResponse;
use Illuminate\Support\Collection;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/courses — öffentliche Kurs-Liste (id, name, image).
 * Ohne selected begrenzt das Portal auf 10 Einträge.
 */
class GetCoursesRequest extends Request
{
    /** @use CollectsDataFromResponse<CourseData> */
    use CollectsDataFromResponse;

    protected Method $method = Method::GET;

    /**
     * @param  list<int>  $selected
     */
    public function __construct(
        private readonly ?string $search = null,
        private readonly ?int $userId = null,
        private readonly array $selected = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return '/courses';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return array_filter([
            'search' => $this->search,
            'user_id' => $this->userId,
            'selected' => $this->selected !== [] ? $this->selected : null,
        ]);
    }

    /**
     * @param  array<int|string, mixed>  $json
     * @return Collection<int, CourseData>
     */
    public static function collectData(array $json): Collection
    {
        return CourseData::collect($json, Collection::class);
    }
}
