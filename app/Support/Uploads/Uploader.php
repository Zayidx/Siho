<?php

namespace App\Support\Uploads;

use Illuminate\Support\Facades\Storage;

class Uploader
{
    /**
     * Store an uploaded image on the public disk under a directory.
     * Accepts Laravel UploadedFile or Livewire TemporaryUploadedFile.
     */
    public static function storePublicImage($file, string $directory): string
    {
        // Delegate to framework; returns relative path like "dir/hashedname.jpg"
        return $file->store($directory, 'public');
    }

    /**
     * Delete a public file only if the path looks local (not http/data URL).
     */
    public static function deletePublicIfLocal(?string $path): void
    {
        if (! $path) {
            return;
        }
        $p = (string) $path;
        if (str_starts_with($p, 'http') || str_starts_with($p, 'data:')) {
            return;
        }
        // Ignore if not exists; Storage::delete is safe to call
        Storage::disk('public')->delete($p);
    }
}
