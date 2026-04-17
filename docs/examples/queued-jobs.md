# Queued Jobs

Use this flow when you need to process Paystack operations asynchronously, handle large datasets, or retry failed operations. Laravel's queue system allows you to defer time-consuming tasks, improving web response times while ensuring reliable processing of critical payment operations.

## Why Use Queues for Payment Operations

Payment processing often involves external API calls that can be slow or unreliable. Queues provide several benefits:

- **Improved response times** — Web requests return immediately while heavy work happens in the background
- **Automatic retries** — Failed operations can be retried with configurable delays and maximum attempts
- **Failure isolation** — One failed payment verification won't block others
- **Rate limiting** — Control how many API calls you make to avoid hitting Paystack limits
- **Scalability** — Process more operations by adding queue workers
- **Reliability** — Jobs persist until completed, surviving deploys or server restarts

## Typical Use Cases

1. **Transaction verification** — Verify payments after callbacks without blocking the response
2. **Bulk data synchronization** — Sync large numbers of customers or transactions
3. **Webhook processing** — Handle webhook events asynchronously to prevent timeouts
4. **Post-payment actions** — Send notifications, fulfill orders, or update inventory after successful payments
5. **Retry failed operations** — Automatically retry API calls that failed due to network issues

## Basic Job Structure

This example shows a job that verifies a transaction with Paystack. It includes retry logic and proper error handling.

```php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Facades\Paystack;

class VerifyTransaction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = [10, 30, 60]; // seconds between retries

    public function __construct(
        public string $reference,
        public int $orderId,
    ) {}

    public function handle(): void
    {
        $response = Paystack::verifyTransaction(
            VerifyTransactionInputData::from([
                'reference' => $this->reference,
            ])
        );

        $order = \App\Models\Order::query()->find($this->orderId);

        if ($order === null) {
            return;
        }

        $order->update([
            'payment_status' => $response->transaction->status,
            'payment_verified_at' => now(),
            'payment_channel' => $response->transaction->channel,
        ]);

        if ($response->transaction->status === 'success') {
            // Dispatch follow-up job
            FulfillOrder::dispatch($order)->onQueue('orders');
        }
    }

    public function failed(\Throwable $exception): void
    {
        // Log failure for manual review
        \Illuminate\Support\Facades\Log::error('Transaction verification failed', [
            'reference' => $this->reference,
            'order_id' => $this->orderId,
            'error' => $exception->getMessage(),
        ]);

        // Notify admin
        \App\Models\Order::query()
            ->where('id', $this->orderId)
            ->update(['payment_status' => 'verification_failed']);
    }
}
```

**Understanding the job structure:**

- **`$tries = 3`** — The job will be attempted up to 3 times before being marked as failed
- **`$backoff = [10, 30, 60]`** — Delays between retries: 10 seconds after first failure, 30 after second, 60 after third
- **`SerializesModels`** — Eloquent models in constructor are serialized as IDs and re-fetched when job runs
- **`failed()` method** — Called when all retries are exhausted, use it for logging and alerting

**Retry strategy:**

Paystack API calls can fail due to network issues, rate limiting, or temporary service problems. The exponential backoff gives transient issues time to resolve while ensuring you don't overwhelm the API with immediate retries.

## Dispatching Jobs

Jobs can be dispatched from controllers, listeners, or other jobs. Here's how to dispatch a verification job from a payment callback:

```php
namespace App\Http\Controllers;

use App\Jobs\VerifyTransaction;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentCallbackController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $reference = $request->input('reference');
        $order = Order::query()
            ->where('payment_reference', $reference)
            ->firstOrFail();

        // Queue verification with priority
        VerifyTransaction::dispatch($reference, $order->getKey())
            ->onQueue('payments')
            ->delay(now()->addSeconds(5)); // Small delay for Paystack processing

        return response()->json([
            'message' => 'Payment verification queued',
            'order_id' => $order->getKey(),
        ]);
    }
}
```

**Dispatch options explained:**

- **`onQueue('payments')`** — Routes the job to a specific queue, letting you prioritize different job types
- **`delay(now()->addSeconds(5))`** — Waits 5 seconds before processing. This is useful after payment initialization to give Paystack time to process
- **`dispatch()`** — Adds the job to the queue immediately

**Why add a delay?**

Paystack may need a moment to fully process a transaction after the callback is sent. A short delay ensures the verification call returns accurate status.

## Bulk Sync Job

When you need to sync large amounts of data, chunk the work into smaller jobs that can be processed independently. This prevents memory issues and allows parallel processing.

```php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maxiviper117\Paystack\Data\Input\Transaction\ListTransactionsInputData;
use Maxiviper117\Paystack\Facades\Paystack;

class SyncTransactionsBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes

    public function __construct(
        public int $page,
        public int $perPage = 100,
    ) {}

    public function handle(): void
    {
        $response = Paystack::listTransactions(
            ListTransactionsInputData::from([
                'perPage' => $this->perPage,
                'page' => $this->page,
            ])
        );
        
        foreach ($response->transactions as $transaction) {
            \App\Models\Payment::query()->updateOrCreate(
                ['reference' => $transaction->reference],
                [
                    'amount' => $transaction->amount,
                    'status' => $transaction->status,
                    'channel' => $transaction->channel,
                    'currency' => $transaction->currency,
                    'paid_at' => $transaction->paidAt,
                    'metadata' => $transaction->metadata,
                ]
            );
        }

        // Dispatch next page if there are more
        if (count($response->transactions) === $this->perPage) {
            self::dispatch($this->page + 1, $this->perPage)
                ->onQueue('sync');
        }
    }
}

// Start bulk sync
SyncTransactionsBatch::dispatch(1)->onQueue('sync');
```

**Bulk processing strategy:**

- **Paginated API calls** — Each job fetches one page of results, keeping memory usage constant
- **Self-dispatching** — When a full page is returned, the job dispatches another job for the next page
- **`updateOrCreate`** — Prevents duplicates if jobs are retried or run multiple times
- **`timeout = 300`** — Allows 5 minutes for large pages or slow API responses

**Memory efficiency:**

Processing thousands of transactions in one request could exhaust memory. By chunking into pages and using jobs, each worker only handles a manageable subset at a time.

## Batchable Job for Refunds

When processing multiple refunds, use Laravel's batching to track progress and handle partial failures gracefully.

```php
namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maxiviper117\Paystack\Data\Input\Refund\CreateRefundInputData;
use Maxiviper117\Paystack\Facades\Paystack;

class ProcessRefund implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $transactionReference,
        public ?int $amount = null,
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $data = [
            'transaction' => $this->transactionReference,
        ];

        if ($this->amount !== null) {
            $data['amount'] = $this->amount;
        }

        $response = Paystack::createRefund(
            CreateRefundInputData::from($data)
        );

        // Update local record
        \App\Models\Refund::query()->create([
            'reference' => $response->refund->transactionReference,
            'amount' => $response->refund->amount,
            'status' => $response->refund->status,
            'processed_by' => auth()->id(),
        ]);
    }
}
```

**Batching benefits:**

Batches let you track groups of related jobs, handle failures gracefully, and run callbacks when all jobs complete. This is ideal for bulk operations like processing multiple refunds.

## Using Batches

Create and monitor batches of related jobs:

```php
use App\Jobs\ProcessRefund;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

$refunds = [
    ['reference' => 'ref_123', 'amount' => 5000],
    ['reference' => 'ref_124', 'amount' => 10000],
    ['reference' => 'ref_125', 'amount' => null], // Full refund
];

$batch = Bus::batch(
    collect($refunds)->map(fn ($refund) => new ProcessRefund(
        $refund['reference'],
        $refund['amount']
    ))
)->then(function (Batch $batch): void {
    // All jobs completed successfully
    \Illuminate\Support\Facades\Log::info('Refund batch completed', [
        'processed' => $batch->processedJobs(),
    ]);
})->catch(function (Batch $batch, \Throwable $e): void {
    // First job failure
    \Illuminate\Support\Facades\Log::error('Refund batch failed', [
        'error' => $e->getMessage(),
    ]);
})->finally(function (Batch $batch): void {
    // Always runs - good for notifications
    // Send summary email to admin
})->dispatch();

// Store batch ID for monitoring
$batchId = $batch->id;
```

**Batch callbacks:**

- **`then()`** — Runs when all jobs succeed
- **`catch()`** — Runs when any job fails (after retries are exhausted)
- **`finally()`** — Always runs, regardless of success or failure
- **`$batch->id`** — Store this to check batch status later

**Use cases for batches:**

- Processing end-of-day refunds
- Bulk subscription renewals
- Mass customer data updates
- Monthly billing runs

## Unique Job for Idempotent Operations

Some operations should only run once at a time per resource. The `ShouldBeUnique` interface prevents duplicate jobs for the same customer or transaction.

```php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncCustomerSubscription implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $uniqueFor = 3600; // 1 hour

    public function __construct(
        public string $customerCode,
    ) {}

    public function uniqueId(): string
    {
        return 'sync-subscription-' . $this->customerCode;
    }

    public function handle(): void
    {
        // Only one job per customer can run at a time
        // Prevents race conditions in subscription updates
    }
}
```

**How uniqueness works:**

- **`uniqueId()`** — Returns a unique identifier for this job type and resource
- **`$uniqueFor = 3600`** — The lock expires after 1 hour (prevents stuck locks)
- **Automatic release** — Lock is released when job succeeds, fails, or times out

**When to use unique jobs:**

- Subscription synchronization for the same customer
- Balance updates for the same account
- Any operation where running twice could cause data inconsistency

## Job Middleware for Rate Limiting

Protect Paystack's API (and your own service) by rate-limiting jobs. This middleware uses Redis to throttle job execution.

```php
namespace App\Jobs\Middleware;

use Illuminate\Support\Facades\Redis;

class RateLimited
{
    public function handle(object $job, callable $next): void
    {
        Redis::throttle('paystack-api')
            ->allow(10)
            ->every(60)
            ->then(
                function () use ($job, $next): void {
                    $next($job);
                },
                function () use ($job): void {
                    // Could not obtain lock, release back to queue
                    $job->release(10);
                }
            );
    }
}
```

```php
// Apply middleware in job
public function middleware(): array
{
    return [new \App\Jobs\Middleware\RateLimited];
}
```

**Rate limiting explained:**

- **`allow(10)->every(60)`** — Allows 10 jobs per 60 seconds across all workers
- **Redis throttle** — Uses Redis as a centralized rate limiter (works across multiple servers)
- **`$job->release(10)`** — Puts the job back on the queue for 10 seconds later if rate limited

**Why rate limit?**

Paystack has API rate limits. Exceeding them can result in temporary blocks. Rate limiting your jobs ensures you stay within acceptable usage levels.

## Testing Jobs

Jobs should be tested to ensure they handle success and failure cases correctly. Laravel provides tools for both mocking and running jobs in tests.

```php
namespace Tests\Feature\Jobs;

use App\Jobs\VerifyTransaction;
use App\Models\Order;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class VerifyTransactionTest extends TestCase
{
    public function test_job_is_dispatched_on_callback(): void
    {
        Queue::fake();

        $order = Order::factory()->create([
            'payment_reference' => 'ref_123',
        ]);

        $this->postJson('/payment/callback', [
            'reference' => 'ref_123',
        ]);

        Queue::assertPushed(VerifyTransaction::class, function ($job) use ($order) {
            return $job->reference === 'ref_123' && $job->orderId === $order->getKey();
        });
    }

    public function test_job_updates_order_on_success(): void
    {
        $order = Order::factory()->create([
            'payment_reference' => 'ref_123',
            'payment_status' => 'pending',
        ]);

        // Mock Paystack response or use a fake
        $this->mock(Paystack::class, function ($mock) {
            $mock->shouldReceive('verifyTransaction')
                ->andReturn(new \Maxiviper117\Paystack\Data\Output\Transaction\VerifyTransactionOutputData(
                    status: true,
                    message: 'Verification successful',
                    transaction: new \Maxiviper117\Paystack\Data\Output\Transaction\TransactionResourceData(
                        // ... transaction data
                    ),
                ));
        });

        $job = new VerifyTransaction('ref_123', $order->getKey());
        $job->handle();

        $this->assertDatabaseHas('orders', [
            'id' => $order->getKey(),
            'payment_status' => 'success',
        ]);
    }

    public function test_job_handles_missing_order(): void
    {
        $job = new VerifyTransaction('ref_123', 99999);
        
        // Should not throw exception
        $job->handle();
        
        $this->assertTrue(true); // Test passes if no exception thrown
    }
}
```

**Testing strategies:**

- **Mock the queue** to verify jobs are dispatched correctly without actually running them
- **Mock external APIs** to test job logic without making real Paystack calls
- **Run jobs synchronously** for integration tests of the job logic itself
- **Test failure cases** like missing records or API errors

## Queue Configuration Tips

**Separate queues by priority:**

```php
// High priority - payment verifications
VerifyTransaction::dispatch($ref, $orderId)->onQueue('payments');

// Low priority - bulk syncs
SyncTransactionsBatch::dispatch(1)->onQueue('sync');

// Critical - webhooks
ProcessWebhook::dispatch($event)->onQueue('webhooks');
```

**Configure workers per queue:**

```bash
# Process webhooks immediately
php artisan queue:work --queue=webhooks,payments,sync

# Dedicated sync worker for bulk operations
php artisan queue:work --queue=sync --sleep=3 --tries=3
```

**Monitor failed jobs:**

```bash
# View failed jobs
php artisan queue:failed

# Retry a specific failed job
php artisan queue:retry 5

# Retry all failed jobs
php artisan queue:retry all
```

## Related Pages

- [Webhook Processing](/examples/webhooks) — Process webhooks asynchronously
- [One-Time Checkout](/examples/checkout) — See payment verification in action
- [Manager and Facade Usage](/examples/manager-and-facade) — Using the Paystack facade in jobs
