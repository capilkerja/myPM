<?php

namespace App\Providers;

use App\Events\ProjectMemberAttached;
use App\Events\ProjectMemberDetached;
use App\Listeners\SendProjectAssignmentNotification;
use App\Listeners\SendProjectRemovalNotification;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ProjectMemberAttached::class => [
            SendProjectAssignmentNotification::class,
        ],
        ProjectMemberDetached::class => [
            SendProjectRemovalNotification::class,
        ],
    ];
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
