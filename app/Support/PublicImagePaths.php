<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Resolves public disk URLs for JSON columns that store lists of upload paths.
 */
class PublicImagePaths
{
    /**
     * @return list<string>
     */
    public static function urls(mixed $paths): array
    {
        if (is_string($paths)) {
            // Catalog queries select the raw JSON column without a model cast.
            $paths = json_decode($paths, true);
        }

        if (! is_array($paths)) {
            return [];
        }

        return collect($paths)
            ->filter(fn (mixed $path): bool => filled($path))
            ->map(fn (mixed $path): string => Storage::disk('public')->url((string) $path))
            ->values()
            ->all();
    }
}
