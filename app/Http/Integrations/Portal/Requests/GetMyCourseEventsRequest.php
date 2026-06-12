<?php

namespace App\Http\Integrations\Portal\Requests;

use App\Data\Portal\CourseEventData;
use App\Http\Integrations\Portal\Requests\Concerns\CollectsDataFromResponse;
use Illuminate\Support\Collection;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/course-events — eigene Kurs-Events des angemeldeten Nutzers
 * inkl. Kurs- und Venue-Kurzinfo (auth:sanctum). Ersetzt das im Plan
 * ursprünglich angenommene /api/my-courses, das im Portal nicht existiert.
 */
class GetMyCourseEventsRequest extends Request
{
    /** @use CollectsDataFromResponse<CourseEventData> */
    use CollectsDataFromResponse;

    protected Method $method = Method::GET;

    public function __construct(private readonly ?int $courseId = null) {}

    public function resolveEndpoint(): string
    {
        return '/course-events';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return array_filter([
            'course_id' => $this->courseId,
        ]);
    }

    /**
     * @param  array<int|string, mixed>  $json
     * @return Collection<int, CourseEventData>
     */
    public static function collectData(array $json): Collection
    {
        return CourseEventData::collect($json, Collection::class);
    }
}
