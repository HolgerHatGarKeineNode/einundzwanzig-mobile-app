<?php

namespace App\Data\Portal;

use App\Data\Portal\Concerns\RendersMarkdown;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

/**
 * Referenten-Profil aus GET /api/lecturers/{id}: Avatar, Untertitel,
 * Intro, Beschreibung, Nostr-/Web-Links und die Kurse des Referenten
 * mit deren nächstem zukünftigen Kurs-Event.
 */
final class LecturerDetailData extends Data
{
    use RendersMarkdown;

    /**
     * @param  array<int, CourseData>  $courses
     */
    public function __construct(
        public int $id,
        public string $name,
        public ?string $subtitle,
        public ?string $intro,
        public ?string $description,
        public string $image,
        public bool $active,
        public ?string $nostr,
        public ?string $website,
        public ?string $twitter_username,
        public ?string $lightning_address,
        #[DataCollectionOf(CourseData::class)]
        public array $courses,
    ) {}

    public function introHtml(): ?string
    {
        return $this->markdownToHtml($this->intro);
    }

    public function descriptionHtml(): ?string
    {
        return $this->markdownToHtml($this->description);
    }

    /**
     * Externe Links des Referenten als [Label => URL] für die Link-Liste.
     *
     * @return array<string, string>
     */
    public function socialLinks(): array
    {
        $links = [];

        if ($this->website !== null) {
            $links[__('Website')] = $this->website;
        }

        if ($this->twitter_username !== null) {
            $links[__('X (Twitter)')] = 'https://x.com/'.ltrim($this->twitter_username, '@');
        }

        if ($this->nostr !== null) {
            $links[__('Nostr')] = 'https://njump.me/'.$this->nostr;
        }

        return $links;
    }
}
