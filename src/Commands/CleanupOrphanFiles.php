<?php

namespace Ashiful\Upload\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupOrphanFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:cleanup {--disk= : The disk to clean} {--path=uploads/temp : The directory to clean} {--hours=24 : The max age of files in hours}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove orphan/temporary files older than X hours.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $diskName = $this->option('disk') ?: config('upload.disk', 'public');
        $path = $this->option('path');
        $hours = (int) $this->option('hours');

        $disk = Storage::disk($diskName);

        if (! $disk->exists($path)) {
            $this->info("Path [{$path}] does not exist on disk [{$diskName}].");
            return;
        }

        $files = $disk->files($path);
        $deleted = 0;
        $now = now();

        foreach ($files as $file) {
            $lastModified = $disk->lastModified($file);
            $fileTime = \Illuminate\Support\Carbon::createFromTimestamp($lastModified);

            if ($now->diffInHours($fileTime) > $hours) {
                $disk->delete($file);
                $deleted++;
            }
        }

        $this->info("Cleaned up {$deleted} files from [{$path}] older than {$hours} hours.");
    }
}
