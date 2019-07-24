<?php

namespace App\Providers;

use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {cprs
        $this->registerPolicies();

        Route::middleware('cors')->group(function() {
            Passport::routes();
        });
        // Passport::routes();


        // Passport::tokensExpireIn(now()->addMinutes(30));
        Passport::tokensExpireIn(now()->addSeconds(10));

        Passport::tokensCan([
            'admin' => 'Do anything',
            'coordinator' => 'Limit in some actions',
            'foremen' => 'Limit alot action'
        ]);

    }
}
