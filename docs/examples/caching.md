# Caching Paystack Data

Use this flow when you need to reduce redundant API calls by caching frequently accessed Paystack data. Laravel's cache system makes it easy to store plan lists, customer details, and transaction lookups with appropriate TTLs.

## Why Cache Paystack Responses

- **Reduce API calls** — Paystack rate-limits requests; caching avoids unnecessary calls
- **Faster responses** — Local cache is orders of magnitude faster than an HTTP round-trip
- **Cost efficiency** — Fewer API calls means lower bandwidth and processing costs
- **Resilience** — Cached data can serve requests even when the Paystack API is temporarily unavailable
- **User experience** — Plan selection pages load instantly when plans are cached

## Typical Caching Scenarios

1. **Plan lists** — Plans rarely change; cache them for minutes or hours
2. **Customer details** — Cache after first fetch; invalidate on update
3. **Transaction status** — Short-lived cache during verification flows
4. **Subscription details** — Cache between webhook updates
5. **Dispute status** — Cache with short TTL during active dispute resolution

## Caching Plan Lists

Plans change infrequently, making them ideal candidates for caching. This service caches the plan list and individual plans with separate keys.

```php
namespace App\Services\Billing;

use Illuminate\Support\Facades\Cache;
use Maxiviper117\Paystack\Data\Input\Plan\FetchPlanInputData;
use Maxiviper117\Paystack\Data\Input\Plan\ListPlansInputData;
use Maxiviper117\Paystack\Facades\Paystack;

class CachedPlanService
{
    /**
     * List all plans with caching.
     *
     * @return array<int, mixed>
     */
    public function listPlans(int $perPage = 50, int $page = 1): array
    {
        return Cache::remember(
            "paystack_plans_{$perPage}_{$page}",
            now()->addHours(2),
            fn (): array => Paystack::listPlans(
                ListPlansInputData::from(['perPage' => $perPage, 'page' => $page])
            )->plans
        );
    }

    /**
     * Fetch a single plan by code with caching.
     */
    public function fetchPlan(string $planCode): mixed
    {
        return Cache::remember(
            "paystack_plan_{$planCode}",
            now()->addHours(4),
            fn (): mixed => Paystack::fetchPlan(
                FetchPlanInputData::from(['idOrCode' => $planCode])
            )->plan
        );
    }

    /**
     * Forget cached plan data after an update.
     */
    public function forgetPlan(string $planCode): void
    {
        Cache::forget("paystack_plan_{$planCode}");
        Cache::forget('paystack_plans_50_1'); // Adjust key pattern as needed
    }
}
```

**Using in a controller:**

```php
namespace App\Http\Controllers;

use App\Services\Billing\CachedPlanService;

class PlanController extends Controller
{
    public function __construct(
        private CachedPlanService $plans,
    ) {}

    public function index()
    {
        return response()->json([
            'plans' => $this->plans->listPlans(),
        ]);
    }

    public function show(string $planCode)
    {
        return response()->json([
            'plan' => $this->plans->fetchPlan($planCode),
        ]);
    }
}
```

**Cache invalidation after plan updates:**

```php
use App\Services\Billing\CachedPlanService;
use Maxiviper117\Paystack\Data\Input\Plan\UpdatePlanInputData;
use Maxiviper117\Paystack\Facades\Paystack;

$updated = Paystack::updatePlan(UpdatePlanInputData::from([
    'idOrCode' => 'PLN_test123',
    'name' => 'Updated Plan Name',
    'amount' => 10000,
]));

app(CachedPlanService::class)->forgetPlan('PLN_test123');
```

## Caching Customer Details

Customer data changes when users update their profile. Cache with a moderate TTL and invalidate on updates.

```php
namespace App\Services\Billing;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Maxiviper117\Paystack\Data\Input\Customer\FetchCustomerInputData;
use Maxiviper117\Paystack\Facades\Paystack;

class CachedCustomerService
{
    /**
     * Fetch a customer by code with caching.
     */
    public function fetchCustomer(string $customerCode): mixed
    {
        return Cache::remember(
            "paystack_customer_{$customerCode}",
            now()->addMinutes(30),
            fn (): mixed => Paystack::fetchCustomer(
                FetchCustomerInputData::from(['codeOrEmail' => $customerCode])
            )->customer
        );
    }

    /**
     * Forget cached customer data after an update.
     */
    public function forgetCustomer(string $customerCode): void
    {
        Cache::forget("paystack_customer_{$customerCode}");
    }
}
```

**Invalidation from a webhook listener:**

```php
use App\Services\Billing\CachedCustomerService;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeSuccessWebhookData;
use Maxiviper117\Paystack\Listeners\PaystackWebhookHandler;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;

class HandlePaystackWebhook
{
    public function __construct(
        private CachedCustomerService $customers,
    ) {}

    public function handle(PaystackWebhookReceived $event): void
    {
        (new PaystackWebhookHandler)
            ->onChargeSuccess(function (ChargeSuccessWebhookData $typed): void {
                // Invalidate customer cache when a charge succeeds
                if ($typed->customer?->customerCode !== null) {
                    $this->customers->forgetCustomer($typed->customer->customerCode);
                }
            })
            ->handle($event);
    }
}
```

## Caching Transaction Verification

Transaction verification results should be cached briefly to prevent redundant verification calls during the same session. This is especially useful when the callback URL might be hit multiple times.

```php
namespace App\Services\Billing;

use Illuminate\Support\Facades\Cache;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Facades\Paystack;

class VerifyTransactionWithCache
{
    /**
     * Verify a transaction, caching the result for 5 minutes.
     *
     * This prevents redundant API calls when the same reference
     * is verified multiple times in quick succession (e.g., double
     * callback from Paystack).
     */
    public function verify(string $reference): mixed
    {
        return Cache::remember(
            "paystack_verify_{$reference}",
            now()->addMinutes(5),
            fn (): mixed => Paystack::verifyTransaction(
                VerifyTransactionInputData::from(['reference' => $reference])
            )->transaction
        );
    }

    /**
     * Force a fresh verification, bypassing cache.
     */
    public function verifyFresh(string $reference): mixed
    {
        Cache::forget("paystack_verify_{$reference}");

        return $this->verify($reference);
    }
}
```

**Why a short TTL for verification:**

- Transaction status can change (e.g., from pending to success)
- A 5-minute cache prevents redundant calls during the same checkout session
- After 5 minutes, a fresh verification call ensures accurate status
- The cache key includes the reference, so different transactions don't collide

## Caching with the Billing Layer

When using the `Billable` trait, the local mirror tables already act as a cache. You can combine them with Laravel's cache for frequently accessed data:

```php
namespace App\Services\Billing;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Maxiviper117\Paystack\Facades\Paystack;

class BillablePlanService
{
    /**
     * Get available plans, preferring local mirror data.
     *
     * Uses the local PaystackPlan model as primary source
     * and falls back to the API with caching.
     */
    public function availablePlans(): array
    {
        return Cache::remember(
            'paystack_available_plans',
            now()->addHours(1),
            function (): array {
                // Try local mirror first if billing migrations are published
                $localPlans = \Maxiviper117\Paystack\Models\PaystackPlan::query()->get();

                if ($localPlans->isNotEmpty()) {
                    return $localPlans->toArray();
                }

                // Fall back to API
                return Paystack::listPlans(
                    \Maxiviper117\Paystack\Data\Input\Plan\ListPlansInputData::from(['perPage' => 100])
                )->plans;
            }
        );
    }
}
```

## Cache Tags for Bulk Invalidation

If your cache driver supports tags (Redis, database), use tags to invalidate all Paystack-related caches at once:

```php
namespace App\Services\Billing;

use Illuminate\Support\Facades\Cache;
use Maxiviper117\Paystack\Data\Input\Plan\ListPlansInputData;
use Maxiviper117\Paystack\Facades\Paystack;

class CachedPlanService
{
    public function listPlans(int $perPage = 50, int $page = 1): array
    {
        return Cache::tags(['paystack', 'plans'])->remember(
            "paystack_plans_{$perPage}_{$page}",
            now()->addHours(2),
            fn (): array => Paystack::listPlans(
                ListPlansInputData::from(['perPage' => $perPage, 'page' => $page])
            )->plans
        );
    }

    /**
     * Flush all Paystack plan caches.
     */
    public function flushPlanCache(): void
    {
        Cache::tags(['paystack', 'plans'])->flush();
    }
}
```

**When to flush:**

- After creating, updating, or deleting a plan
- After receiving a `subscription.create` webhook (if plans affect subscription display)
- On an admin action that modifies plan data

## Cache Key Conventions

Use consistent, namespaced cache keys to avoid collisions and make debugging easier:

| Data                     | Key Pattern                                | TTL        |
| ------------------------ | ------------------------------------------ | ---------- |
| Plan list                | `paystack_plans_{perPage}_{page}`          | 2 hours    |
| Single plan              | `paystack_plan_{planCode}`                 | 4 hours    |
| Customer                 | `paystack_customer_{customerCode}`         | 30 minutes |
| Transaction verification | `paystack_verify_{reference}`              | 5 minutes  |
| Subscription             | `paystack_subscription_{subscriptionCode}` | 15 minutes |

## Related pages

- [Manager and Facade Usage](/examples/manager-and-facade) — SDK methods used in caching services
- [Webhook Processing](/examples/webhooks) — Invalidate caches when webhook events arrive
- [Queued Jobs](/examples/queued-jobs) — Cache warming and invalidation in background jobs
- [Optional Billing Layer](/examples/billing-layer) — Local mirror tables as a persistent cache