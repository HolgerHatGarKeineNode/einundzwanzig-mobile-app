<?php

namespace App\Http\Integrations\Portal\Requests;

use App\Data\Portal\MemberMeetupData;
use App\Http\Integrations\Portal\Requests\Concerns\CollectsDataFromResponse;
use Illuminate\Support\Collection;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * GET /api/meetup — Meetups, denen der Nutzer beigetreten ist.
 *
 * ⚠️ Die Route liegt im Portal in der öffentlichen Gruppe OHNE
 * auth:sanctum; $request->user() läuft dort über den web-Guard.
 * Mit Bearer-Token antwortet sie daher aktuell immer 401 — für die
 * App muss das Portal die Route noch um auth:sanctum ergänzen
 * (siehe PLAN.md, offene Fragen).
 */
class GetMemberMeetupsRequest extends Request
{
    /** @use CollectsDataFromResponse<MemberMeetupData> */
    use CollectsDataFromResponse;

    protected Method $method = Method::GET;

    /**
     * @param  list<int>  $selected
     */
    public function __construct(
        private readonly ?string $search = null,
        private readonly array $selected = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return '/meetup';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return array_filter([
            'search' => $this->search,
            'selected' => $this->selected !== [] ? $this->selected : null,
        ]);
    }

    /**
     * @param  array<int|string, mixed>  $json
     * @return Collection<int, MemberMeetupData>
     */
    public static function collectData(array $json): Collection
    {
        return MemberMeetupData::collect($json, Collection::class);
    }
}
