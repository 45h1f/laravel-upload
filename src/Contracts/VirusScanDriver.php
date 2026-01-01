<?php

namespace Ashiful\Upload\Contracts;

use Illuminate\Http\UploadedFile;

interface VirusScanDriver
{
    /**
     * Scan the file for viruses.
     *
     * @param UploadedFile $file
     * @return bool True if safe, false if infected.
     * @throws \Exception If scanning fails/error.
     */
    public function isSafe(UploadedFile $file): bool;
}
