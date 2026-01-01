<?php

namespace Ashiful\Upload\Contracts;

use Illuminate\Http\UploadedFile;

interface UploadDriver
{
    /**
     * Handle the file upload.
     *
     * @param UploadedFile $file
     * @param string $path
     * @param array $options
     * @return string The path to the uploaded file.
     */
    public function upload(UploadedFile $file, string $path, array $options = []): string;

    /**
     * Get a temporary signed URL for the file.
     *
     * @param string $path
     * @param \DateTimeInterface|int $expiration
     * @param array $options
     * @return string
     */
    public function temporaryUrl(string $path, $expiration, array $options = []): string;
}
