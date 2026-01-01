<?php

namespace Ashiful\Upload\Services;

use Ashiful\Upload\Contracts\VirusScanDriver;
use Illuminate\Http\UploadedFile;

class NullVirusScanDriver implements VirusScanDriver
{
    public function isSafe(UploadedFile $file): bool
    {
        // By default, assume everything is safe if no scanner is configured.
        return true;
    }
}
