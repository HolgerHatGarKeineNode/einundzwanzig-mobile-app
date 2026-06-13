#!/usr/bin/env bash
#
# Startet die manuelle Integrationstest-Suite gegen ein LOKAL laufendes
# Portal-Dev — inklusive der Schreibtests. Zieht dafür reproduzierbar ein
# frisches Sanctum-Token aus dem Portal (über dessen artisan tinker), damit
# nicht bei jedem Lauf von Hand ein Token besorgt werden muss.
#
# Voraussetzungen:
#   - Das Portal-Dev läuft (Standard: http://127.0.0.1:8000, z. B. via
#     `composer dev` / `php artisan serve` im Portal-Repo).
#   - Im Portal existiert mindestens ein User (DatabaseSeeder).
#
# Nutzung (aus dem Mobile-App-Repo):
#   scripts/run-integration.sh                # Lese- + Schreibtests
#   PORTAL_PATH=/pfad/zum/portal scripts/run-integration.sh
#   PORTAL_URL=http://127.0.0.1:9000 scripts/run-integration.sh
#   scripts/run-integration.sh --filter=updates   # Argumente gehen an pest
#
# Ohne Token (nur Lesetests) genügt: composer test:integration
set -euo pipefail

PORTAL_PATH="${PORTAL_PATH:-/home/user/Code/einundzwanzig-app}"
PORTAL_URL="${PORTAL_URL:-http://127.0.0.1:8000}"
TOKEN_NAME="${PORTAL_TEST_TOKEN_NAME:-mobile-integration}"

if [[ ! -f "${PORTAL_PATH}/artisan" ]]; then
    echo "✗ Portal-Repo nicht gefunden unter '${PORTAL_PATH}'." >&2
    echo "  Setze PORTAL_PATH auf das Verzeichnis des einundzwanzig-app-Repos." >&2
    exit 1
fi

# Erreichbarkeit kurz prüfen, sonst überspringen die Tests ohnehin.
host="$(printf '%s' "${PORTAL_URL}" | sed -E 's#^https?://([^:/]+).*#\1#')"
port="$(printf '%s' "${PORTAL_URL}" | sed -E 's#^https?://[^:/]+:?([0-9]+)?.*#\1#')"
port="${port:-8000}"
if ! (exec 3<>"/dev/tcp/${host}/${port}") 2>/dev/null; then
    echo "✗ Portal nicht erreichbar unter ${PORTAL_URL}. Läuft das Portal-Dev?" >&2
    exit 1
fi

echo "→ Hole frisches Sanctum-Token aus dem Portal (${PORTAL_PATH}) …"
# Im Portal-Verzeichnis ausführen: dessen SQLite-DB liegt unter einem RELATIVEN
# Pfad (database/database.sqlite) und würde aus einem anderen CWD eine leere
# DB öffnen (→ kein User).
TOKEN="$(cd "${PORTAL_PATH}" && php artisan tinker --execute \
    '$u = \App\Models\User::query()->first(); if (! $u) { fwrite(STDERR, "NOUSER"); exit(1); } echo $u->createToken("'"${TOKEN_NAME}"'")->plainTextToken;' \
    2>/dev/null | tail -n1 | tr -d '[:space:]')"

if [[ -z "${TOKEN}" || "${TOKEN}" == *NOUSER* ]]; then
    echo "✗ Konnte kein Token erzeugen — existiert ein User im Portal? (php artisan db:seed)" >&2
    exit 1
fi

echo "→ Token erhalten (User #1, ID-Präfix ${TOKEN%%|*}). Starte Integrationssuite …"
echo

export PORTAL_TEST_TOKEN="${TOKEN}"
export PORTAL_URL

exec php vendor/bin/pest -c phpunit.integration.xml "$@"
