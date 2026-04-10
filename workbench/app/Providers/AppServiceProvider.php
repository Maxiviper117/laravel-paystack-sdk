<?php

namespace App\Providers;

use App\Listeners\HandlePaystackWebhook;
use Illuminate\Support\ServiceProvider;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;

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
        $this->app['events']->listen(PaystackWebhookReceived::class, HandlePaystackWebhook::class);
    }
}
