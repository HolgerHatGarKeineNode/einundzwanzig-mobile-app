<?php

namespace App\Http\Integrations\Portal\Requests;

use App\Data\Portal\LecturerDetailData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * GET /api/lecturers/{id} — öffentliches Referenten-Profil mit Avatar,
 * Untertitel, Intro, Beschreibung, Links und den Kursen des Referenten.
 */
class GetLecturerRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(private readonly int $lecturerId) {}

    public function resolveEndpoint(): string
    {
        return '/lecturers/'.$this->lecturerId;
    }

    public function createDtoFromResponse(Response $response): LecturerDetailData
    {
        return LecturerDetailData::from($response->json());
    }
}
