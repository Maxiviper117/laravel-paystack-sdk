# Scheduled Tasks

Use this flow when you need to run Paystack-related operations on a schedule. Laravel's task scheduling provides a clean, cron-free way to automate reconciliation, cleanup, and maintenance tasks.

## Why Use Scheduled Tasks for Paystack Operations

- **Automation** — Run reconciliation and cleanup without manual intervention
- **Consistency** — Daily/weekly/monthly tasks run at predictable times
- **Reliability** — Laravel's scheduler handles failures and retries
- **Visibility** — Task output can be logged and monitored
- **No cron sprawl** — All scheduling logic lives in your application code

## Typical Scheduled Task Scenarios

1. **Daily reconciliation** — Verify pending transactions that may have been missed
2. **Webhook cleanup** — Purge old webhook records to manage database size
3. **Subscription renewal reminders** — Notify users before their subscription renews
4. **Failed payment retry** — Re-process payments that failed due to temporary issues
5. **Reporting** — Generate daily/weekly revenue reports
6. **Data sync** — Keep local mirror tables in sync with Paystack

## Setting Up the Scheduler

Add this cron entry to run Laravel's scheduler every minute:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Define tasks in `routes/console.php` (Laravel 11/12) or `app/Console/Kernel.php` (older versions).

## Daily Reconciliation Task

Verify pending transactions that haven't been updated in 24 hours. This catches webhooks that may have been missed.

```php
<?php

use App\Jobs\VerifyPendingTransactions;
use Illuminate\Support\Facades\Schedule;

// In routes/console.php (Laravel 11/12)

Schedule::job(new VerifyPendingTransactions)
    ->daily()
    ->at('02:00')
    ->name('reconcile-pending-payments')
    ->onOneServer() // Prevent duplicate runs in multi-server setups
    ->withoutOverlapping(3600); // Lock for 1 hour
```

```php
<?php

namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Facades\Paystack;

class VerifyPendingTransactions implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        $pendingPayments = Payment::query()
            ->pending()
            ->olderThanDays(1)
            ->whereNull('verified_at')
            ->get();

        if ($pendingPayments->isEmpty()) {
            Log::info('No pending payments to reconcile');

            return;
        }

        Log::info("Reconciling {$pendingPayments->count()} pending payments");

        $verified = 0;
        $failed = 0;

        foreach ($pendingPayments as $payment) {
            try {
                $response = Paystack::verifyTransaction(
                    VerifyTransactionInputData::from([
                        'reference' => $payment->reference,
                    ])
                );

                $payment->update([
                    'status' => $response->transaction->status,
                    'verified_at' => now(),
                    'paid_at' => $response->transaction->paidAt,
                ]);

                if ($response->transaction->status === 'success') {
                    $verified++;
                }
            } catch (\Exception $e) {
                Log::error('Failed to verify payment during reconciliation', [
                    'reference' => $payment->reference,
                    'error' => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        Log::info("Reconciliation complete: {$verified} verified, {$failed} failed");
    }
}
```

**Why run at 2 AM:**

- Low traffic period minimizes impact on application performance
- Paystack's daily settlement is typically complete by this time
- Gives webhooks a full 24 hours to arrive before manual verification

## Webhook Cleanup Task

The package stores webhook calls in `paystack_webhook_calls`. Clean up old records to prevent database bloat:

```php
<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('paystack:cleanup-webhooks')
    ->weekly()
    ->sundays()
    ->at('03:00')
    ->onOneServer();
```

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maxiviper117\Paystack\Models\PaystackWebhookCall;

class CleanupWebhooks extends Command
{
    protected $signature = 'paystack:cleanup-webhooks
                            {--days=30 : Delete webhooks older than this many days}';

    protected $description = 'Clean up old Paystack webhook records';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $count = PaystackWebhookCall::query()
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info("Deleted {$count} webhook records older than {$days} days.");

        return self::SUCCESS;
    }
}
```

**Retention considerations:**

- Keep webhooks for at least 30 days for debugging and audit purposes
- Some jurisdictions require longer retention for financial records
- Consider archiving to cold storage instead of deletion for compliance

## Subscription Renewal Reminders

Notify users before their subscription renews to reduce involuntary churn:

```php
<?php

use App\Jobs\SendRenewalReminders;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new SendRenewalReminders(daysBefore: 3))
    ->daily()
    ->at('09:00')
    ->name('subscription-renewal-reminders')
    ->onOneServer();
```

```php
<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Notifications\SubscriptionRenewingSoon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendRenewalReminders implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private int $daysBefore = 3,
    ) {}

    public function handle(): void
    {
        $targetDate = now()->addDays($this->daysBefore)->startOfDay();

        $subscriptions = Subscription::query()
            ->active()
            ->whereDate('next_payment_date', $targetDate)
            ->with('user')
            ->get();

        foreach ($subscriptions as $subscription) {
            $subscription->user->notify(
                new SubscriptionRenewingSoon(
                    planName: $subscription->plan_name,
                    amount: $subscription->amount,
                    renewalDate: $subscription->next_payment_date,
                )
            );
        }
    }
}
```

## Failed Payment Retry

Some payment failures are temporary (network issues, bank downtime). Retry failed payments from the last 24 hours:

```php
<?php

use App\Jobs\RetryFailedPayments;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new RetryFailedPayments)
    ->daily()
    ->at('04:00')
    ->onOneServer();
```

```php
<?php

namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;
use Maxiviper117\Paystack\Facades\Paystack;

class RetryFailedPayments implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        $failedPayments = Payment::query()
            ->where('status', 'failed')
            ->where('retry_count', '<', 3)
            ->where('created_at', '>', now()->subDay())
            ->whereNull('retried_at')
            ->get();

        foreach ($failedPayments as $payment) {
            try {
                // Re-initialize the transaction with a new reference
                $response = Paystack::initializeTransaction(
                    InitializeTransactionInputData::from([
                        'email' => $payment->user->email,
                        'amount' => $payment->amount,
                        'reference' => $payment->reference . '_retry_' . time(),
                        'metadata' => [
                            'original_reference' => $payment->reference,
                            'retry_count' => $payment->retry_count + 1,
                        ],
                    ])
                );

                $payment->update([
                    'retry_count' => $payment->retry_count + 1,
                    'retried_at' => now(),
                    'retry_reference' => $response->reference,
                ]);

                // Notify user of retry with new payment link
                $payment->user->notify(
                    new \App\Notifications\PaymentRetryAvailable($response->authorizationUrl)
                );
            } catch (\Exception $e) {
                Log::error('Failed to retry payment', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
```

## Daily Revenue Report

Generate and email a daily revenue summary to stakeholders:

```php
<?php

use App\Jobs\GenerateDailyRevenueReport;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new GenerateDailyRevenueReport)
    ->daily()
    ->at('08:00')
    ->name('daily-revenue-report')
    ->onOneServer();
```

```php
<?php

namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class GenerateDailyRevenueReport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        $yesterday = now()->subDay();

        $stats = [
            'total_revenue' => Payment::query()
                ->paid()
                ->whereDate('paid_at', $yesterday)
                ->sum('amount'),
            'transaction_count' => Payment::query()
                ->paid()
                ->whereDate('paid_at', $yesterday)
                ->count(),
            'average_transaction' => Payment::query()
                ->paid()
                ->whereDate('paid_at', $yesterday)
                ->avg('amount'),
            'failed_count' => Payment::query()
                ->where('status', 'failed')
                ->whereDate('created_at', $yesterday)
                ->count(),
            'pending_count' => Payment::query()
                ->pending()
                ->count(),
        ];

        Mail::to('finance@example.com')
            ->send(new \App\Mail\DailyRevenueReport($stats, $yesterday));
    }
}
```

## Billing Layer Sync

If you use the optional billing layer, schedule periodic syncs to catch any drift:

```php
<?php

use App\Jobs\SyncBillingLayer;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new SyncBillingLayer)
    ->weekly()
    ->mondays()
    ->at('01:00')
    ->onOneServer();
```

```php
<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maxiviper117\Paystack\Facades\Paystack;

class SyncBillingLayer implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        // Sync customers for users with Paystack records
        User::query()
            ->has('paystackCustomer')
            ->chunk(100, function ($users): void {
                foreach ($users as $user) {
                    try {
                        Paystack::syncBillableCustomer($user);
                    } catch (\Exception $e) {
                        // Log but continue with other users
                        report($e);
                    }
                }
            });
    }
}
```

## Task Frequency Options

Laravel's scheduler supports various frequencies:

```php
<?php

use Illuminate\Support\Facades\Schedule;

// Every minute
Schedule::command('paystack:ping')->everyMinute();

// Every 5 minutes
Schedule::command('paystack:check-status')->everyFiveMinutes();

// Hourly
Schedule::command('paystack:hourly-report')->hourly();

// Daily at specific time
Schedule::command('paystack:daily-reconcile')->dailyAt('02:00');

// Twice daily
Schedule::command('paystack:midday-report')->twiceDaily(9, 15);

// Weekly
Schedule::command('paystack:weekly-report')->weekly()->mondays()->at('08:00');

// Monthly
Schedule::command('paystack:monthly-report')->monthlyOn(1, '08:00');

// Weekdays only
Schedule::command('paystack:business-check')->weekdays()->at('09:00');

// Custom cron expression
Schedule::command('paystack:custom')->cron('0 */6 * * *'); // Every 6 hours
```

## Monitoring Scheduled Tasks

Log task output and send notifications on failure:

```php
<?php

use Illuminate\Support\Facades\Schedule;

Schedule::job(new VerifyPendingTransactions)
    ->daily()
    ->at('02:00')
    ->onOneServer()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/reconciliation.log'))
    ->emailOutputOnFailure('devops@example.com');
```

## Testing Scheduled Tasks

Test task logic without waiting for the scheduler:

```php
use App\Jobs\VerifyPendingTransactions;

test('reconciliation job verifies pending payments', function (): void {
    Payment::factory()->create([
        'status' => 'pending',
        'created_at' => now()->subDays(2),
    ]);

    Paystack::shouldReceive('verifyTransaction')
        ->once()
        ->andReturn(/* success response */);

    (new VerifyPendingTransactions)->handle();

    expect(Payment::query()->first()->status)->toBe('success');
});
```

## Related pages

- [Artisan Commands](/examples/artisan-commands) — Manual commands that complement scheduled tasks
- [Queued Jobs](/examples/queued-jobs) — Background processing for scheduled task work
- [Optional Billing Layer](/examples/billing-layer) — Sync tasks for local mirror tables
- [Payment Notifications](/examples/notifications) — Email alerts from scheduled tasks