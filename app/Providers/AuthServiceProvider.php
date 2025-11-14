<?php

namespace App\Providers;

use App\Models\Media;
use App\Models\Project;
use App\Policies\MediaPolicy;
use App\Policies\ProjectPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Project::class => ProjectPolicy::class,
        Media::class => MediaPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}