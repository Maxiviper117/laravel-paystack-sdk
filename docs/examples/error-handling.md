# Error Handling

Use this flow when you need to handle Paystack API errors gracefully in your application. Proper error handling ensures your app degrades gracefully, provides clear user feedback, and maintains data consistency when Paystack operations fail.

## Why Error Handling Matters for Payments

- **User experience** — Clear error messages instead of generic "something went wrong"
- **Data consistency** — Roll back local changes when Paystack calls fail
- **Debugging** — Log detailed error context for troubleshooting
- **Recovery** — Retry transient failures, queue for later, or notify admins
- **Compliance** — Audit trails of failed payment attempts

## Common Paystack Error Scenarios

1. **API errors** — Invalid API key, rate limiting, malformed requests
2. **Network failures** — Timeouts, connection refused, DNS issues
3. **Validation errors** — Invalid customer code, insufficient funds, expired card
4. **Business logic errors** — Duplicate reference, already processed, invalid amount
5. **Service unavailable** — Paystack downtime, maintenance windows

## Basic Try-Catch Pattern

Wrap Paystack calls in try-catch blocks to handle failures gracefully:

```php
namespace App\Services\Billing;

use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;
use Maxiviper117\Paystack\Exceptions\PaystackApiException;
use Maxiviper117\Paystack\Facades\Paystack;

class SafeCheckout
{
    public function initialize(array $data): array
    {
        try {
            $response = Paystack::initializeTransaction(
                InitializeTransactionInputData::from($data)
            );

            return [
                'success' => true,
                'authorization_url' => $response->authorizationUrl,
                'reference' => $response->reference,
            ];
        } catch (PaystackApiException $e) {
            // Paystack returned an error response
            Log::error('Paystack API error during checkout', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'error' => 'Payment service temporarily unavailable. Please try again.',
                'reference' => null,
            ];
        } catch (\Exception $e) {
            // Network or other unexpected error
            Log::error('Unexpected error during checkout', [
                'message' => $e->getMessage(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'error' => 'An unexpected error occurred. Please try again later.',
                'reference' => null,
            ];
        }
    }
}
```

## Handling Specific Error Types

Different errors require different responses. Check error codes and messages for specific handling:

```php
namespace App\Services\Billing;

use Maxiviper117\Paystack\Exceptions\PaystackApiException;

class ErrorClassifier
{
    public function handle(PaystackApiException $e): array
    {
        $message = $e->getMessage();
        $code = $e->getCode();

        // Rate limiting
        if ($code === 429 || str_contains($message, 'rate limit')) {
            return [
                'type' => 'rate_limit',
                'retry_after' => 60,
                'user_message' => 'Too many requests. Please wait a moment and try again.',
            ];
        }

        // Invalid API key
        if ($code === 401 || str_contains($message, 'invalid key')) {
            return [
                'type' => 'auth_error',
                'retry' => false,
                'user_message' => 'Payment service configuration error. Please contact support.',
                'alert_admin' => true,
            ];
        }

        // Validation errors
        if ($code === 400) {
            return [
                'type' => 'validation_error',
                'retry' => false,
                'user_message' => 'Invalid payment details. Please check and try again.',
            ];
        }

        // Insufficient funds (from charge response)
        if (str_contains($message, 'insufficient funds')) {
            return [
                'type' => 'insufficient_funds',
                'retry' => false,
                'user_message' => 'Insufficient funds. Please try a different payment method.',
            ];
        }

        // Expired card
        if (str_contains($message, 'expired')) {
            return [
                'type' => 'expired_card',
                'retry' => false,
                'user_message' => 'Your card has expired. Please update your payment method.',
            ];
        }

        // Default: possibly retryable
        return [
            'type' => 'unknown',
            'retry' => true,
            'user_message' => 'Payment failed. Please try again.',
        ];
    }
}
```

## Global Exception Handler

Register Paystack-specific handling in your exception handler:

```php
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Maxiviper117\Paystack\Exceptions\PaystackApiException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->renderable(function (PaystackApiException $e, $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Payment processing error',
                    'error' => $this->classifyError($e),
                ], 422);
            }

            return redirect()
                ->back()
                ->with('error', 'Payment could not be processed. Please try again.');
        });
    }

    private function classifyError(PaystackApiException $e): array
    {
        // Return structured error info for API consumers
        return [
            'type' => 'paystack_error',
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
        ];
    }
}
```

## Retry with Exponential Backoff

For transient failures (network issues, rate limits), implement retry logic:

```php
namespace App\Services\Billing;

use Illuminate\Support\Facades\Log;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Exceptions\PaystackApiException;
use Maxiviper117\Paystack\Facades\Paystack;

class RetryableVerification
{
    private const MAX_RETRIES = 3;

    private const BACKOFF_SECONDS = [1, 2, 4]; // Exponential backoff

    public function verify(string $reference): mixed
    {
        $lastException = null;

        for ($attempt = 0; $attempt < self::MAX_RETRIES; $attempt++) {
            try {
                return Paystack::verifyTransaction(
                    VerifyTransactionInputData::from(['reference' => $reference])
                )->transaction;
            } catch (PaystackApiException $e) {
                $lastException = $e;

                // Don't retry auth errors or validation errors
                if (in_array($e->getCode(), [400, 401, 403, 404], true)) {
                    throw $e;
                }

                // Don't retry after last attempt
                if ($attempt === self::MAX_RETRIES - 1) {
                    break;
                }

                Log::warning('Paystack verification failed, retrying', [
                    'reference' => $reference,
                    'attempt' => $attempt + 1,
                    'error' => $e->getMessage(),
                ]);

                // Wait before retry
                sleep(self::BACKOFF_SECONDS[$attempt]);
            }
        }

        // All retries exhausted
        throw $lastException ?? new \RuntimeException('Verification failed after retries');
    }
}
```

## Queue Failed Operations

When real-time processing isn't required, queue failed operations for later retry:

```php
namespace App\Services\Billing;

use App\Jobs\RetryVerificationLater;
use Illuminate\Support\Facades\Log;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Exceptions\PaystackApiException;
use Maxiviper117\Paystack\Facades\Paystack;

class QueueOnFailure
{
    public function verifyOrQueue(string $reference, int $orderId): ?array
    {
        try {
            $transaction = Paystack::verifyTransaction(
                VerifyTransactionInputData::from(['reference' => $reference])
            )->transaction;

            return ['status' => $transaction->status, 'queued' => false];
        } catch (PaystackApiException $e) {
            Log::error('Verification failed, queuing for retry', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            // Queue for retry in 5 minutes
            RetryVerificationLater::dispatch($reference, $orderId)
                ->delay(now()->addMinutes(5));

            return ['status' => 'pending_verification', 'queued' => true];
        }
    }
}
```

## Circuit Breaker Pattern

Prevent cascading failures when Paystack is down by implementing a circuit breaker:

```php
namespace App\Services\Billing;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaystackCircuitBreaker
{
    private const FAILURE_THRESHOLD = 5;

    private const TIMEOUT_SECONDS = 60;

    private const CACHE_KEY = 'paystack_circuit_state';

    public function call(callable $operation): mixed
    {
        if ($this->isOpen()) {
            throw new \RuntimeException(
                'Payment service temporarily unavailable. Please try again later.'
            );
        }

        try {
            $result = $operation();
            $this->recordSuccess();

            return $result;
        } catch (\Exception $e) {
            $this->recordFailure();
            throw $e;
        }
    }

    private function isOpen(): bool
    {
        $failures = Cache::get(self::CACHE_KEY . '_failures', 0);
        $lastFailure = Cache::get(self::CACHE_KEY . '_last_failure');

        if ($failures >= self::FAILURE_THRESHOLD) {
            // Check if timeout has passed
            if ($lastFailure !== null && now()->diffInSeconds($lastFailure) < self::TIMEOUT_SECONDS) {
                return true; // Circuit still open
            }

            // Timeout passed, reset
            $this->reset();
        }

        return false;
    }

    private function recordSuccess(): void
    {
        Cache::forget(self::CACHE_KEY . '_failures');
    }

    private function recordFailure(): void
    {
        $failures = Cache::increment(self::CACHE_KEY . '_failures');
        Cache::put(self::CACHE_KEY . '_last_failure', now(), self::TIMEOUT_SECONDS);

        Log::warning('Paystack circuit breaker recorded failure', [
            'consecutive_failures' => $failures,
        ]);
    }

    private function reset(): void
    {
        Cache::forget(self::CACHE_KEY . '_failures');
        Cache::forget(self::CACHE_KEY . '_last_failure');
    }
}
```

**Using the circuit breaker:**

```php
$circuitBreaker = new PaystackCircuitBreaker;

try {
    $result = $circuitBreaker->call(function () use ($data) {
        return Paystack::initializeTransaction(
            InitializeTransactionInputData::from($data)
        );
    });
} catch (\RuntimeException $e) {
    // Circuit is open or operation failed
    return ['error' => $e->getMessage()];
}
```

## Graceful Degradation

When Paystack is unavailable, offer alternative flows:

```php
namespace App\Services\Billing;

use Illuminate\Support\Facades\Log;
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;
use Maxiviper117\Paystack\Exceptions\PaystackApiException;
use Maxiviper117\Paystack\Facades\Paystack;

class GracefulCheckout
{
    public function initialize(array $data): array
    {
        try {
            $response = Paystack::initializeTransaction(
                InitializeTransactionInputData::from($data)
            );

            return [
                'success' => true,
                'method' => 'paystack',
                'authorization_url' => $response->authorizationUrl,
            ];
        } catch (PaystackApiException $e) {
            Log::error('Paystack unavailable, offering alternative', [
                'error' => $e->getMessage(),
            ]);

            // Offer bank transfer as fallback
            return [
                'success' => true,
                'method' => 'bank_transfer',
                'instructions' => $this->getBankTransferInstructions($data['amount']),
                'reference' => $this->generateManualReference(),
            ];
        }
    }

    private function getBankTransferInstructions(int $amount): string
    {
        return "Please transfer {$this->formatAmount($amount)} to:\n" .
               "Account: 1234567890\n" .
               "Bank: Example Bank\n" .
               "Reference: Your email address";
    }

    private function formatAmount(int $amountInKobo): string
    {
        return '₦' . number_format($amountInKobo / 100, 2);
    }

    private function generateManualReference(): string
    {
        return 'MANUAL_' . uniqid();
    }
}
```

## Error Logging Best Practices

Log errors with enough context to debug without exposing sensitive data:

```php
use Illuminate\Support\Facades\Log;

Log::error('Paystack operation failed', [
    // Safe to log
    'operation' => 'initialize_transaction',
    'reference' => $reference,
    'amount' => $amount,
    'currency' => $currency,
    'error_code' => $e->getCode(),
    'error_message' => $e->getMessage(),
    'user_id' => auth()->id(),
    'timestamp' => now()->toIso8601String(),

    // NEVER log these
    // 'card_number' => $cardNumber,
    // 'cvv' => $cvv,
    // 'api_key' => $apiKey,
]);
```

## Testing Error Handling

Test your error handling to ensure it works when things go wrong:

```php
use Maxiviper117\Paystack\Exceptions\PaystackApiException;
use Maxiviper117\Paystack\Facades\Paystack;

test('checkout handles api error gracefully', function (): void {
    Paystack::shouldReceive('initializeTransaction')
        ->once()
        ->andThrow(new PaystackApiException('Service unavailable', 503));

    $service = new \App\Services\Billing\SafeCheckout;
    $result = $service->initialize(['amount' => 50000, 'email' => 'test@example.com']);

    expect($result['success'])->toBeFalse()
        ->and($result['error'])->toContain('temporarily unavailable');
});

test('rate limit triggers retry with backoff', function (): void {
    $attempts = 0;

    Paystack::shouldReceive('verifyTransaction')
        ->times(3)
        ->andReturnUsing(function () use (&$attempts) {
            $attempts++;
            if ($attempts < 3) {
                throw new PaystackApiException('Rate limit exceeded', 429);
            }
            return /* success response */;
        });

    $service = new \App\Services\Billing\RetryableVerification;
    $result = $service->verify('ref_123');

    expect($attempts)->toBe(3)
        ->and($result)->not->toBeNull();
});
```

## Related pages

- [Database Transactions](/examples/database-transactions) — Roll back local changes on Paystack errors
- [Queued Jobs](/examples/queued-jobs) — Retry failed operations asynchronously
- [Testing Paystack Integrations](/examples/testing) — Test error scenarios
- [Scheduled Tasks](/examples/scheduled-tasks) — Automated retry of failed operations