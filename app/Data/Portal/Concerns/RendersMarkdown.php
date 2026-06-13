<?php

namespace App\Data\Portal\Concerns;

use App\Support\Markdown;

/**
 * Rendert Markdown-Felder der Portal-API (intro, description) zu HTML.
 * Die eigentliche Sanitisierung lebt in {@see Markdown} (geteilt mit der
 * Live-Vorschau im Meetup-Editor, Phase 4.5); dieses Trait ergänzt nur die
 * Memoisierung pro Eingabe für die Lese-DTOs.
 */
trait RendersMarkdown
{
    /**
     * Pro Eingabe memoisiert, weil Blade-Views die *Html()-Methoden
     * mehrfach pro Render aufrufen (Sichtbarkeits-@if + Ausgabe).
     *
     * @var array<string, ?string>
     */
    private array $memoizedMarkdownHtml = [];

    protected function markdownToHtml(?string $markdown): ?string
    {
        if (blank($markdown)) {
            return null;
        }

        return $this->memoizedMarkdownHtml[$markdown] ??= Markdown::toHtml($markdown);
    }
}
