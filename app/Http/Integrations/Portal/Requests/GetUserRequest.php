<?php

namespace App\Http\Integrations\Portal\Requests;

use App\Data\Portal\UserProfileData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * GET /api/user — Profil des Token-Inhabers (auth:sanctum).
 */
class GetUserRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/user';
    }

    public function createDtoFromResponse(Response $response): UserProfileData
    {
        return UserProfileData::from($response->json());
    }
}
