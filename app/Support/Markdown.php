<?php

namespace App\Support;

use App\Data\Portal\Concerns\RendersMarkdown;
use Illuminate\Support\Str;

/**
 * Wandelt Markdown der Portal-Inhalte (intro, description) sicher in HTML.
 * Rohes HTML wird gestrippt und unsichere Links entfernt, weil die Inhalte
 * von Portal-Nutzern stammen. Anker werden zu reinem Text, damit Links die
 * WebView nicht ohne Zurück-Navigation verlassen.
 *
 * Single Source of Truth für die Markdown-Sanitisierung: das Data-Trait
 * {@see RendersMarkdown} (Lese-DTOs) und die
 * Live-Vorschau im Meetup-Editor (Phase 4.5) teilen sich diese Logik.
 */
final class Markdown
{
    /** @var list<string> */
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'em', 'del',
        'ul', 'ol', 'li',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'blockquote', 'code', 'pre', 'hr',
    ];

    public static function toHtml(?string $markdown): ?string
    {
        if (blank($markdown)) {
            return null;
        }

        return strip_tags(Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]), self::ALLOWED_TAGS);
    }
}
