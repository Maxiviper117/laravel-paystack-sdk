<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Maxiviper117\Paystack\Actions\Transaction\InitializeTransactionAction;
use Maxiviper117\Paystack\Actions\Transaction\VerifyTransactionAction;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/paystack/test/start', function () {
    $initialized = app(InitializeTransactionAction::class)->execute(
        email: 'customer@example.com',
        amount: 15.50,
        options: [
            'callback_url' => url('/paystack/test/callback'),
            'metadata' => [
                'source' => 'workbench',
                'purpose' => 'live-test',
            ],
        ],
    );

    return redirect()->away($initialized->authorizationUrl);
});

Route::get('/paystack/test/callback', function (Request $request) {
    $reference = (string) $request->query('reference', '');

    abort_if($reference === '', 400, 'Missing Paystack reference.');

    $verified = app(VerifyTransactionAction::class)->execute($reference);

    return response()->json([
        'reference' => $verified->reference,
        'status' => $verified->status,
        'amount' => $verified->amount,
        'currency' => $verified->currency,
        'customer' => $verified->customer?->email,
        'raw' => $verified->raw,
    ]);
});
