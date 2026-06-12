# Einundzwanzig Mobile

Eine mobile App der [Einundzwanzig](https://einundzwanzig.space)-Community für Android:
Meetups, Termine, Kurse, Referenten und Orte aus dem
[Einundzwanzig-Portal](https://portal.einundzwanzig.space) — direkt in deiner Tasche.

- 📅 Meetups & Termine mit Regions-Filter
- 🗺️ Karte aller Meetups (OpenStreetMap)
- 🎓 Kurse & Referenten
- ⚡ Login per Lightning (LNURL-auth) oder Nostr (NIP-55, z. B. [Amber](https://github.com/greenart7c3/Amber))
- 📦 Offline-fähig: zuletzt geladene Daten bleiben verfügbar
- 🌐 Deutsch & Englisch

Gebaut mit [NativePHP Mobile](https://nativephp.com/mobile), Laravel, Livewire und Flux UI.

## Download & Installation

> Es gibt **keinen** Play-Store-Release. Offizielle Builds gibt es ausschließlich über
> GitHub Releases:

- **GitHub:** APK aus dem [neuesten Release](https://github.com/HolgerHatGarKeineNode/einundzwanzig-mobile-app/releases/latest) laden — danach **verifizieren** (siehe unten)

## Sicherheit & Verifikation

Alle Releases werden kryptografisch signiert: Das Release-Manifest (`manifest-vX.Y.Z.txt`
mit den SHA256-Prüfsummen aller APKs) ist GPG-signiert, die APKs tragen die
Android-Signatur des Projekts.

Die vollständige Anleitung steht in [VERIFY_RELEASES.md](VERIFY_RELEASES.md).

**GPG-Signaturschlüssel:**

```
Key-ID:      B2DD9D9969E61E617125346E6D5B01E06AA11B68
Fingerprint: B2DD 9D99 69E6 1E61 7125  346E 6D5B 01E0 6AA1 1B68
```

**Android-Signaturzertifikat** (für [AppVerifier](https://github.com/soupslurpr/AppVerifier) / `apksigner`):

```
Package: space.einundzwanzig.mobile
SHA-256: 44:41:1E:20:A1:B4:3D:0F:66:CF:99:E1:23:8A:33:E7:E8:FD:92:48:F0:D0:D2:58:F5:E0:72:7C:FA:BF:0B:7C
```

Schwachstellen bitte vertraulich melden — siehe [SECURITY.md](SECURITY.md).

## Entwicklung

```bash
composer install && yarn install
yarn build --mode=android
php artisan native:run android        # Build + Start im Emulator/Gerät
php artisan test --compact            # Tests
```

Details zu Setup, Dev-Loop und Architektur: [`docs/nativephp-ausfuehrungsplan.md`](docs/nativephp-ausfuehrungsplan.md)
und [`PLAN.md`](PLAN.md).

## Release bauen

```bash
php artisan native:release patch      # Version bumpen
./scripts/release.sh                  # APK bauen, Manifest erzeugen, GPG-signieren
```

Das Skript legt alle GitHub-Release-Artefakte unter `dist/v<version>/` ab
(APK, `manifest-v<version>.txt`, `manifest-v<version>.txt.sig`).

## Lizenz

[MIT](LICENSE)
