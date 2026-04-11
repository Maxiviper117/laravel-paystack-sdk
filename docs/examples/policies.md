# Policies and Authorization

Use this flow when you need to control who can initiate payments, manage subscriptions, process refunds, or access billing data. Laravel Policies provide a clean, testable way to authorize Paystack-related actions in your application.

## Why Use Policies for Payment Operations

Payment authorization has unique requirements:

- **Financial sensitivity** — Not every user should be able to initiate refunds or view transaction details
- **Role-based access** — Admins, support staff, and customers have different payment permissions
- **Business rules** — Refund windows, subscription changes, and dispute handling often require conditional authorization
- **Audit compliance** — Policy checks create clear, testable authorization boundaries

## Typical Authorization Scenarios

1. **Initiate payments** — Only verified users can start a checkout
2. **Process refunds** — Only admins or support staff within a refund window
3. **Manage subscriptions** — Users can modify their own; admins can modify any
4. **View transactions** — Users see their own; admins see all
5. **Handle disputes** — Only designated staff can submit evidence or resolve disputes

## Payment Policy

This policy covers the most common payment authorization rules: who can view, create, refund, and dispute transactions.

```php
namespace App\Policies;

use App\Models\User;
use App\Models\Payment;

class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        // Users can view their own payments; admins can view any
        return $user->id === $payment->user_id
            || $user->is_admin;
    }

    public function create(User $user): bool
    {
        // Only verified users with a confirmed email can initiate payments
        return $user->hasVerifiedEmail()
            && $user->is_active;
    }

    public function refund(User $user, Payment $payment): bool
    {
        // Admins can always refund
        if ($user->is_admin) {
            return true;
        }

        // Support staff can refund within 30 days
        if ($user->is_support_staff && $payment->created_at->diffInDays(now()) <= 30) {
            return true;
        }

        return false;
    }

    public function dispute(User $user, Payment $payment): bool
    {
        // Only admins and support staff can manage disputes
        return $user->is_admin || $user->is_support_staff;
    }
}
```

**Registering the policy** in `AuthServiceProvider` or `bootstrap/app.php`:

```php
use App\Models\Payment;
use App\Policies\PaymentPolicy;

// In AuthServiceProvider::$policies:
protected $policies = [
    Payment::class => PaymentPolicy::class,
];

// Or in Laravel 11/12 bootstrap/app.php:
->withPolicies([
    PaymentPolicy::class,
])
```

## Subscription Policy

Subscription changes often have different rules for the subscriber versus an administrator. This policy handles plan changes, cancellations, and reactivations.

```php
namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

class SubscriptionPolicy
{
    public function view(User $user, Subscription $subscription): bool
    {
        return $user->id === $subscription->user_id
            || $user->is_admin;
    }

    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }

    public function changePlan(User $user, Subscription $subscription): bool
    {
        // The subscriber can change their own plan
        // Admins can change any plan
        return $user->id === $subscription->user_id
            || $user->is_admin;
    }

    public function cancel(User $user, Subscription $subscription): bool
    {
        // Users can cancel their own; admins can cancel any
        return $user->id === $subscription->user_id
            || $user->is_admin;
    }

    public function reactivate(User $user, Subscription $subscription): bool
    {
        // Only admins can reactivate a cancelled subscription
        // Users should create a new subscription instead
        return $user->is_admin;
    }
}
```

## Using Policies in Controllers

Laravel policies integrate cleanly with controllers through the `authorize` method or middleware.

```php
namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Billing\StartCheckout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function show(Request $request, Payment $payment): JsonResponse
    {
        $this->authorize('view', $payment);

        return response()->json([
            'reference' => $payment->reference,
            'amount' => $payment->amount,
            'status' => $payment->status,
        ]);
    }

    public function store(Request $request, StartCheckout $checkout): RedirectResponse
    {
        $this->authorize('create', Payment::class);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:100'],
            'email' => ['required', 'email'],
        ]);

        $authorizationUrl = $checkout->handle($validated);

        return redirect()->away($authorizationUrl);
    }

    public function refund(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('refund', $payment);

        // Refund logic here
    }
}
```

**Using policy middleware** for route-level authorization:

```php
use App\Models\Payment;
use Illuminate\Support\Facades\Route;

Route::get('/payments/{payment}', [PaymentController::class, 'show'])
    ->middleware('can:view,payment');

Route::post('/payments', [PaymentController::class, 'store'])
    ->middleware('can:create,' . Payment::class);
```

## Using Policies in Blade Views

Policies also work in Blade templates to conditionally show or hide UI elements:

```blade
@can('refund', $payment)
    <a href="{{ route('payments.refund', $payment) }}" class="text-red-600">
        Request Refund
    </a>
@endcan

@can('dispute', $payment)
    <a href="{{ route('disputes.create', $payment) }}" class="text-orange-600">
        Open Dispute
    </a>
@endcan

@can('changePlan', $subscription)
    <a href="{{ route('subscriptions.change-plan', $subscription) }}" class="text-blue-600">
        Change Plan
    </a>
@endcan
```

## Policy with Paystack SDK Integration

Policies work well with the Paystack SDK when you need to check authorization before making API calls. This prevents unauthorized API usage and keeps your Paystack secret key operations server-side only.

```php
namespace App\Services\Billing;

use App\Models\User;
use App\Models\Payment;
use Maxiviper117\Paystack\Data\Input\Refund\CreateRefundInputData;
use Maxiviper117\Paystack\Facades\Paystack;

class ProcessRefund
{
    public function handle(User $user, Payment $payment, int $amount): array
    {
        // Policy check before any API call
        $user->can('refund', $payment)
            || abort(403, 'You are not authorized to process refunds for this payment.');

        $response = Paystack::createRefund(
            CreateRefundInputData::from([
                'transaction' => $payment->paystack_id,
                'amount' => $amount,
            ])
        );

        return [
            'refund_id' => $response->refund->id,
            'status' => $response->refund->status,
        ];
    }
}
```

**Why check authorization before the API call:**

- Prevents unnecessary Paystack API calls for unauthorized users
- Avoids creating refund records in Paystack that your app doesn't allow
- Keeps authorization logic in one place (the policy) rather than scattered in controllers

## Testing Policies

Policies are easy to test in isolation, which is especially important for payment authorization:

```php
use App\Models\User;
use App\Models\Payment;
use App\Policies\PaymentPolicy;

test('users can view their own payments', function (): void {
    $user = User::factory()->create();
    $payment = Payment::factory()->create(['user_id' => $user->id]);

    $policy = new PaymentPolicy;

    expect($policy->view($user, $payment))->toBeTrue();
});

test('users cannot view others payments', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $payment = Payment::factory()->create(['user_id' => $otherUser->id]);

    $policy = new PaymentPolicy;

    expect($policy->view($user, $payment))->toBeFalse();
});

test('admins can refund any payment', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);
    $payment = Payment::factory()->create();

    $policy = new PaymentPolicy;

    expect($policy->refund($admin, $payment))->toBeTrue();
});

test('support staff can refund within 30 days', function (): void {
    $staff = User::factory()->create(['is_support_staff' => true]);
    $recentPayment = Payment::factory()->create(['created_at' => now()->subDays(15)]);
    $oldPayment = Payment::factory()->create(['created_at' => now()->subDays(45)]);

    $policy = new PaymentPolicy;

    expect($policy->refund($staff, $recentPayment))->toBeTrue()
        ->and($policy->refund($staff, $oldPayment))->toBeFalse();
});
```

## Related pages

- [Middleware](/examples/middleware) — Route-level protection and rate limiting
- [Form Request Validation](/examples/form-requests) — Validate payment input before processing
- [Queued Jobs](/examples/queued-jobs) — Process authorized payment operations asynchronously
- [Optional Billing Layer](/examples/billing-layer) — Local mirror tables for payment data