<?php

namespace App\Providers;

use App\Models\Note;
use App\Policies\NotePolicy;
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
        \Illuminate\Pagination\Paginator::useBootstrapFour();
        \Carbon\Carbon::setLocale(config('app.locale', 'ar'));

        Gate::policy(Note::class, NotePolicy::class);
    }
}
