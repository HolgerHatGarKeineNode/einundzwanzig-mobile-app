<?php

namespace App\Livewire\Forms;

use App\Data\Portal\MyCityData;
use App\Http\Integrations\Portal\Requests\CreateCityRequest;
use Livewire\Attributes\Validate;
use Livewire\Form;

/**
 * Form-Object für das Anlegen/Bearbeiten einer Stadt (Phase 6.2). Die Felder
 * spiegeln die Payload von {@see CreateCityRequest}: name, die per Namen
 * gesuchte, zu `country_id` aufgelöste Land sowie die Geo-Koordinaten (vom
 * Karten-Picker, Phase 6.3) und optional die Einwohnerzahl. `countryName` ist
 * nur Anzeige-/Suchhilfe und wird nicht gesendet.
 */
class CityForm extends Form
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|integer')]
    public ?int $country_id = null;

    /** Nur zur Anzeige des gewählten Landes; nicht Teil der Payload. */
    public string $countryName = '';

    #[Validate('required|numeric|between:-90,90')]
    public ?float $latitude = null;

    #[Validate('required|numeric|between:-180,180')]
    public ?float $longitude = null;

    #[Validate('nullable|integer|min:0')]
    public ?int $population = null;

    /**
     * Bestehende Stadt zum Bearbeiten in die Form laden.
     */
    public function setCity(MyCityData $city, string $countryName): void
    {
        $this->name = $city->name;
        $this->country_id = $city->country_id;
        $this->countryName = $countryName;
        $this->latitude = $city->latitude;
        $this->longitude = $city->longitude;
        $this->population = $city->population;
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
            'country_id' => $this->country_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'population' => $this->population,
        ];
    }
}
