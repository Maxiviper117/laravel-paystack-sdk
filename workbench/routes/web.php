<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Maxiviper117\Paystack\Actions\Transaction\InitializeTransactionAction;
use Maxiviper117\Paystack\Actions\Transaction\VerifyTransactionAction;
use Maxiviper117\Paystack\Data\Input\Plan\CreatePlanInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\CreateSubscriptionInputData;
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

Route::match(['GET', 'POST'], '/paystack/test/plan', function (Request $request) {
    if ($request->isMethod('get')) {
        return response()->json([
            'message' => 'POST plan fields such as name, amount, and interval to create a plan with the local package.',
        ]);
    }

    $result = Paystack::createPlan(new CreatePlanInputData(
        name: (string) $request->input('name', 'Workbench Starter Plan'),
        amount: (int) $request->input('amount', 5000),
        interval: (string) $request->input('interval', 'monthly'),
        description: $request->filled('description') ? (string) $request->input('description') : 'Created from the workbench route.',
    ));

    return response()->json([
        'plan_code' => $result->plan->planCode,
        'amount' => $result->plan->amount,
        'interval' => $result->plan->interval,
    ]);
});

Route::match(['GET', 'POST'], '/paystack/test/subscription', function (Request $request) {
    if ($request->isMethod('get')) {
        return response()->json([
            'message' => 'POST customer and plan fields to create a subscription with the local package.',
        ]);
    }

    $result = Paystack::createSubscription(new CreateSubscriptionInputData(
        customer: (string) $request->input('customer', ''),
        plan: (string) $request->input('plan', ''),
        authorization: $request->filled('authorization') ? (string) $request->input('authorization') : null,
        startDate: $request->filled('start_date') ? (string) $request->input('start_date') : null,
    ));

    return response()->json([
        'subscription_code' => $result->subscription->subscriptionCode,
        'status' => $result->subscription->status,
        'customer' => $result->subscription->customer?->customerCode,
    ]);
});
