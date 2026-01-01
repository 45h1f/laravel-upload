<?php

namespace Ashiful\Upload\Services;

use Ashiful\Upload\Contracts\UploadDriver;
use Ashiful\Upload\Contracts\VirusScanDriver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;

class UploadService
{
    protected UploadDriver $uploader;
    protected VirusScanDriver $scanner;

    public function __construct(UploadDriver $uploader, VirusScanDriver $scanner)
    {
        $this->uploader = $uploader;
        $this->scanner = $scanner;
    }

    /**
     * Upload a file with validation and virus scanning.
     *
     * @param UploadedFile $file
     * @param string $path
     * @param array $rules Validation rules (e.g., ['mimes:jpg,png', 'max:1024'])
     * @return string Path of the uploaded file
     * @throws ValidationException
     * @throws \Exception
     */
    public function upload(UploadedFile $file, string $path = 'uploads', array $rules = []): string
    {
        // 1. Validate File
        $this->validate($file, $rules);

        // 2. Virus Scan
        if (Config::get('upload.virus_scan', false)) {
            if (! $this->scanner->isSafe($file)) {
                throw ValidationException::withMessages(['file' => 'The file contains a virus.']);
            }
        }

        // 3. Upload
        return $this->uploader->upload($file, $path);
    }

    /**
     * Get a temporary signed URL for a file.
     *
     * @param string $path
     * @param \DateTimeInterface|int $expiration minutes or DateTime
     * @return string
     */
    public function getSignedUrl(string $path, $expiration = 60): string
    {
        if (is_int($expiration)) {
            $expiration = now()->addMinutes($expiration);
        }

        return $this->uploader->temporaryUrl($path, $expiration);
    }

    protected function validate(UploadedFile $file, array $rules): void
    {
        if (empty($rules)) {
            return;
        }

        $validator = validator(['file' => $file], ['file' => $rules]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
