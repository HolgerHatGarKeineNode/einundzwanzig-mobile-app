<?php

namespace App\Livewire\Forms;

use App\Data\Portal\MyVenueData;
use App\Http\Integrations\Portal\Requests\CreateVenueRequest;
use Livewire\Attributes\Validate;
use Livewire\Form;

/**
 * Form-Object für das Anlegen/Bearbeiten eines Veranstaltungsorts (Phase 6.1).
 * Die Felder spiegeln die Payload von {@see CreateVenueRequest}: name, street
 * und die per Namen gesuchte, zu `city_id` aufgelöste Stadt (REST-Writes
 * erwarten IDs, siehe Entscheidungs-Log). `cityName` ist nur Anzeige-/Suchhilfe
 * und wird nicht gesendet.
 *
 * Geo-Koordinaten und ein Typ-Feld gibt es bewusst nicht — die Portal-API für
 * Venues kennt nur city_id/name/street; die Geo-Daten hängen an der Stadt
 * (siehe {@see CityForm}).
 */
class VenueForm extends Form
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|max:255')]
    public string $street = '';

    #[Validate('required|integer')]
    public ?int $city_id = null;

    /** Nur zur Anzeige der gewählten Stadt; nicht Teil der Payload. */
    public string $cityName = '';

    /**
     * Bestehenden Veranstaltungsort zum Bearbeiten in die Form laden.
     */
    public function setVenue(MyVenueData $venue, string $cityName): void
    {
        $this->name = $venue->name;
        $this->street = $venue->street;
        $this->city_id = $venue->city_id;
        $this->cityName = $cityName;
    }

    /**
     * Validierte Payload für den Portal-Write (ohne die Anzeige-Felder).
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $this->validate();

        return [
            'name' => $this->name,
            'street' => $this->street,
            'city_id' => $this->city_id,
        ];
    }
}
