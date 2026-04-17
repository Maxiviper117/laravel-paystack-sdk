<?php

use App\Http\Controllers\PaystackDemoController;
use App\Http\Controllers\PaystackTestController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Maxiviper117\Paystack\Data\Input\Plan\CreatePlanInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\CreateSubscriptionInputData;
use Maxiviper117\Paystack\Facades\Paystack;

Route::get('/', function () {
    return redirect('/paystack/demo');
});

Route::get('/paystack/demo', [PaystackDemoController::class, 'index']);

Route::match(['GET', 'POST'], '/paystack/demo/playground', [PaystackDemoController::class, 'playground']);
Route::match(['GET', 'POST'], '/paystack/demo/transactions', [PaystackDemoController::class, 'transactions']);
Route::match(['GET', 'POST'], '/paystack/demo/customers', [PaystackDemoController::class, 'customers']);
Route::match(['GET', 'POST'], '/paystack/demo/disputes', [PaystackDemoController::class, 'disputes']);
Route::match(['GET', 'POST'], '/paystack/demo/refunds', [PaystackDemoController::class, 'refunds']);
Route::match(['GET', 'POST'], '/paystack/demo/plans', [PaystackDemoController::class, 'plans']);
Route::match(['GET', 'POST'], '/paystack/demo/subscriptions', [PaystackDemoController::class, 'subscriptions']);
Route::get('/paystack/demo/webhooks', [PaystackDemoController::class, 'webhooks']);
Route::match(['GET', 'POST'], '/paystack/demo/billing-layer', [PaystackDemoController::class, 'billingLayer']);
Route::match(['GET', 'POST'], '/paystack/demo/billing-sync', [PaystackDemoController::class, 'billingSync']);

Route::get('/paystack/test/start', [PaystackTestController::class, 'start']);

Route::get('/paystack/test/callback', [PaystackTestController::class, 'callback']);

Route::get('/paystack/test/customers', [PaystackTestController::class, 'customers']);

Route::match(['GET', 'POST'], '/paystack/test/plan', function (Request $request) {
    if ($request->isMethod('get')) {
        return response()->json([
            'message' => 'POST plan fields such as name, amount, and interval to create a plan with the local package.',
        ]);
    }

    return Paystack::createPlan(new CreatePlanInputData(
        name: (string) $request->input('name', 'Workbench Starter Plan'),
        amount: (int) $request->input('amount', 5000),
        interval: (string) $request->input('interval', 'monthly'),
        description: $request->filled('description') ? (string) $request->input('description') : 'Created from the workbench route.',
    ));
});

Route::match(['GET', 'POST'], '/paystack/test/subscription', function (Request $request) {
    if ($request->isMethod('get')) {
        return response()->json([
            'message' => 'POST customer and plan fields to create a subscription with the local package.',
        ]);
    }

    return Paystack::createSubscription(new CreateSubscriptionInputData(
        customer: (string) $request->input('customer', ''),
        plan: (string) $request->input('plan', ''),
        authorization: $request->filled('authorization') ? (string) $request->input('authorization') : null,
        startDate: $request->filled('start_date') ? (string) $request->input('start_date') : null,
    ));
});

Route::match(['GET', 'POST'], '/paystack/test/billing-layer', function (Request $request) {
    if ($request->isMethod('get')) {
        return response()->json([
            'message' => 'POST an email and plan to exercise the optional billing lifecycle layer. Publish the package billing migrations first to mirror customers, plans, subscriptions, transactions, refunds, and disputes locally.',
        ]);
    }

    $user = User::query()->firstOrCreate(
        ['email' => (string) $request->input('email', 'billable@example.com')],
        [
            'name' => (string) $request->input('name', 'Billable Test User'),
            'password' => 'password',
        ],
    );

    $subscription = Paystack::createBillableSubscription(
        billable: $user,
        planCode: (string) $request->input('plan', ''),
        name: (string) $request->input('subscription_name', 'default'),
        authorization: $request->filled('authorization') ? (string) $request->input('authorization') : null,
        startDate: $request->filled('start_date') ? (string) $request->input('start_date') : null,
    );

    return response()->json([
        'user_id' => $user->getKey(),
        'customer' => $user->paystackCustomer?->only(['id', 'customer_code', 'email']),
        'subscription' => $user->paystackSubscription((string) $request->input('subscription_name', 'default'))?->only([
            'id',
            'name',
            'subscription_code',
            'status',
            'plan_code',
            'email_token',
            'next_payment_date',
        ]),
        'response' => $subscription->toArray(),
    ]);
});
