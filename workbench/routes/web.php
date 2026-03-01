<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Maxiviper117\Paystack\Actions\Transaction\InitializeTransactionAction;
use Maxiviper117\Paystack\Actions\Transaction\VerifyTransactionAction;
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;

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
