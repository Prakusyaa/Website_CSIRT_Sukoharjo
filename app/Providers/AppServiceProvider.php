<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Severity;
use App\Observers\CategoryObserver;
use App\Observers\RoleObserver;
use App\Observers\SeverityObserver;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        Vite::prefetch(concurrency: 3);

        $this->configureDefaults();

        // Register central observer on critical entities automating Audit traces
        \App\Models\Report::observe(\App\Observers\AuditLogObserver::class);
        // Security / Audit Logging Observers
        \App\Models\User::observe(\App\Observers\AuditLogObserver::class);
        \App\Models\Role::observe(\App\Observers\AuditLogObserver::class);
        \App\Models\Category::observe(\App\Observers\AuditLogObserver::class);
        \App\Models\Severity::observe(\App\Observers\AuditLogObserver::class);
        \App\Models\Attachment::observe(\App\Observers\AuditLogObserver::class);

        // Reference Data Cache Invalidation
        Category::observe(CategoryObserver::class);
        Severity::observe(SeverityObserver::class);
        \App\Models\Role::observe(RoleObserver::class);

        Gate::before(function ($user, $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
