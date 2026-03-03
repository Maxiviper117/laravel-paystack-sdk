<?php

use Illuminate\Support\Facades\Route;
use Maxiviper117\Paystack\Actions\Transaction\VerifyTransactionAction;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Facades\Paystack;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Transaction\VerifyTransactionRequest;
use Maxiviper117\Paystack\Tests\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can return an action response dto directly from a route as json', function () {
    /** @var TestCase $testCase */
    $testCase = $this;

    Route::get('/test/paystack/action-response', function (VerifyTransactionAction $verifyTransaction) {
        return $verifyTransaction(new VerifyTransactionInputData('ref_action'));
    });

    app(PaystackConnector::class)->withMockClient(new MockClient([
        VerifyTransactionRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Verification successful',
            'data' => [
                'id' => 10,
                'status' => 'success',
                'reference' => 'ref_action',
                'amount' => 1550,
                'currency' => 'NGN',
                'paid_at' => '2026-03-01T10:00:00+00:00',
            ],
        ], 200),
    ]));

    $testCase->getJson('/test/paystack/action-response')
        ->assertOk()
        ->assertJson([
            'transaction' => [
                'reference' => 'ref_action',
                'status' => 'success',
                'amount' => 1550,
                'currency' => 'NGN',
                'paidAt' => '2026-03-01T10:00:00+00:00',
            ],
        ]);
});

it('can return a facade response dto directly from a route as json', function () {
    /** @var TestCase $testCase */
    $testCase = $this;

    Route::get('/test/paystack/facade-response', function () {
        return Paystack::verifyTransaction(new VerifyTransactionInputData('ref_facade'));
    });

    app(PaystackConnector::class)->withMockClient(new MockClient([
        VerifyTransactionRequest::class => MockResponse::make([
            'status' => true,
            'message' => 'Verification successful',
            'data' => [
                'id' => 11,
                'status' => 'success',
                'reference' => 'ref_facade',
                'amount' => 2500,
                'currency' => 'NGN',
                'paid_at' => '2026-03-02T10:00:00+00:00',
            ],
        ], 200),
    ]));

    $testCase->getJson('/test/paystack/facade-response')
        ->assertOk()
        ->assertJson([
            'transaction' => [
                'reference' => 'ref_facade',
                'status' => 'success',
                'amount' => 2500,
                'currency' => 'NGN',
                'paidAt' => '2026-03-02T10:00:00+00:00',
            ],
        ]);
});
