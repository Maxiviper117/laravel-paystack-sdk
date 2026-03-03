<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
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
        $this->app['events']->listen(PaystackWebhookReceived::class, function (PaystackWebhookReceived $event): void {
            Cache::put('paystack:last-webhook-event', [
                'event' => $event->event->event,
                'resource_type' => $event->event->resourceType,
                'id' => $event->event->id,
                'occurred_at' => $event->event->occurredAt,
                'data' => $event->event->data,
            ]);
        });
    }
}
