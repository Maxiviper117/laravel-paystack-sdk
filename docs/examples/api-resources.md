# API Resources

Use this flow when you need to transform Paystack data into consistent JSON responses for your API. Laravel's API Resources provide a transformation layer between your internal data and the JSON your clients consume.

## Why Use API Resources for Paystack Data

- **Consistent responses** — Shape every API response the same way regardless of internal data changes
- **Field control** — Hide sensitive data, format amounts, and include only what clients need
- **Relationship loading** — Conditionally include related resources without over-fetching
- **Version stability** — Change internal structures without breaking API contracts
- **Documentation** — Resources serve as a clear contract of what your API returns

## Typical Resource Scenarios

1. **Transaction responses** — Format amounts from kobo to currency, hide internal IDs
2. **Customer profiles** — Expose only safe customer fields, not raw Paystack data
3. **Subscription details** — Include plan information with subscription data
4. **Refund summaries** — Present refund status with human-readable timelines
5. **Paginated lists** — Consistent pagination metadata across all list endpoints

## Transaction Resource

Paystack stores amounts in kobo (smallest currency unit). This resource converts amounts to human-readable format and controls which fields are exposed.

```php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'reference' => $this->reference,
            'amount' => $this->amount,
            'amount_display' => $this->formatAmount($this->amount, $this->currency),
            'currency' => $this->currency,
            'status' => $this->status,
            'channel' => $this->channel,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'metadata' => $this->when(
                $request->boolean('include_metadata'),
                $this->metadata
            ),
        ];
    }

    private function formatAmount(int $amountInKobo, string $currency): string
    {
        $amount = $amountInKobo / 100;

        return match (strtoupper($currency)) {
            'NGN' => '₦' . number_format($amount, 2),
            'USD' => '$' . number_format($amount, 2),
            'GHS' => 'GH₵' . number_format($amount, 2),
            'ZAR' => 'R' . number_format($amount, 2),
            default => $currency . ' ' . number_format($amount, 2),
        };
    }
}
```

**Using in a controller:**

```php
namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    public function show(Payment $payment): JsonResponse
    {
        return response()->json([
            'data' => new TransactionResource($payment),
        ]);
    }
}
```

**Response output:**

```json
{
    "data": {
        "reference": "ref_abc123",
        "amount": 50000,
        "amount_display": "₦500.00",
        "currency": "NGN",
        "status": "success",
        "channel": "card",
        "paid_at": "2025-01-15T10:30:00+00:00",
        "created_at": "2025-01-15T10:28:00+00:00"
    }
}
```

## Customer Resource

Customer data from Paystack includes fields you may not want to expose. This resource shows only safe fields and conditionally includes verification status.

```php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->customer_code,
            'email' => $this->email,
            'name' => trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')),
            'phone' => $this->when($this->phone !== null, $this->phone),
            'risk_action' => $this->when(
                $request->user()?->is_admin,
                $this->risk_action
            ),
            'identified' => $this->when(
                $request->boolean('include_verification'),
                fn (): bool => $this->identified ?? false
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
```

**Conditional field inclusion:**

- `phone` — Only shown when the customer has a phone number
- `risk_action` — Only shown to admin users (sensitive data)
- `identified` — Only shown when the `include_verification` query parameter is true

## Subscription Resource with Relationships

Subscriptions often need plan details. This resource conditionally includes the plan and customer data.

```php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->subscription_code,
            'name' => $this->name,
            'status' => $this->status,
            'plan_code' => $this->plan_code,
            'email_token' => $this->when(
                $request->user()?->is_admin,
                $this->email_token
            ),
            'next_payment_date' => $this->next_payment_date?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'plan' => $this->when(
                $this->relationLoaded('plan'),
                fn (): ?array => $this->plan
                    ? new PlanResource($this->plan)
                    : null
            ),
            'customer' => $this->when(
                $request->boolean('include_customer'),
                fn (): ?array => $this->customer
                    ? new CustomerResource($this->customer)
                    : null
            ),
        ];
    }
}
```

**Controller with eager loading:**

```php
namespace App\Http\Controllers;

use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Subscription::query();

        if ($request->boolean('include_plan')) {
            $query->with('plan');
        }

        $subscriptions = $query->paginate($request->integer('per_page', 15));

        return SubscriptionResource::collection($subscriptions)
            ->response();
    }
}
```

## Refund Resource

Refund data includes timestamps and amounts that benefit from consistent formatting.

```php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RefundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->paystack_id,
            'reference' => $this->refund_reference,
            'amount' => $this->amount,
            'amount_display' => $this->formatAmount($this->amount, $this->currency ?? 'NGN'),
            'currency' => $this->currency,
            'status' => $this->status,
            'transaction_reference' => $this->transaction_reference,
            'created_at' => $this->created_at?->toIso8601String(),
            'settled_at' => $this->when(
                $this->settled_at !== null,
                $this->settled_at?->toIso8601String()
            ),
        ];
    }

    private function formatAmount(int $amountInKobo, string $currency): string
    {
        $amount = $amountInKobo / 100;

        return $currency . ' ' . number_format($amount, 2);
    }
}
```

## Resource Collection with Pagination

For list endpoints, use resource collections with pagination metadata:

```php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TransactionCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
            ],
        ];
    }
}
```

**Controller returning a paginated collection:**

```php
namespace App\Http\Controllers;

use App\Http\Resources\TransactionCollection;
use App\Http\Resources\TransactionResource;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $payments = Payment::query()
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 25));

        return (new TransactionCollection($payments))
            ->response();
    }

    public function show(Payment $payment): JsonResponse
    {
        $this->authorize('view', $payment);

        return response()->json([
            'data' => new TransactionResource($payment),
        ]);
    }
}
```

## Transforming Paystack SDK Responses Directly

When you don't have local Eloquent models, you can still use API resources to transform raw Paystack SDK response DTOs:

```php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaystackTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // $this->resource is a Paystack SDK response DTO
        return [
            'reference' => $this->resource->reference,
            'amount' => $this->resource->amount,
            'amount_display' => number_format($this->resource->amount / 100, 2),
            'currency' => $this->resource->currency,
            'status' => $this->resource->status,
            'channel' => $this->resource->channel,
            'paid_at' => $this->resource->paidAt?->toIso8601String(),
        ];
    }
}
```

**Using with the facade:**

```php
use App\Http\Resources\PaystackTransactionResource;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Facades\Paystack;

$transaction = Paystack::verifyTransaction(
    VerifyTransactionInputData::from(['reference' => $reference])
)->transaction;

return new PaystackTransactionResource($transaction);
```

## Best Practices

- **Always convert kobo to display amounts** — Clients should never need to do this math
- **Use `when()` for conditional fields** — Don't expose admin-only or sensitive data by default
- **Include ISO 8601 dates** — Consistent date formatting across all resources
- **Keep resources focused** — One resource per model or DTO, not one mega-resource
- **Use collections for lists** — Provide pagination metadata consistently
- **Version your API** — Resources make it easy to maintain backward compatibility

## Related pages

- [Middleware](/examples/middleware) — Protect API endpoints before resources are returned
- [Policies and Authorization](/examples/policies) — Control which users see which data
- [Caching Paystack Data](/examples/caching) — Cache transformed responses for performance
- [Manager and Facade Usage](/examples/manager-and-facade) — SDK methods that provide the data