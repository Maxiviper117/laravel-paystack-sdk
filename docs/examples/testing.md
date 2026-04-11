# Testing Paystack Integrations

Use this flow when you need to write automated tests for code that interacts with the Paystack SDK. Testing payment code requires faking external API calls so your tests are fast, deterministic, and don't hit Paystack's live or test servers.

## Why Fake the Paystack SDK in Tests

- **Speed** — API calls are slow; faked calls are instant
- **Reliability** — No flaky tests from network issues or rate limits
- **Determinism** — Control exactly what the SDK returns for every scenario
- **Safety** — Never accidentally create real transactions or customers
- **Coverage** — Test edge cases like API errors, empty responses, and timeouts

## Typical Testing Scenarios

1. **Controller tests** — Verify checkout flows redirect correctly
2. **Service tests** — Assert business logic uses the right SDK methods
3. **Webhook tests** — Confirm event handling updates local state
4. **Job tests** — Verify queued verification processes succeed and fail correctly
5. **Policy tests** — Confirm authorization rules work independently

## Faking the Paystack Facade

The `Paystack` facade extends Laravel's `Facade`, so you can use `Paystack::fake()` in tests. This prevents any real API calls and lets you assert which methods were called.

```php
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;
use Maxiviper117\Paystack\Data\Output\Transaction\InitializeTransactionResponseData;
use Maxiviper117\Paystack\Facades\Paystack;

beforeEach(function (): void {
    Paystack::fake();
});

test('checkout initializes a transaction and redirects', function (): void {
    Paystack::shouldReceive('initializeTransaction')
        ->once()
        ->andReturn(new InitializeTransactionResponseData(
            authorizationUrl: 'https://checkout.paystack.com/test',
            reference: 'ref_12345',
            accessCode: 'access_abc',
        ));

    $response = $this->post('/checkout', [
        'email' => 'customer@example.com',
        'amount' => 50000,
    ]);

    $response->assertRedirect('https://checkout.paystack.com/test');
});
```

## Asserting Method Calls

Use `shouldReceive` to verify that your code calls the right SDK methods with the right arguments:

```php
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Data\Output\Transaction\VerifyTransactionResponseData;
use Maxiviper117\Paystack\Facades\Paystack;

test('verification job calls verify transaction with correct reference', function (): void {
    $reference = 'ref_test_123';

    Paystack::shouldReceive('verifyTransaction')
        ->once()
        ->with(\Mockery::on(function (VerifyTransactionInputData $input) use ($reference): bool {
            return $input->reference === $reference;
        }))
        ->andReturn(/* VerifyTransactionResponseData */);

    VerifyTransactionJob::dispatchSync($reference, orderId: 1);
});
```

**The `Mockery::on` matcher** lets you inspect the DTO passed to the method, verifying that your code constructs the right input.

## Testing Webhook Handlers

Webhook handlers receive `PaystackWebhookReceived` events. You can dispatch these events directly in tests to verify your handler logic:

```php
use Maxiviper117\Paystack\Data\Output\Webhook\PaystackWebhookEventData;
use Maxiviper117\Paystack\Enums\Webhook\PaystackWebhookEvent;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;
use Maxiviper117\Paystack\Models\PaystackWebhookCall;

test('charge success webhook updates payment status', function (): void {
    $payment = Payment::factory()->create([
        'reference' => 'ref_test_123',
        'status' => 'pending',
    ]);

    $webhookCall = PaystackWebhookCall::factory()->create([
        'type' => PaystackWebhookEvent::ChargeSuccess->value,
        'payload' => [
            'event' => 'charge.success',
            'data' => [
                'reference' => 'ref_test_123',
                'status' => 'success',
                'amount' => 50000,
                'paid_at' => '2025-01-15T10:30:00.000Z',
            ],
        ],
    ]);

    $eventData = PaystackWebhookEventData::fromPayload($webhookCall->payload);

    event(new PaystackWebhookReceived($webhookCall, $eventData));

    expect($payment->fresh()->status)->toBe('paid');
});
```

## Testing Error Handling

Test that your code handles Paystack API errors gracefully. Use `andReturnUsing` or throw exceptions to simulate failures:

```php
use Maxiviper117\Paystack\Exceptions\PaystackApiException;
use Maxiviper117\Paystack\Facades\Paystack;

test('checkout handles api error gracefully', function (): void {
    Paystack::shouldReceive('initializeTransaction')
        ->once()
        ->andThrow(new PaystackApiException('Invalid API key'));

    $response = $this->post('/checkout', [
        'email' => 'customer@example.com',
        'amount' => 50000,
    ]);

    $response->assertStatus(500)
        ->assertJson(['message' => 'Payment initialization failed. Please try again.']);
});

test('verification job retries on transient failure', function (): void {
    Paystack::shouldReceive('verifyTransaction')
        ->once()
        ->andThrow(new \RuntimeException('Connection timeout'));

    $this->expectException(\RuntimeException::class);

    // Job will be retried by the queue system
    VerifyTransactionJob::dispatchSync('ref_test_123', orderId: 1);
});
```

## Testing the Billing Layer

When testing code that uses the `Billable` trait, fake the Paystack facade and assert the lifecycle methods are called correctly:

```php
use App\Models\User;
use Maxiviper117\Paystack\Facades\Paystack;

test('create billable customer calls paystack and stores locally', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'name' => 'Test User',
    ]);

    Paystack::shouldReceive('createBillableCustomer')
        ->once()
        ->with(\Mockery::on(fn ($billable) => $billable->is($user)), null)
        ->andReturn(/* CreateCustomerResponseData */);

    $user->syncPaystackCustomer();
});

test('hasPaystackCustomer returns false when no customer exists', function (): void {
    $user = User::factory()->create();

    expect($user->hasPaystackCustomer())->toBeFalse();
});
```

## Testing with Form Requests

Form requests can be tested independently from controllers. This is useful for validating payment input rules:

```php
use App\Http\Requests\StoreCheckoutRequest;

test('checkout request rejects invalid email', function (): void {
    $request = new StoreCheckoutRequest;

    $validator = app('validator')->make(
        ['email' => 'not-an-email', 'amount' => 50000],
        $request->rules()
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('email'))->toBeString();
});

test('checkout request rejects amount below minimum', function (): void {
    $request = new StoreCheckoutRequest;

    $validator = app('validator')->make(
        ['email' => 'test@example.com', 'amount' => 50],
        $request->rules()
    );

    expect($validator->fails())->toBeTrue();
});
```

## Testing Notifications

Payment notifications can be tested by asserting the notification is sent to the right user with the right data:

```php
use App\Notifications\PaymentConfirmed;
use Illuminate\Support\Facades\Notification;

test('payment confirmation notification is sent after successful charge', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    // Trigger the notification
    $user->notify(new PaymentConfirmed(
        reference: 'ref_12345',
        amount: 50000,
        currency: 'NGN',
    ));

    Notification::assertSentTo(
        $user,
        PaymentConfirmed::class,
        fn (PaymentConfirmed $notification) => $notification->reference === 'ref_12345'
    );
});

test('payment confirmation formats amount correctly', function (): void {
    $notification = new PaymentConfirmed(
        reference: 'ref_12345',
        amount: 50000, // 500 NGN in kobo
        currency: 'NGN',
    );

    $mail = $notification->toMail($user);

    expect($mail)->toBeInstanceOf(\Illuminate\Notifications\Messages\MailMessage::class);
});
```

## Integration Test Base

For integration tests that need the full service container, create a base test class that sets up the Paystack fake:

```php
namespace Tests\Feature;

use Maxiviper117\Paystack\Facades\Paystack;
use Tests\TestCase;

abstract class PaystackTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Paystack::fake();
    }

    protected function fakeInitializeTransactionResponse(): array
    {
        return [
            'authorizationUrl' => 'https://checkout.paystack.com/test',
            'reference' => 'ref_test_' . str()->random(10),
            'accessCode' => 'access_test_' . str()->random(8),
        ];
    }

    protected function fakeVerifyTransactionResponse(string $status = 'success'): array
    {
        return [
            'status' => $status,
            'amount' => 50000,
            'currency' => 'NGN',
            'reference' => 'ref_test_123',
            'channel' => 'card',
            'paidAt' => now()->toIso8601String(),
        ];
    }
}
```

Then extend this in your feature tests:

```php
namespace Tests\Feature\Payments;

use Tests\Feature\PaystackTestCase;

class CheckoutTest extends PaystackTestCase
{
    public function test_checkout_redirects_to_paystack(): void
    {
        Paystack::shouldReceive('initializeTransaction')
            ->once()
            ->andReturn(/* ... */);

        $response = $this->post('/checkout', [
            'email' => 'test@example.com',
            'amount' => 50000,
        ]);

        $response->assertRedirect();
    }
}
```

## Key Testing Principles

- **Always fake the Paystack facade** in tests — never hit the real API
- **Assert method calls** — Verify your code calls the right SDK methods
- **Test both success and failure paths** — API errors, validation failures, and network issues
- **Test webhook handlers independently** — Dispatch events directly without HTTP
- **Test policies separately** — Authorization logic should not depend on the SDK
- **Use factory helpers** — Create consistent fake response data across tests

## Related pages

- [Manager and Facade Usage](/examples/manager-and-facade) — How the facade works in production code
- [Queued Jobs](/examples/queued-jobs) — Testing job retry and failure behavior
- [Webhook Processing](/examples/webhooks) — Testing webhook event handling
- [Form Request Validation](/examples/form-requests) — Testing validation rules independently