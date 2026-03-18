<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Quote;
use App\Models\User;
use App\Observers\ModelActivityObserver;
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
        Password::defaults(function () {
            return Password::min(8)
                ->letters()
                ->numbers();
        });

        // Observer global pour tracer les modifications sur les principaux modèles métier.
        Patient::observe(ModelActivityObserver::class);
        Appointment::observe(ModelActivityObserver::class);
        Invoice::observe(ModelActivityObserver::class);
        Quote::observe(ModelActivityObserver::class);
        User::observe(ModelActivityObserver::class);
    }
}
