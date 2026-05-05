<?php

namespace App\Providers;

use App\Models\AhspBasePrice;
use App\Models\Project;
use App\Observers\AhspBasePriceObserver;
use App\Observers\ProjectObserver;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        // Register observers for cache invalidation
        Project::observe(ProjectObserver::class);

        // Register observer for AHSP auto-sync to materials
        AhspBasePrice::observe(AhspBasePriceObserver::class);

        // Register events
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\ProgressReportApproved::class,
            \App\Listeners\CreateDraftMaterialUsage::class
        );

        // Scramble Authentication Configuration
        if (class_exists(\Dedoc\Scramble\Scramble::class)) {
            \Dedoc\Scramble\Scramble::afterOpenApiGenerated(function (\Dedoc\Scramble\Support\Generator\OpenApi $openApi) {
                $openApi->secure(
                    \Dedoc\Scramble\Support\Generator\SecurityScheme::http('bearer')
                );
            });
        }

        // Define gate for Scramble API Documentation access
        Gate::define('viewApiDocs', function (?User $user) {
            // Allow anyone to view API documentation as requested
            return true;
        });
    }
}

