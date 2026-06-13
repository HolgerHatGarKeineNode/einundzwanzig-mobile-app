<?php

namespace App\Http\Integrations\Portal\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Gemeinsame Basis für alle schreibenden Portal-Requests (POST/PATCH).
 * Trägt den JSON-Body und reicht die bereits validierte Payload als
 * Request-Body durch. Die Method und der Endpoint kommen aus den
 * konkreten Requests.
 *
 * Bewusst body-only: Das DTO-Mapping und die 422-Fehlerauswertung
 * passieren zentral im PortalWriter, weil die store/update-Resources
 * des Portals teils nicht deckungsgleich mit den Lese-DTOs sind
 * (z. B. liefert die CityResource kein verschachteltes country).
 */
abstract class PortalWriteRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * @param  array<string, mixed>  $payload  Bereits validierte Felder.
     */
    public function __construct(protected readonly array $payload) {}

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return $this->payload;
    }
}
