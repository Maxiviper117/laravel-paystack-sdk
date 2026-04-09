<?php

namespace App\Http\Controllers;

use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maxiviper117\Paystack\Actions\Customer\CreateCustomerAction;
use Maxiviper117\Paystack\Actions\Customer\ListCustomersAction;
use Maxiviper117\Paystack\Actions\Customer\UpdateCustomerAction;
use Maxiviper117\Paystack\Actions\Plan\CreatePlanAction;
use Maxiviper117\Paystack\Actions\Plan\FetchPlanAction;
use Maxiviper117\Paystack\Actions\Plan\ListPlansAction;
use Maxiviper117\Paystack\Actions\Plan\UpdatePlanAction;
use Maxiviper117\Paystack\Actions\Subscription\CreateSubscriptionAction;
use Maxiviper117\Paystack\Actions\Subscription\DisableSubscriptionAction;
use Maxiviper117\Paystack\Actions\Subscription\EnableSubscriptionAction;
use Maxiviper117\Paystack\Actions\Subscription\FetchSubscriptionAction;
use Maxiviper117\Paystack\Actions\Subscription\ListSubscriptionsAction;
use Maxiviper117\Paystack\Actions\Transaction\InitializeTransactionAction;
use Maxiviper117\Paystack\Actions\Transaction\VerifyTransactionAction;
use Maxiviper117\Paystack\Data\Input\Customer\CreateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\ListCustomersInputData;
use Maxiviper117\Paystack\Data\Input\Customer\UpdateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Plan\CreatePlanInputData;
use Maxiviper117\Paystack\Data\Input\Plan\FetchPlanInputData;
use Maxiviper117\Paystack\Data\Input\Plan\ListPlansInputData;
use Maxiviper117\Paystack\Data\Input\Plan\UpdatePlanInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\CreateSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\DisableSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\EnableSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\FetchSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\ListSubscriptionsInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Models\PaystackWebhookCall;
use Throwable;

class PaystackDemoController extends Controller
{
    public function index(): View
    {
        return $this->render('index', [
            'title' => 'Paystack Workbench Demo',
            'heading' => 'Interactive demo pages for every Paystack feature in this package.',
            'description' => 'Use the feature pages to initialize and verify transactions, manage customers and plans, create subscriptions, inspect webhook intake, and exercise the optional billing layer.',
        ]);
    }

    public function playground(Request $request): View|RedirectResponse
    {
        if ($request->isMethod('post')) {
            return redirect('/paystack/demo');
        }

        return $this->render('playground', [
            'title' => 'Legacy Playground',
            'heading' => 'The old single-page playground has been split into separate feature pages.',
            'description' => 'Use the demo hub below to open the dedicated transaction, customer, plan, subscription, webhook, and billing pages.',
        ]);
    }

    public function transactions(
        Request $request,
        InitializeTransactionAction $initializeTransaction,
        VerifyTransactionAction $verifyTransaction,
    ): View {
        [$result, $resultLabel] = $this->capturePost($request, function () use ($request, $initializeTransaction, $verifyTransaction): array {
            return match ((string) $request->input('action', 'initialize')) {
                'verify' => [
                    $verifyTransaction(new VerifyTransactionInputData((string) $request->input('reference', ''))),
                    'Transaction verification',
                ],
                default => [
                    $initializeTransaction(new InitializeTransactionInputData(
                        email: (string) $request->input('email', 'customer@example.com'),
                        amount: (float) $request->input('amount', 15.50),
                        callbackUrl: (string) $request->input('callback_url', url('/paystack/demo/transactions')),
                        metadata: [
                            'source' => 'workbench',
                            'page' => 'transactions',
                        ],
                    )),
                    'Transaction initialization',
                ],
            };
        });

        return $this->render('transactions', [
            'title' => 'Transactions Demo',
            'heading' => 'Transactions',
            'description' => 'Initialize a checkout or verify a returned reference.',
            'result' => $result,
            'resultLabel' => $resultLabel,
            'currentPath' => '/paystack/demo/transactions',
        ]);
    }

    public function customers(
        Request $request,
        CreateCustomerAction $createCustomer,
        UpdateCustomerAction $updateCustomer,
        ListCustomersAction $listCustomers,
    ): View {
        [$result, $resultLabel] = $this->capturePost($request, function () use ($request, $createCustomer, $updateCustomer, $listCustomers): array {
            return match ((string) $request->input('action', 'create')) {
                'update' => [
                    $updateCustomer(new UpdateCustomerInputData(
                        customerCode: (string) $request->input('customer_code', ''),
                        email: $request->filled('email') ? (string) $request->input('email') : null,
                        firstName: $request->filled('first_name') ? (string) $request->input('first_name') : null,
                        lastName: $request->filled('last_name') ? (string) $request->input('last_name') : null,
                        phone: $request->filled('phone') ? (string) $request->input('phone') : null,
                        metadata: ['source' => 'workbench', 'page' => 'customers'],
                    )),
                    'Customer update',
                ],
                'list' => [
                    $listCustomers(new ListCustomersInputData(
                        perPage: $request->integer('per_page') ?: 10,
                        page: $request->integer('page') ?: 1,
                        email: $request->filled('list_email') ? (string) $request->input('list_email') : null,
                    )),
                    'Customer list',
                ],
                default => [
                    $createCustomer(new CreateCustomerInputData(
                        email: (string) $request->input('email', 'customer@example.com'),
                        firstName: $request->filled('first_name') ? (string) $request->input('first_name') : null,
                        lastName: $request->filled('last_name') ? (string) $request->input('last_name') : null,
                        phone: $request->filled('phone') ? (string) $request->input('phone') : null,
                        metadata: ['source' => 'workbench', 'page' => 'customers'],
                    )),
                    'Customer creation',
                ],
            };
        });

        return $this->render('customers', [
            'title' => 'Customers Demo',
            'heading' => 'Customers',
            'description' => 'Create, update, and list customer records.',
            'result' => $result,
            'resultLabel' => $resultLabel,
            'currentPath' => '/paystack/demo/customers',
        ]);
    }

    public function plans(
        Request $request,
        CreatePlanAction $createPlan,
        UpdatePlanAction $updatePlan,
        FetchPlanAction $fetchPlan,
        ListPlansAction $listPlans,
    ): View {
        [$result, $resultLabel] = $this->capturePost($request, function () use ($request, $createPlan, $updatePlan, $fetchPlan, $listPlans): array {
            return match ((string) $request->input('action', 'create')) {
                'update' => [
                    $updatePlan(new UpdatePlanInputData(
                        planCode: (string) $request->input('plan_code', ''),
                        name: (string) $request->input('name', ''),
                        amount: (float) $request->input('amount', 25.00),
                        interval: (string) $request->input('interval', 'monthly'),
                        description: $request->filled('description') ? (string) $request->input('description') : null,
                    )),
                    'Plan update',
                ],
                'fetch' => [
                    $fetchPlan(new FetchPlanInputData((string) $request->input('plan_identifier', ''))),
                    'Plan fetch',
                ],
                'list' => [
                    $listPlans(new ListPlansInputData(
                        perPage: $request->integer('per_page') ?: 10,
                        page: $request->integer('page') ?: 1,
                    )),
                    'Plan list',
                ],
                default => [
                    $createPlan(new CreatePlanInputData(
                        name: (string) $request->input('name', 'Workbench Plan'),
                        amount: (float) $request->input('amount', 25.00),
                        interval: (string) $request->input('interval', 'monthly'),
                        description: $request->filled('description') ? (string) $request->input('description') : 'Created from the workbench demo page.',
                    )),
                    'Plan creation',
                ],
            };
        });

        return $this->render('plans', [
            'title' => 'Plans Demo',
            'heading' => 'Plans',
            'description' => 'Create, update, fetch, and list plans.',
            'result' => $result,
            'resultLabel' => $resultLabel,
            'currentPath' => '/paystack/demo/plans',
        ]);
    }

    public function subscriptions(
        Request $request,
        CreateSubscriptionAction $createSubscription,
        FetchSubscriptionAction $fetchSubscription,
        ListSubscriptionsAction $listSubscriptions,
        EnableSubscriptionAction $enableSubscription,
        DisableSubscriptionAction $disableSubscription,
    ): View {
        [$result, $resultLabel] = $this->capturePost($request, function () use ($request, $createSubscription, $fetchSubscription, $listSubscriptions, $enableSubscription, $disableSubscription): array {
            return match ((string) $request->input('action', 'create')) {
                'fetch' => [
                    $fetchSubscription(new FetchSubscriptionInputData((string) $request->input('subscription_identifier', ''))),
                    'Subscription fetch',
                ],
                'list' => [
                    $listSubscriptions(new ListSubscriptionsInputData(
                        customer: $request->filled('customer') ? (string) $request->input('customer') : null,
                        plan: $request->filled('plan') ? (string) $request->input('plan') : null,
                        perPage: $request->integer('per_page') ?: 10,
                        page: $request->integer('page') ?: 1,
                    )),
                    'Subscription list',
                ],
                'enable' => [
                    $enableSubscription(new EnableSubscriptionInputData(
                        code: (string) $request->input('code', ''),
                        token: (string) $request->input('token', ''),
                    )),
                    'Subscription enable',
                ],
                'disable' => [
                    $disableSubscription(new DisableSubscriptionInputData(
                        code: (string) $request->input('code', ''),
                        token: (string) $request->input('token', ''),
                    )),
                    'Subscription disable',
                ],
                default => [
                    $createSubscription(new CreateSubscriptionInputData(
                        customer: (string) $request->input('customer', ''),
                        plan: (string) $request->input('plan', ''),
                        authorization: $request->filled('authorization') ? (string) $request->input('authorization') : null,
                        startDate: $request->filled('start_date') ? (string) $request->input('start_date') : null,
                    )),
                    'Subscription creation',
                ],
            };
        });

        return $this->render('subscriptions', [
            'title' => 'Subscriptions Demo',
            'heading' => 'Subscriptions',
            'description' => 'Create, fetch, list, enable, and disable subscriptions.',
            'result' => $result,
            'resultLabel' => $resultLabel,
            'currentPath' => '/paystack/demo/subscriptions',
        ]);
    }

    public function webhooks(): View
    {
        return $this->render('webhooks', [
            'title' => 'Webhooks Demo',
            'heading' => 'Webhooks',
            'description' => 'Inspect the webhook endpoint, latest stored call, and the latest cached parsed event.',
            'latestWebhookCall' => PaystackWebhookCall::query()->latest()->first(),
            'latestWebhookEvent' => cache()->get('paystack:last-webhook-event'),
            'currentPath' => '/paystack/demo/webhooks',
        ]);
    }

    public function billingLayer(Request $request): View
    {
        [$result, $resultLabel] = $this->capturePost($request, function () use ($request): array {
            $user = User::query()->firstOrCreate(
                ['email' => (string) $request->input('email', 'billable@example.com')],
                [
                    'name' => (string) $request->input('name', 'Billable Demo User'),
                    'password' => 'password',
                ],
            );

            $result = match ((string) $request->input('action', 'sync')) {
                'create-subscription' => $user->createPaystackSubscription(
                    planCode: (string) $request->input('plan', ''),
                    name: (string) $request->input('subscription_name', 'default'),
                    authorization: $request->filled('authorization') ? (string) $request->input('authorization') : null,
                    startDate: $request->filled('start_date') ? (string) $request->input('start_date') : null,
                ),
                'enable' => $user->enablePaystackSubscription((string) $request->input('subscription_name', 'default')),
                'disable' => $user->disablePaystackSubscription((string) $request->input('subscription_name', 'default')),
                default => $user->syncAsPaystackCustomer(),
            };

            $user->load(['paystackCustomer', 'paystackSubscriptions']);

            return [[
                'user' => $user->only(['id', 'name', 'email']),
                'paystack_customer' => $user->paystackCustomer?->only(['id', 'customer_code', 'email']),
                'paystack_subscriptions' => $user->paystackSubscriptions->map(static fn (mixed $subscription): array => $subscription->only([
                    'id',
                    'name',
                    'subscription_code',
                    'status',
                    'plan_code',
                    'email_token',
                    'next_payment_date',
                ]))->values(),
                'result' => $result,
            ], 'Billing layer'];
        });

        return $this->render('billing-layer', [
            'title' => 'Billing Layer Demo',
            'heading' => 'Billing layer',
            'description' => 'Test the optional Billable trait and the local customer/subscription tables.',
            'result' => $result,
            'resultLabel' => $resultLabel,
            'currentPath' => '/paystack/demo/billing-layer',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function render(string $view, array $data = []): View
    {
        return view("paystack-demo.$view", array_merge([
            'pages' => $this->pages(),
        ], $data));
    }

    /**
     * @return array{0: array<string, mixed>|object|null, 1: string|null}
     */
    private function capturePost(Request $request, Closure $callback): array
    {
        if (! $request->isMethod('post')) {
            return [null, null];
        }

        try {
            return $callback();
        } catch (Throwable $throwable) {
            return [
                [
                    'error' => $throwable->getMessage(),
                    'type' => $throwable::class,
                ],
                'Error',
            ];
        }
    }

    /**
     * @return array<int, array{title: string, path: string, description: string}>
     */
    private function pages(): array
    {
        return [
            ['title' => 'Transactions', 'path' => '/paystack/demo/transactions', 'description' => 'Initialize and verify payment flows.'],
            ['title' => 'Customers', 'path' => '/paystack/demo/customers', 'description' => 'Create, update, and list customers.'],
            ['title' => 'Plans', 'path' => '/paystack/demo/plans', 'description' => 'Create, update, fetch, and list plans.'],
            ['title' => 'Subscriptions', 'path' => '/paystack/demo/subscriptions', 'description' => 'Create, fetch, list, enable, and disable subscriptions.'],
            ['title' => 'Webhooks', 'path' => '/paystack/demo/webhooks', 'description' => 'Inspect webhook intake and stored calls.'],
            ['title' => 'Billing Layer', 'path' => '/paystack/demo/billing-layer', 'description' => 'Exercise the opt-in Billable layer.'],
        ];
    }
}
