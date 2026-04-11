# Query Scopes

Use this flow when you need reusable filters for your payment-related Eloquent models. Laravel's query scopes provide a clean, chainable way to filter transactions, subscriptions, and refunds by common criteria.

## Why Use Query Scopes for Payment Data

- **Reusability** — Define once, use everywhere in your application
- **Readability** — `Payment::paid()->thisMonth()` is self-documenting
- **Consistency** — Same filtering logic across controllers, reports, and exports
- **Composability** — Chain multiple scopes for complex queries
- **Testability** — Scopes are easy to test in isolation

## Typical Scope Scenarios

1. **Status filtering** — `paid()`, `pending()`, `failed()`, `refunded()`
2. **Date ranges** — `thisMonth()`, `lastMonth()`, `betweenDates()`
3. **Amount ranges** — `aboveAmount()`, `belowAmount()`, `inRange()`
4. **User relationships** — `forUser()`, `forBillable()`
5. **Channel filtering** — `byCard()`, `byBankTransfer()`

## Basic Status Scopes

Define scopes for common payment statuses to simplify filtering:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'success');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->whereIn('status', ['failed', 'abandoned']);
    }

    public function scopeRefunded(Builder $query): Builder
    {
        return $query->where('status', 'refunded');
    }

    public function scopeDisputed(Builder $query): Builder
    {
        return $query->where('disputed', true);
    }
}
```

**Using status scopes:**

```php
// Get all paid payments for a user
$paidPayments = Payment::query()
    ->forUser($user)
    ->paid()
    ->get();

// Count pending payments
$pendingCount = Payment::query()
    ->pending()
    ->count();

// Get disputed payments that need attention
$disputes = Payment::query()
    ->disputed()
    ->with('user')
    ->latest()
    ->get();
```

## Date Range Scopes

Scopes for common date filtering in payment reporting:

```php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    // ... status scopes ...

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    public function scopeLastMonth(Builder $query): Builder
    {
        return $query->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year);
    }

    public function scopeThisYear(Builder $query): Builder
    {
        return $query->whereYear('created_at', now()->year);
    }

    public function scopeBetweenDates(
        Builder $query,
        Carbon $start,
        Carbon $end
    ): Builder {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    public function scopeOlderThanDays(Builder $query, int $days): Builder
    {
        return $query->where('created_at', '<', now()->subDays($days));
    }
}
```

**Using date scopes for reports:**

```php
// Today's revenue
$todayRevenue = Payment::query()
    ->paid()
    ->today()
    ->sum('amount');

// This month's transaction count
$thisMonthCount = Payment::query()
    ->thisMonth()
    ->count();

// Custom date range report
$report = Payment::query()
    ->paid()
    ->betweenDates(
        now()->subDays(30),
        now()
    )
    ->with('user')
    ->get();
```

## Amount Range Scopes

Filter by transaction amounts for analytics and fraud detection:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    // ... other scopes ...

    public function scopeAboveAmount(Builder $query, int $amountInKobo): Builder
    {
        return $query->where('amount', '>', $amountInKobo);
    }

    public function scopeBelowAmount(Builder $query, int $amountInKobo): Builder
    {
        return $query->where('amount', '<', $amountInKobo);
    }

    public function scopeInAmountRange(
        Builder $query,
        int $min,
        int $max
    ): Builder {
        return $query->whereBetween('amount', [$min, $max]);
    }
}
```

**Using amount scopes:**

```php
// High-value transactions for manual review
$highValue = Payment::query()
    ->paid()
    ->aboveAmount(100000) // 1000 NGN
    ->latest()
    ->get();

// Micro-transactions (possible test data)
$micro = Payment::query()
    ->belowAmount(1000) // 10 NGN
    ->get();

// Transactions in typical range
$typical = Payment::query()
    ->inAmountRange(5000, 50000) // 50-500 NGN
    ->count();
```

## Relationship Scopes

Scopes that filter by related models:

```php
namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    // ... other scopes ...

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeForUserId(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForEmail(Builder $query, string $email): Builder
    {
        return $query->whereHas('user', function ($q) use ($email): void {
            $q->where('email', $email);
        });
    }
}
```

**Using relationship scopes:**

```php
// Get user's payment history
$history = Payment::query()
    ->forUser(auth()->user())
    ->paid()
    ->latest()
    ->paginate(20);

// Find payments by email (for support inquiries)
$payments = Payment::query()
    ->forEmail('customer@example.com')
    ->thisMonth()
    ->get();
```

## Channel and Metadata Scopes

Filter by payment channel or metadata fields:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    // ... other scopes ...

    public function scopeByCard(Builder $query): Builder
    {
        return $query->where('channel', 'card');
    }

    public function scopeByBankTransfer(Builder $query): Builder
    {
        return $query->where('channel', 'bank_transfer');
    }

    public function scopeByChannel(Builder $query, string $channel): Builder
    {
        return $query->where('channel', $channel);
    }

    public function scopeWithMetadata(Builder $query, string $key, mixed $value): Builder
    {
        return $query->whereJsonContains('metadata->' . $key, $value);
    }
}
```

**Using channel and metadata scopes:**

```php
// Card vs bank transfer split
$cardTotal = Payment::query()->paid()->byCard()->sum('amount');
$bankTotal = Payment::query()->paid()->byBankTransfer()->sum('amount');

// Find payments from a specific campaign
$campaignPayments = Payment::query()
    ->paid()
    ->withMetadata('campaign_id', 'summer_sale_2025')
    ->thisMonth()
    ->get();
```

## Subscription Scopes

Scopes for subscription models:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeExpiringSoon(Builder $query, int $days = 7): Builder
    {
        return $query->where('next_payment_date', '<=', now()->addDays($days))
            ->where('next_payment_date', '>=', now());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('next_payment_date', '<', now());
    }

    public function scopeOnPlan(Builder $query, string $planCode): Builder
    {
        return $query->where('plan_code', $planCode);
    }

    public function scopeForPlan(Builder $query, string $planCode): Builder
    {
        return $this->scopeOnPlan($query, $planCode);
    }
}
```

**Using subscription scopes:**

```php
// Active subscriptions expiring this week
$renewals = Subscription::query()
    ->active()
    ->expiringSoon(7)
    ->with('user')
    ->get();

// Subscriptions on a specific plan
$planSubscribers = Subscription::query()
    ->active()
    ->onPlan('PLN_premium_monthly')
    ->count();

// Expired subscriptions to clean up
$cleanup = Subscription::query()
    ->expired()
    ->olderThanDays(30)
    ->delete();
```

## Chaining Scopes for Complex Queries

The real power of scopes comes from chaining them together:

```php
// Monthly revenue report by channel
$report = [
    'card' => Payment::query()
        ->paid()
        ->thisMonth()
        ->byCard()
        ->sum('amount'),
    'bank_transfer' => Payment::query()
        ->paid()
        ->thisMonth()
        ->byBankTransfer()
        ->sum('amount'),
    'total' => Payment::query()
        ->paid()
        ->thisMonth()
        ->sum('amount'),
];

// High-value pending payments (possible fraud)
$suspicious = Payment::query()
    ->pending()
    ->olderThanDays(1)
    ->aboveAmount(100000)
    ->get();

// User's spending this quarter
$spending = Payment::query()
    ->forUser(auth()->user())
    ->paid()
    ->betweenDates(
        now()->firstOfQuarter(),
        now()->lastOfQuarter()
    )
    ->sum('amount');
```

## Dynamic Scopes

Scopes that accept parameters for flexible filtering:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    // ... other scopes ...

    public function scopeFilterByStatus(
        Builder $query,
        ?string $status
    ): Builder {
        return $status !== null
            ? $query->where('status', $status)
            : $query;
    }

    public function scopeFilterByDateRange(
        Builder $query,
        ?string $from,
        ?string $to
    ): Builder {
        if ($from !== null) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to !== null) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if ($term === null || $term === '') {
            return $query;
        }

        return $query->where(function ($q) use ($term): void {
            $q->where('reference', 'like', "%{$term}%")
                ->orWhereHas('user', function ($uq) use ($term): void {
                    $uq->where('email', 'like', "%{$term}%")
                        ->orWhere('name', 'like', "%{$term}%");
                });
        });
    }
}
```

**Using dynamic scopes in a controller:**

```php
namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $payments = Payment::query()
            ->forUser(auth()->user())
            ->filterByStatus($request->input('status'))
            ->filterByDateRange(
                $request->input('from'),
                $request->input('to')
            )
            ->search($request->input('search'))
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json($payments);
    }
}
```

## Testing Scopes

Test scopes in isolation to ensure they filter correctly:

```php
use App\Models\Payment;
use App\Models\User;

test('paid scope filters by success status', function (): void {
    Payment::factory()->create(['status' => 'success']);
    Payment::factory()->create(['status' => 'pending']);
    Payment::factory()->create(['status' => 'failed']);

    expect(Payment::query()->paid()->count())->toBe(1);
});

test('this month scope filters correctly', function (): void {
    Payment::factory()->create(['created_at' => now()]);
    Payment::factory()->create(['created_at' => now()->subMonth()]);

    expect(Payment::query()->thisMonth()->count())->toBe(1);
});

test('scopes can be chained', function (): void {
    $user = User::factory()->create();

    // Paid this month for user
    Payment::factory()->create([
        'user_id' => $user->id,
        'status' => 'success',
        'created_at' => now(),
        'amount' => 50000,
    ]);

    // Paid last month (should not match)
    Payment::factory()->create([
        'user_id' => $user->id,
        'status' => 'success',
        'created_at' => now()->subMonth(),
    ]);

    // Pending this month (should not match)
    Payment::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
        'created_at' => now(),
    ]);

    $result = Payment::query()
        ->forUser($user)
        ->paid()
        ->thisMonth()
        ->aboveAmount(10000)
        ->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->amount)->toBe(50000);
});
```

## Related pages

- [API Resources](/examples/api-resources) — Transform scoped query results for API responses
- [Export/Reports](/examples/export-reports) — Use scopes to filter data for CSV/Excel exports
- [Manager and Facade Usage](/examples/manager-and-facade) — SDK methods that work alongside local queries