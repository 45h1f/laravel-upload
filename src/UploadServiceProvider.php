<?php

namespace Ashiful\Upload;

use Illuminate\Support\ServiceProvider;

class UploadServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/upload.php', 'upload');

        $this->app->bind(\Ashiful\Upload\Contracts\UploadDriver::class, \Ashiful\Upload\Services\LocalUploadDriver::class);
        $this->app->bind(\Ashiful\Upload\Contracts\VirusScanDriver::class, \Ashiful\Upload\Services\NullVirusScanDriver::class);

        $this->app->singleton('ashiful.upload', function ($app) {
            return new \Ashiful\Upload\Services\UploadService(
                $app->make(\Ashiful\Upload\Contracts\UploadDriver::class),
                $app->make(\Ashiful\Upload\Contracts\VirusScanDriver::class)
            );
        });
    }

    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../config/upload.php' => config_path('upload.php'),
        ], 'upload-config');

        // Load Routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Load Views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'upload');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/upload'),
        ], 'upload-views');

        $this->publishes([
            __DIR__.'/../resources/js' => resource_path('js/vendor/ashiful/upload'),
        ], 'upload-assets');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Ashiful\Upload\Commands\CleanupOrphanFiles::class,
            ]);
        }
    }
}
