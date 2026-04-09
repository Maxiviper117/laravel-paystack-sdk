<?php

namespace Tests\Feature;

use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Transaction\VerifyTransactionRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Tests\TestCase;

class TransactionVerificationBannerTest extends TestCase
{
    public function test_it_shows_a_success_message_when_transaction_verification_succeeds(): void
    {
        app(PaystackConnector::class)->withMockClient(new MockClient([
            VerifyTransactionRequest::class => MockResponse::make([
                'status' => true,
                'message' => 'Verification successful',
                'data' => [
                    'id' => 10,
                    'status' => 'success',
                    'reference' => 'dtj2hc8cd7',
                    'amount' => 1550,
                    'currency' => 'NGN',
                    'channel' => 'card',
                ],
            ], 200),
        ]));

        $response = $this->get('/paystack/demo/transactions?trxref=dtj2hc8cd7&reference=dtj2hc8cd7');

        $response->assertOk()
            ->assertSee('Verification successful', false)
            ->assertSee('Transaction dtj2hc8cd7 was verified successfully.', false)
            ->assertSee('Confirmed in UI', false);
    }

    public function test_it_shows_a_failure_message_when_transaction_verification_returns_a_failed_status(): void
    {
        app(PaystackConnector::class)->withMockClient(new MockClient([
            VerifyTransactionRequest::class => MockResponse::make([
                'status' => true,
                'message' => 'Verification complete',
                'data' => [
                    'id' => 10,
                    'status' => 'failed',
                    'reference' => 'dtj2hc8cd7',
                    'amount' => 1550,
                    'currency' => 'NGN',
                    'channel' => 'card',
                ],
            ], 200),
        ]));

        $response = $this->get('/paystack/demo/transactions?trxref=dtj2hc8cd7&reference=dtj2hc8cd7');

        $response->assertOk()
            ->assertSee('Verification failed', false)
            ->assertSee('Transaction dtj2hc8cd7 returned status failed.', false)
            ->assertSee('Verification failed in UI', false);
    }
}
