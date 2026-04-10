<?php

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Maxiviper117\Paystack\Data\Output\Webhook\Typed\ChargeSuccessWebhookData;
use Maxiviper117\Paystack\Events\PaystackWebhookReceived;
use Maxiviper117\Paystack\Exceptions\MalformedWebhookPayloadException;
use Maxiviper117\Paystack\Jobs\ProcessPaystackWebhookJob;
use Maxiviper117\Paystack\Models\PaystackWebhookCall;
use Maxiviper117\Paystack\Tests\TestCase;
use Spatie\WebhookClient\Exceptions\InvalidWebhookSignature;
use Spatie\WebhookClient\Http\Controllers\WebhookController;

beforeEach(function () {
    Route::post('/paystack/webhook', WebhookController::class)
        ->name('webhook-client-paystack');
});

it('stores and processes a valid paystack webhook', function () {
    /** @var TestCase $testCase */
    $testCase = $this;

    Event::fake([PaystackWebhookReceived::class]);

    $payload = [
        'event' => 'charge.success',
        'data' => [
            'id' => 123,
            'domain' => 'test',
            'status' => 'success',
            'reference' => 'txn_feature_123',
            'amount' => 250000,
            'paid_at' => '2026-03-01T10:00:00+00:00',
        ],
    ];
    $rawPayload = json_encode($payload, JSON_THROW_ON_ERROR);

    $signature = hash_hmac('sha512', $rawPayload, 'sk_test_123');

    $response = $testCase->withServerVariables([
        'REMOTE_ADDR' => '52.31.139.75',
    ])->postJson('/paystack/webhook', $payload, [
        'X-Paystack-Signature' => $signature,
        'User-Agent' => 'paystack-test',
        'X-Unexpected-Header' => 'skip-me',
    ]);

    $response->assertOk()
        ->assertJson([
            'received' => true,
        ]);

    $webhookCall = PaystackWebhookCall::query()->sole();

    expect($webhookCall->name)->toBe('paystack')
        ->and($webhookCall->rawBody())->toBe($rawPayload)
        ->and($webhookCall->inputPayload())->toBe($payload)
        ->and($webhookCall->headers()->has('x-paystack-signature'))->toBeTrue()
        ->and($webhookCall->headers()->has('x-unexpected-header'))->toBeFalse();

    Event::assertDispatched(PaystackWebhookReceived::class, fn (PaystackWebhookReceived $event): bool => $event->webhookCall->is($webhookCall)
        && $event->event->event === 'charge.success'
        && $event->event->resourceType === 'charge'
        && $event->event->id === 123
        && $event->event->occurredAt instanceof CarbonImmutable
        && $event->event->occurredAt->toAtomString() === '2026-03-01T10:00:00+00:00'
        && $event->event->typedData() instanceof ChargeSuccessWebhookData);
});

it('queues the paystack webhook processing job', function () {
    /** @var TestCase $testCase */
    $testCase = $this;

    Queue::fake();

    config()->set('paystack.webhooks.connection', 'sync');
    config()->set('paystack.webhooks.queue', 'paystack-webhooks');

    $payload = [
        'event' => 'invoice.create',
        'data' => [
            'id' => 44,
        ],
    ];
    $rawPayload = json_encode($payload, JSON_THROW_ON_ERROR);

    $signature = hash_hmac('sha512', $rawPayload, 'sk_test_123');

    $response = $testCase->withServerVariables([
        'REMOTE_ADDR' => '52.31.139.75',
    ])->postJson('/paystack/webhook', $payload, [
        'X-Paystack-Signature' => $signature,
    ]);

    $response->assertOk();

    Queue::assertPushed(ProcessPaystackWebhookJob::class, fn (ProcessPaystackWebhookJob $job): bool => $job->connection === 'sync' && $job->queue === 'paystack-webhooks');
});

it('drops webhook requests from non-paystack ip addresses before storage', function () {
    /** @var TestCase $testCase */
    $testCase = $this;

    Event::fake([PaystackWebhookReceived::class]);

    $payload = [
        'event' => 'charge.success',
        'data' => [
            'id' => 123,
            'reference' => 'txn_feature_123',
        ],
    ];
    $rawPayload = json_encode($payload, JSON_THROW_ON_ERROR);
    $signature = hash_hmac('sha512', $rawPayload, 'sk_test_123');

    $response = $testCase->withServerVariables([
        'REMOTE_ADDR' => '127.0.0.1',
    ])->postJson('/paystack/webhook', $payload, [
        'X-Paystack-Signature' => $signature,
    ]);

    $response->assertOk()
        ->assertJson([
            'received' => true,
        ]);

    expect(PaystackWebhookCall::count())->toBe(0);

    Event::assertNotDispatched(PaystackWebhookReceived::class);
});

it('rejects invalid webhook signatures before storage', function () {
    /** @var TestCase $testCase */
    $testCase = $this;

    $payload = [
        'event' => 'charge.success',
        'data' => [
            'id' => 1,
        ],
    ];

    /** @phpstan-ignore-next-line */
    $testCase->withoutExceptionHandling();

    expect(fn () => $testCase->postJson('/paystack/webhook', $payload, [
        'X-Paystack-Signature' => 'invalid-signature',
    ]))->toThrow(InvalidWebhookSignature::class);

    expect(PaystackWebhookCall::count())->toBe(0);
});

it('stores malformed signed payloads and records processing exceptions', function () {
    /** @var TestCase $testCase */
    $testCase = $this;

    $payload = '{"event":"charge.success","data":';
    $signature = hash_hmac('sha512', $payload, 'sk_test_123');

    /** @phpstan-ignore-next-line */
    $testCase->withoutExceptionHandling();

    expect(fn () => $testCase->call('POST', '/paystack/webhook', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
        'REMOTE_ADDR' => '52.31.139.75',
    ], $payload))->toThrow(MalformedWebhookPayloadException::class);

    $webhookCall = PaystackWebhookCall::query()->sole();

    expect($webhookCall->rawBody())->toBe($payload)
        ->and($webhookCall->exception)->not->toBeNull();

    $exception = $webhookCall->exception;

    if (! \is_array($exception) || ! array_key_exists('message', $exception) || ! is_string($exception['message'])) {
        throw new RuntimeException('Expected the stored webhook exception payload to contain a message.');
    }

    expect($exception['message'])->toContain('not valid JSON');
});
