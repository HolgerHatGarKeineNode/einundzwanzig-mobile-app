# Einundzwanzig Mobile App — Umsetzungsplan

> **Arbeitsanweisung für Claude:** Dieser Plan ist die Single Source of Truth.
> Bei Session-Start: diesen Plan lesen, erste unerledigte Checkbox `[ ]` finden, dort weitermachen.
> Erledigte Punkte sofort auf `[x]` setzen. Neue Erkenntnisse/Entscheidungen unten in
> „Entscheidungs-Log" bzw. „Offene Fragen" nachtragen. Vor jedem Portal-Eingriff:
> Konventionen im Schwesterprojekt prüfen.

## Kontext & feste Entscheidungen

- **Diese App** (`/home/user/Code/einundzwanzig-mobile-app`): Laravel 13 + NativePHP Mobile v3 + Livewire 4 + Flux Pro. App-ID `space.einundzwanzig.mobile`. Installiert, aber ungenutzt: Saloon v4, spatie/laravel-data, NativePHP-Plugins `network`/`dialog`/`share`/`browser` (⚠️ noch nicht registriert), laravel-lang.
- **Portal** (`/home/user/Code/einundzwanzig-app`, portal.einundzwanzig.space): Laravel 13, Livewire 4, Flux Pro, Sanctum (`HasApiTokens` am User), bestehende API unter `/api` (Scramble-Doku unter `/docs/api`). Wir dürfen und werden das Portal erweitern.
- **Auth-Flow (entschieden):** Deep-Link-Flow. App öffnet `portal.../auth/mobile` im In-App-Browser → User loggt sich ein → Portal erzeugt Sanctum Personal Access Token → Redirect `einundzwanzig://auth?token=...` → App speichert Token in SecureStorage → alle API-Calls mit `Authorization: Bearer`.
- **Login-Methoden (entschieden):** Nur **Lightning (LNURL-auth)** und **Nostr** — exakt wie der bestehende Portal-Login. Kein E-Mail/Passwort, keine eigene Registrierung in der App.
- **Module v1 (entschieden):** Meetups & Termine, Kurse & Referenten, Orte & Karte. **Nicht** in v1: Library, Podcasts, BitcoinEvents, ProjectProposals, Teams.
- **v1 ist read-only + Auth.** Schreibfunktionen (Events anlegen/bearbeiten) kommen in v2.
- App baut **kein eigenes Login-Frontend** — Login-UI lebt im Portal (neue mobile Views).

---

## Phase 1 — Portal-Erweiterung: Mobile Auth-Flow

Arbeitsverzeichnis: `/home/user/Code/einundzwanzig-app` (eigener Branch, z. B. `feature/mobile-auth`).

- [ ] 1.1 Bestehenden Login-Flow analysieren: LNURL (`app/Http/Controllers/LnurlAuthController.php`, `routes/auth.php`, `POST /api/lnurl-auth-callback`, `GET /auth/complete-lightning/{k1}`) und Nostr-Login-Komponente finden und verstehen.
- [ ] 1.2 Route `GET /auth/mobile` anlegen: schlanke, mobile-optimierte Login-View (Livewire/Flux) mit nur Lightning + Nostr. Query-Param `redirect_uri` (nur `einundzwanzig://`-Schema zulassen, Whitelist!) und optional `device_name`.
- [ ] 1.3 Token-Erzeugung nach erfolgreichem Login: `auth()->user()->createToken($deviceName, [abilities])` → Redirect auf `einundzwanzig://auth?token={plainTextToken}`. Flow-State (redirect_uri) über die Session des Login-Vorgangs tragen — auch über den LNURL-Callback hinweg (k1 ↔ Session-Mapping beachten).
- [ ] 1.4 Wiederverwendung bei bereits eingeloggter Session: ist der User im In-App-Browser schon eingeloggt, Bestätigungsseite zeigen („Als {name} mit der App verbinden?") statt erneutem Login.
- [ ] 1.5 Sicherheit: Rate-Limiting auf `/auth/mobile`, Token-Name = Gerätename, alte Tokens desselben Geräts optional ersetzen, Abilities minimal halten (v1: read + my-*).
- [ ] 1.6 `GET /api/user` (Sanctum-geschützt) prüfen/anlegen: liefert Profil des Token-Users (Name, Avatar, is_lecturer, Meetup-Mitgliedschaften) — die App braucht das direkt nach dem Login.
- [ ] 1.7 Token-Verwaltung im Portal-Dashboard prüfen: User soll App-Tokens sehen und widerrufen können (existiert ggf. schon über Sanctum-Token-UI).
- [ ] 1.8 Pest-Tests im Portal für den kompletten Mobile-Auth-Flow (Redirect-Whitelist, Token-Erzeugung, Abilities).
- [ ] 1.9 Pint laufen lassen, Branch committen. (Deployment macht der User.)

## Phase 2 — App: Deep Link, SecureStorage, Login-Flow

Arbeitsverzeichnis: dieses Projekt.

- [ ] 2.1 NativePHP-Plugins registrieren (`browser`, `dialog`, `network`, `share`) — laut Memory installiert, aber nicht registriert. Skill `nativephp-mobile` aktivieren und Registrierungsweg prüfen.
- [ ] 2.2 Deep-Link-Schema `einundzwanzig://` in NativePHP konfigurieren (`config/nativephp.php` / `.env` `NATIVEPHP_DEEPLINK_SCHEME`); Handler für `einundzwanzig://auth?token=...` bauen.
- [ ] 2.3 `AuthService` (o. ä.): Token aus Deep Link entgegennehmen → **SecureStorage** (NativePHP) speichern, nie in DB/Session/Logs. Logout = Token lokal löschen + `DELETE`-Call ans Portal (Token revoken).
- [ ] 2.4 Login-Screen in der App: nur ein Button „Mit Einundzwanzig Portal anmelden" → öffnet `https://portal.einundzwanzig.space/auth/mobile?redirect_uri=einundzwanzig://auth&device_name={gerät}` im In-App-Browser (browser-Plugin). Lokale `.env`-Konfig `PORTAL_URL` für Dev gegen lokales Portal.
- [ ] 2.5 Auth-Middleware/State in der App: eingeloggt vs. Gast; nach Login `GET /api/user` ziehen und Profil lokal cachen. Gast-Modus erlaubt die öffentlichen Read-Inhalte trotzdem (API ist public).
- [ ] 2.6 Feature-Tests (Pest): Deep-Link-Token-Verarbeitung, AuthService, Logout. HTTP gegen Portal mit Saloon::fake() mocken.

## Phase 3 — App: Saloon API-Client + DTOs

- [ ] 3.1 Saloon-Connector `PortalConnector` (Base-URL aus Config, Bearer-Token aus SecureStorage wenn vorhanden, Accept: application/json, sinnvolle Timeouts/Retry).
- [ ] 3.2 Requests für v1-Endpunkte: `GET /api/meetups` (Map-Format), `GET /api/meetup`, `GET /api/meetup-events/{date?}`, `GET /api/courses`, `GET /api/lecturers`, `GET /api/cities`, `GET /api/venues`, `GET /api/countries`, `GET /api/btc-map-communities`, `GET /api/user`, `GET /api/my-meetups`, `GET /api/my-courses`.
- [ ] 3.3 DTOs mit spatie/laravel-data je Resource (Meetup, MeetupEvent, Course, Lecturer, City, Venue, UserProfile) — Felder gegen die Portal-Resources (`app/Http/Resources/` im Schwesterprojekt) bzw. Scramble-Doku abgleichen.
- [ ] 3.4 Caching-Layer: Responses lokal cachen (SQLite/Cache) mit TTL, damit die App offline zumindest zuletzt geladene Daten zeigt (network-Plugin: Online-Status prüfen).
- [ ] 3.5 Unit-/Feature-Tests mit Saloon MockClient für Connector + DTO-Mapping.

## Phase 4 — App: Modul Meetups & Termine

- [ ] 4.1 Bestehende `meetups`-Route/Home-View der App sichten (Commit „Add meetups route") und auf API-Daten umstellen.
- [ ] 4.2 Meetup-Liste (Suche/Filter nach Land/Stadt) + Meetup-Detail (Beschreibung, Links, nächste Events).
- [ ] 4.3 Event-Übersicht „Kommende Termine" (`/api/meetup-events`), Detailansicht mit Datum/Ort.
- [ ] 4.4 iCal-/Kalender-Export oder „Teilen" via share-Plugin (Meetup-Link teilen).
- [ ] 4.5 Eingeloggt: „Meine Meetups" (`/api/my-meetups`) als eigener Tab/Bereich.
- [ ] 4.6 Browser-Tests/Smoke-Tests (Pest v4) für die Views.

## Phase 5 — App: Modul Kurse & Referenten

- [ ] 5.1 Kurs-Liste + Kurs-Detail (inkl. kommender Kurs-Events).
- [ ] 5.2 Referenten-Liste + Profil (Avatar, Nostr, Kurse des Referenten).
- [ ] 5.3 Eingeloggt als Lecturer: „Meine Kurse" read-only Übersicht.
- [ ] 5.4 Tests wie in Phase 4.

## Phase 6 — App: Modul Orte & Karte

- [ ] 6.1 Kartenlösung wählen (Leaflet + OSM-Tiles im WebView ist naheliegend; Entscheidung dokumentieren).
- [ ] 6.2 Karte mit Meetups (`/api/meetups` Map-Format) und BTC-Map-Communities; Marker → Meetup-Detail.
- [ ] 6.3 Städte-/Venue-Verzeichnis als Liste (Cities, Venues).
- [ ] 6.4 Tests.

## Phase 7 — Polish & Release-Vorbereitung

- [ ] 7.1 Navigation/Shell finalisieren (Tab-Bar: Meetups / Termine / Karte / Kurse / Profil), Dark Mode, Einundzwanzig-Branding (Assets aus Schwesterprojekt übernehmen).
- [ ] 7.2 Profil-Screen: Portal-Profildaten, Token-/Logout-Verwaltung, App-Version.
- [ ] 7.3 Fehler-/Offline-Zustände sauber (dialog-Plugin für Fehler, leere States).
- [ ] 7.4 Lokalisierung de/en mit laravel-lang.
- [ ] 7.5 Android-Release-Build laut `docs/nativephp-ausfuehrungsplan.md` (Keystore, .aab) — iOS später (macOS nötig).

---

## v2-Kandidaten (noch nicht entschieden)

- [ ] Schreibfunktionen: Meetup-Events + Kurs-Events anlegen/bearbeiten (Sanctum-Endpunkte existieren schon).
- [ ] Push-Notifications (NativePHP push) für neue Events der eigenen Meetups — bräuchte Portal-Erweiterung (Device-Registrierung + Versand).
- [ ] Deep Links in Inhalte (`einundzwanzig://meetup/{slug}`) + Portal-seitige App-Links/AssetLinks fürs Teilen.
- [ ] Library & Podcasts (bräuchte neue API-Endpunkte im Portal).
- [ ] Meetup beitreten/verlassen aus der App (neuer Portal-Endpunkt).
- [ ] QR-Scanner (scanner-Plugin) z. B. für LNURL/Events.

## Offene Fragen an den User

- [ ] Soll die LNURL-/Nostr-Login-UI im Portal-Look bleiben oder ein abgespecktes „App-Connect"-Design bekommen?
- [ ] Token-Abilities: reicht v1 read-only-Scope, oder gleich volle `my-*`-Abilities vergeben, damit v2 ohne Re-Login geht?
- [ ] Karte: Leaflet/OSM ok, oder gibt es eine Präferenz (MapLibre, statische Liste zuerst)?
- [ ] Dev-Setup: läuft das Portal lokal (z. B. Herd/Sail), damit der Auth-Flow gegen localhost getestet werden kann?

## Entscheidungs-Log

- 2026-06-11: Deep-Link-Flow für Token-Übergabe gewählt (statt In-App-Formular oder Device-Code).
- 2026-06-11: Login nur Lightning (LNURL) + Nostr, wie Portal-Login. Keine eigene Registrierung in der App.
- 2026-06-11: v1-Module = Meetups & Termine, Kurse & Referenten, Orte & Karte. v1 read-only + Auth.
