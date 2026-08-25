<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;

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
        // define a url do front-end para o link enviado no email
        ResetPassword::createUrlUsing(function (User $user, string $token) {
            $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

            return "{$frontendUrl}/reset-password?token={$token}&email={$user->getEmailForPasswordReset()}";
        });
    }
}
