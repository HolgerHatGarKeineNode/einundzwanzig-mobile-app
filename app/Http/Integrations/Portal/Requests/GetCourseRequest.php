<?php

namespace App\Http\Integrations\Portal\Requests;

use App\Data\Portal\CourseDetailData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * GET /api/courses/{id} — öffentliches Kurs-Detail mit Beschreibung,
 * Logo, Referent und allen kommenden Kurs-Events (inkl. Venue + Stadt).
 */
class GetCourseRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(private readonly int $courseId) {}

    public function resolveEndpoint(): string
    {
        return '/courses/'.$this->courseId;
    }

    public function createDtoFromResponse(Response $response): CourseDetailData
    {
        return CourseDetailData::from($response->json());
    }
}
