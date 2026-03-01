<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Maxiviper117\Paystack\Actions\Transaction\InitializeTransactionAction;
use Maxiviper117\Paystack\Actions\Transaction\VerifyTransactionAction;
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Data\Input\Webhook\VerifyWebhookSignatureInputData;
use Maxiviper117\Paystack\Facades\Paystack;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/paystack/test/start', function () {
    $initializeTransaction = app(InitializeTransactionAction::class);

    $initialized = $initializeTransaction(
        new InitializeTransactionInputData(
            email: 'customer@example.com',
            amount: 15.50,
            callbackUrl: url('/paystack/test/callback'),
            metadata: [
                'source' => 'workbench',
                'purpose' => 'live-test',
            ],
        ),
    );

    return redirect()->away($initialized->authorizationUrl);
});

Route::get('/paystack/test/callback', function (Request $request) {
    $reference = (string) $request->query('reference', '');

    abort_if($reference === '', 400, 'Missing Paystack reference.');

    $verifyTransaction = app(VerifyTransactionAction::class);
    $verified = $verifyTransaction(new VerifyTransactionInputData($reference));

    return response()->json([
        'reference' => $verified->transaction->reference,
        'status' => $verified->transaction->status,
        'amount' => $verified->transaction->amount,
        'currency' => $verified->transaction->currency,
        'customer' => $verified->transaction->customer?->email,
        'raw' => $verified->transaction->raw,
    ]);
});

Route::match(['GET', 'POST'], '/paystack/test/webhook', function (Request $request) {
    if ($request->isMethod('get')) {
        return response()->json([
            'message' => 'POST a Paystack webhook payload to this route with the x-paystack-signature header to test webhook verification.',
        ]);
    }

    $event = Paystack::verifyWebhookSignature(
        new VerifyWebhookSignatureInputData(
            payload: $request->getContent(),
            signature: (string) $request->header('x-paystack-signature', ''),
        )
    );

    return response()->json([
        'event' => $event->event,
        'resource_type' => $event->resourceType,
        'id' => $event->id,
        'domain' => $event->domain,
        'occurred_at' => $event->occurredAt,
    ]);
});
