<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

/**
 * Hält den launchMode-Fix im generierten Android-Manifest am Leben.
 *
 * NativePHP scaffoldet `nativephp/android` bei `native:install` neu aus dem
 * Vendor-Template, das die MainActivity mit `launchMode="singleTop"` anlegt.
 * singleTop lässt externe Deep-Link-Intents (z. B. den Amber-Signer-Callback
 * `einundzwanzig://signed/...`) eine Wegwerf-Activity im Task des Aufrufers
 * erzeugen, statt die laufende App-Instanz per onNewIntent zu erreichen.
 * `singleTask` routet den Intent an die bestehende Instanz und räumt darüber
 * liegende Activities (Custom Tabs) automatisch ab. Siehe PLAN.md 1.20/1.21.
 */
class AndroidManifestPatcher
{
    protected const SEARCH = 'android:launchMode="singleTop"';

    protected const REPLACE = 'android:launchMode="singleTask"';

    public function __construct(protected ?string $manifestPath = null) {}

    public function manifestPath(): string
    {
        return $this->manifestPath ?? base_path('nativephp/android/app/src/main/AndroidManifest.xml');
    }

    /**
     * Ersetzt singleTop durch singleTask. Idempotent; liefert true nur,
     * wenn die Datei tatsächlich geändert wurde.
     */
    public function ensureSingleTask(): bool
    {
        $path = $this->manifestPath();

        if (! File::exists($path)) {
            return false;
        }

        $contents = File::get($path);

        if (! str_contains($contents, self::SEARCH)) {
            return false;
        }

        File::put($path, str_replace(self::SEARCH, self::REPLACE, $contents));

        return true;
    }

    public function isPatched(): bool
    {
        $path = $this->manifestPath();

        return File::exists($path) && str_contains(File::get($path), self::REPLACE);
    }
}
