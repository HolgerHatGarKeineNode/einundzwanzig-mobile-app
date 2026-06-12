---
name: github-release
description: "Erstellt ein GitHub-Release in Amber-Qualität für die Einundzwanzig-App: verifiziert die Artefakte in dist/v<version>/ (GPG-Signatur, SHA256, apksigner), generiert Release-Notes nach Amber-Vorbild (Zusammenfassung, Downloads, Verifikationsanleitung) und legt das Release per gh IMMER zuerst als Draft an. Aufrufen mit /github-release [version] — ohne Argument wird die Version aus dist/ bzw. dem letzten Build ermittelt."
---

# GitHub-Release in Amber-Qualität

Ziel: Eine Release-Page wie bei [Amber](https://github.com/greenart7c3/Amber/releases) —
signierte Artefakte, saubere Release-Notes mit Zusammenfassung, Download-Optionen und
vollständiger Verifikationsanleitung. **Immer zuerst als Draft anlegen**; der User
reviewt und published selbst.

## Feste Projektdaten

- Repo: `HolgerHatGarKeineNode/einundzwanzig-mobile-app`
- GPG-Fingerprint (Manifest-Signatur): `B2DD9D9969E61E617125346E6D5B01E06AA11B68`
  (Blockschreibweise: `B2DD 9D99 69E6 1E61 7125  346E 6D5B 01E0 6AA1 1B68`)
- Android-Cert-SHA256: `44:41:1E:20:A1:B4:3D:0F:66:CF:99:E1:23:8A:33:E7:E8:FD:92:48:F0:D0:D2:58:F5:E0:72:7C:FA:BF:0B:7C`
- Artefakt-Konvention: `dist/v<version>/einundzwanzig-universal-v<version>.apk`,
  `manifest-v<version>.txt`, `manifest-v<version>.txt.sig` (erzeugt `scripts/release.sh`)

## Ablauf (strikt in dieser Reihenfolge)

### 1. Version bestimmen

Argument `$1` nutzen, sonst neuestes `dist/v*`-Verzeichnis. Format `X.Y.Z` validieren.

### 2. Artefakte prüfen — Release nur mit grünem Ergebnis anlegen

Alle drei Dateien müssen in `dist/v<version>/` liegen. Dann verifizieren:

```bash
gpg --verify dist/v<version>/manifest-v<version>.txt.sig dist/v<version>/manifest-v<version>.txt   # → "Good signature", Fingerprint exakt B2DD…1B68
cd dist/v<version> && sha256sum -c manifest-v<version>.txt                                          # → OK
apksigner verify --print-certs <apk> | grep -i sha-256                                              # → 44411e20…0b7c (apksigner braucht java/JBR im PATH)
```

Fehlt die `.sig` oder schlägt ein Check fehl: **abbrechen** und dem User sagen, was fehlt
(z. B. `SKIP_BUILD=1 ./scripts/release.sh` zum Signieren). Niemals unsignierte oder
unverifizierte Artefakte hochladen.

### 3. Änderungs-Zusammenfassung erstellen

- Gibt es ein vorheriges Release-Tag: `git log <letztes-tag>..HEAD --oneline` als Quelle.
- Erstes Release: Feature-Überblick aus `README.md`/`PLAN.md`.
- 3–8 prägnante Bullet-Points, nutzerorientiert formuliert (was hat der Nutzer davon?),
  Deutsch, keine Commit-Hashes, keine internen Codenamen.

### 4. Release-Notes nach Template generieren

Notes-Datei nach `dist/v<version>/release-notes.md` schreiben (liegt in dist/, ist gitignored).
Template — Platzhalter ersetzen, Struktur und Verifikationsblock NICHT verändern:

```markdown
## Was ist neu

<3–8 Bullet-Points der Änderungen>

## Download

> Es gibt keinen Play-Store-Release. Offizielle Builds gibt es ausschließlich hier auf GitHub.

- `einundzwanzig-universal-v<version>.apk` unten herunterladen und **verifizieren** (siehe unten)

## Release verifizieren

Vollständige Anleitung: [VERIFY_RELEASES.md](https://github.com/HolgerHatGarKeineNode/einundzwanzig-mobile-app/blob/master/VERIFY_RELEASES.md)

**1. Signatur-Schlüssel importieren** (einmalig):

​```bash
gpg --keyserver hkps://keys.openpgp.org --recv-keys B2DD9D9969E61E617125346E6D5B01E06AA11B68
​```

Fingerprint muss exakt lauten: `B2DD 9D99 69E6 1E61 7125  346E 6D5B 01E0 6AA1 1B68` — sonst **nicht fortfahren**.

**2. Manifest-Signatur prüfen:**

​```bash
gpg --verify manifest-v<version>.txt.sig manifest-v<version>.txt
​```

Erwartet: `gpg: Good signature from "fsociety.mkv@pm.me"`

**3. APK-Prüfsumme vergleichen:**

​```bash
sha256sum -c manifest-v<version>.txt
​```

**4. Android-Zertifikat prüfen** (optional, [AppVerifier](https://github.com/soupslurpr/AppVerifier)/apksigner):

​```
space.einundzwanzig.mobile
44:41:1E:20:A1:B4:3D:0F:66:CF:99:E1:23:8A:33:E7:E8:FD:92:48:F0:D0:D2:58:F5:E0:72:7C:FA:BF:0B:7C
​```

## Security

Schwachstellen bitte vertraulich melden: [SECURITY.md](https://github.com/HolgerHatGarKeineNode/einundzwanzig-mobile-app/blob/master/SECURITY.md)
```

(Die `​`-Zeichen vor den Backticks im Template entfernen — sie verhindern nur das vorzeitige
Schließen dieses Code-Blocks.)

### 5. Draft-Release anlegen

Vorher prüfen, ob der Release-Commit gepusht ist (`git status` / `git log origin/master..HEAD`)
— der Tag wird beim Publish auf den Remote-HEAD gesetzt; bei ungepushten Commits den User
warnen, aber den Draft trotzdem anlegen.

```bash
gh release create v<version> \
  dist/v<version>/einundzwanzig-universal-v<version>.apk \
  dist/v<version>/manifest-v<version>.txt \
  dist/v<version>/manifest-v<version>.txt.sig \
  --draft \
  --title "v<version>" \
  --notes-file dist/v<version>/release-notes.md
```

Existiert bereits ein Release/Draft zum Tag: nachfragen statt überschreiben
(`gh release view v<version>`).

### 6. Abschlussbericht an den User

- Draft-URL nennen (gh gibt sie aus) + Hinweis: Review → „Publish release“ manuell.
- Verifikations-Ergebnisse zusammenfassen (Signatur ✓, Hash ✓, Cert ✓).
- **Niemals selbst publishen, niemals committen/taggen/pushen** — das macht der User.
