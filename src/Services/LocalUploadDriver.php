<?php

namespace Ashiful\Upload\Services;

use Ashiful\Upload\Contracts\UploadDriver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocalUploadDriver implements UploadDriver
{
    public function upload(UploadedFile $file, string $path, array $options = []): string
    {
        $filename = $this->generateFilename($file);
        $disk = $options['disk'] ?? config('upload.disk', 'public');

        return $file->storeAs($path, $filename, ['disk' => $disk]);
    }

    public function temporaryUrl(string $path, $expiration, array $options = []): string
    {
        $disk = $options['disk'] ?? config('upload.disk', 'public');
        
        // If native driver doesn't support it (like local), generate a signed route
        try {
            return Storage::disk($disk)->temporaryUrl($path, $expiration);
        } catch (\RuntimeException $e) {
            return \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'ashiful.upload.signed',
                $expiration,
                ['path' => $path]
            );
        }
    }

    protected function generateFilename(UploadedFile $file): string
    {
        return Str::random(40) . '.' . $file->getClientOriginalExtension();
    }
}
