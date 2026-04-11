# Middleware

Use this flow when you need to protect payment routes, verify webhook sources, or enforce business rules before Paystack operations reach your controllers. Laravel middleware provides a clean, reusable layer for cross-cutting concerns in payment flows.

## Why Use Middleware for Paystack Operations

Middleware sits between the HTTP request and your controller, making it ideal for:

- **Route protection** — Ensure only authorized users can access payment endpoints
- **Request validation** — Verify required payment parameters before controller logic runs
- **Rate limiting** — Prevent abuse on payment initialization or verification endpoints
- **Webhook security** — The package already validates signatures and IP allowlists; middleware can add application-level checks
- **Audit logging** — Record every payment-related request for compliance and debugging

## Typical Middleware Scenarios

1. **Verify payment ownership** — Ensure the authenticated user owns the transaction being accessed
2. **Rate limit checkout** — Prevent rapid repeated payment initialization attempts
3. **Verify reference format** — Reject requests with malformed Paystack references early
4. **Log payment access** — Record who accessed which payment endpoints and when

## Verify Payment Ownership Middleware

This middleware ensures a user can only access or verify their own transactions. It loads the payment record and checks ownership before the request reaches the controller.

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnsPayment
{
    public function handle(Request $request, Closure $next): Response
    {
        $reference = $request->route('reference')
            ?? $request->input('reference')
            ?? $request->route('payment');

        if ($reference === null) {
            return response()->json(['message' => 'Payment reference is required.'], 400);
        }

        $payment = $request->user()
            ?->paystackTransactions()
            ->where('reference', $reference)
            ->first();

        if ($payment === null) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        // Attach to request so the controller doesn't need to re-fetch
        $request->attributes->set('payment', $payment);

        return $next($request);
    }
}
```

**Registering the middleware** in Laravel 11/12 `bootstrap/app.php`:

```php
use App\Http\Middleware\EnsureOwnsPayment;

->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'owns-payment' => EnsureOwnsPayment::class,
    ]);
})
```

**Applying to routes:**

```php
use Illuminate\Support\Facades\Route;

Route::get('/payments/{reference}/receipt', [PaymentController::class, 'receipt'])
    ->middleware('owns-payment');

Route::get('/payments/{reference}/verify', [PaymentController::class, 'verify'])
    ->middleware(['auth', 'owns-payment']);
```

**Using the attached payment in the controller:**

```php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function receipt(Request $request): JsonResponse
    {
        $payment = $request->attributes->get('payment');

        return response()->json([
            'reference' => $payment->reference,
            'amount' => $payment->amount,
            'status' => $payment->status,
        ]);
    }
}
```

**Why this pattern works:**

- The middleware resolves the payment and checks ownership in one place
- Controllers stay focused on business logic, not authorization
- The `request->attributes` pattern avoids re-fetching the same record

## Rate Limit Checkout Middleware

Payment initialization endpoints are sensitive to abuse. This middleware uses Laravel's `RateLimiter` facade to throttle checkout attempts per user.

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleCheckout
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'checkout:' . $request->user()?->id
            ?? $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => 'Too many checkout attempts. Try again in ' . $seconds . ' seconds.',
            ], 429);
        }

        RateLimiter::hit($key, 60); // 1 minute decay

        return $next($request);
    }
}
```

**Usage:**

```php
Route::post('/checkout', [CheckoutController::class, 'store'])
    ->middleware(['auth', ThrottleCheckout::class]);
```

**Why rate-limit checkout:**

- Prevents accidental double-submissions from impatient users
- Protects against scripted abuse that could create many pending transactions
- Reduces load on both your app and the Paystack API

## Verify Reference Format Middleware

Paystack references follow specific patterns. This middleware rejects malformed references early, before any API calls are made.

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyPaystackReference
{
    public function handle(Request $request, Closure $next): Response
    {
        $reference = $request->route('reference')
            ?? $request->input('reference');

        if ($reference !== null && ! $this->isValidReference($reference)) {
            return response()->json([
                'message' => 'Invalid payment reference format.',
            ], 422);
        }

        return $next($request);
    }

    private function isValidReference(string $reference): bool
    {
        // Paystack references are alphanumeric with hyphens and underscores
        // Adjust this pattern to match your application's reference format
        return preg_match('/^[a-zA-Z0-9_-]+$/', $reference) === 1
            && strlen($reference) <= 100;
    }
}
```

**Usage:**

```php
Route::get('/payments/{reference}/verify', [PaymentController::class, 'verify'])
    ->middleware(['auth', VerifyPaystackReference::class]);
```

## Payment Audit Logging Middleware

For compliance and debugging, log every request to payment-related endpoints with the authenticated user and request details.

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogPaymentAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        Log::channel('payments')->info('Payment endpoint accessed', [
            'user_id' => $request->user()?->id,
            'method' => $request->method(),
            'path' => $request->path(),
            'reference' => $request->route('reference'),
            'status' => $response->getStatusCode(),
            'ip' => $request->ip(),
        ]);

        return $response;
    }
}
```

**Logging channel configuration** in `config/logging.php`:

```php
'channels' => [
    'payments' => [
        'driver' => 'daily',
        'path' => storage_path('logs/payments.log'),
        'level' => 'info',
        'days' => 90, // Keep payment logs for 90 days
    ],
],
```

**Usage:**

```php
Route::middleware(['auth', LogPaymentAccess::class])->group(function (): void {
    Route::post('/checkout', [CheckoutController::class, 'store']);
    Route::get('/payments/{reference}/verify', [PaymentController::class, 'verify']);
    Route::post('/payments/{reference}/refund', [RefundController::class, 'store']);
});
```

**Why separate payment logs:**

- Payment audit trails often have different retention requirements than application logs
- Easier to search and analyze payment-specific events
- Supports compliance requirements for financial data access

## Combining Middleware

For production payment routes, combine multiple middleware for defense in depth:

```php
use App\Http\Middleware\EnsureOwnsPayment;
use App\Http\Middleware\LogPaymentAccess;
use App\Http\Middleware\ThrottleCheckout;
use App\Http\Middleware\VerifyPaystackReference;

Route::middleware(['auth', LogPaymentAccess::class])->group(function (): void {
    // Checkout: rate-limited, no ownership check (new payment)
    Route::post('/checkout', [CheckoutController::class, 'store'])
        ->middleware(ThrottleCheckout::class);

    // Existing payments: ownership check, reference validation
    Route::middleware([VerifyPaystackReference::class, EnsureOwnsPayment::class])->group(function (): void {
        Route::get('/payments/{reference}', [PaymentController::class, 'show']);
        Route::get('/payments/{reference}/verify', [PaymentController::class, 'verify']);
    });
});
```

## Related pages

- [Form Request Validation](/examples/form-requests) — Validate payment input before it reaches your controller
- [Queued Jobs](/examples/queued-jobs) — Process payment operations asynchronously
- [Webhook Processing](/examples/webhooks) — Handle Paystack webhook events
- [Manager and Facade Usage](/examples/manager-and-facade) — Use the Paystack facade in your middleware or controllers