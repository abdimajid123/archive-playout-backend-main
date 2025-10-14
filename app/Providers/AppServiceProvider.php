<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Google\Cloud\Storage\StorageClient;
use Laravel\Sanctum\Sanctum;
use App\Models\PersonalAccessToken;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure Google Cloud Storage
        if (config('filesystems.default') === 'gcs') {
            $this->configureGoogleCloudStorage();
        }

        // Force HTTPS in production
        if (app()->environment('production')) {
            \URL::forceScheme('https');
        }

        // Tell Sanctum to use custom PersonalAccessToken model
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }

    /**
     * Configure Google Cloud Storage
     */
    protected function configureGoogleCloudStorage(): void
    {
        $projectId = config('filesystems.disks.gcs.project_id');
        $keyFilePath = config('filesystems.disks.gcs.key_file');

        if ($projectId && file_exists($keyFilePath)) {
            $storage = new StorageClient([
                'projectId' => $projectId,
                'keyFilePath' => $keyFilePath,
            ]);

            // Register the storage client
            $this->app->singleton(StorageClient::class, function () use ($storage) {
                return $storage;
            });
        }
    }
}
