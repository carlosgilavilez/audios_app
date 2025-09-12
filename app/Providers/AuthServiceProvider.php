<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Autor;
use App\Models\Serie;
use App\Models\Audio;
use App\Policies\UserPolicy;
use App\Policies\AutorPolicy;
use App\Policies\SeriePolicy;
use App\Policies\AudioPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Autor::class => AutorPolicy::class,
        Serie::class => SeriePolicy::class,
        Audio::class => AudioPolicy::class,
        ActivityLog::class => ActivityLogPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        \Log::info('AuthServiceProvider cargado correctamente');

        $this->registerPolicies();

        Gate::define('admin', fn($user) => $user->role === 'admin');
        Gate::define('editor', fn($user) => in_array($user->role, ['editor','admin']));
    }
}
