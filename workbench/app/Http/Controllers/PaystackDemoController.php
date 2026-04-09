<?php

namespace App\Http\Controllers;

use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maxiviper117\Paystack\Actions\Customer\CreateCustomerAction;
use Maxiviper117\Paystack\Actions\Customer\FetchCustomerAction;
use Maxiviper117\Paystack\Actions\Customer\ListCustomersAction;
use Maxiviper117\Paystack\Actions\Customer\SetCustomerRiskAction;
use Maxiviper117\Paystack\Actions\Customer\UpdateCustomerAction;
use Maxiviper117\Paystack\Actions\Customer\ValidateCustomerAction;
use Maxiviper117\Paystack\Actions\Dispute\AddDisputeEvidenceAction;
use Maxiviper117\Paystack\Actions\Dispute\ExportDisputesAction;
use Maxiviper117\Paystack\Actions\Dispute\FetchDisputeAction;
use Maxiviper117\Paystack\Actions\Dispute\GetDisputeUploadUrlAction;
use Maxiviper117\Paystack\Actions\Dispute\ListDisputesAction;
use Maxiviper117\Paystack\Actions\Dispute\ListTransactionDisputesAction;
use Maxiviper117\Paystack\Actions\Dispute\ResolveDisputeAction;
use Maxiviper117\Paystack\Actions\Dispute\UpdateDisputeAction;
use Maxiviper117\Paystack\Actions\Plan\CreatePlanAction;
use Maxiviper117\Paystack\Actions\Plan\FetchPlanAction;
use Maxiviper117\Paystack\Actions\Plan\ListPlansAction;
use Maxiviper117\Paystack\Actions\Plan\UpdatePlanAction;
use Maxiviper117\Paystack\Actions\Refund\CreateRefundAction;
use Maxiviper117\Paystack\Actions\Refund\FetchRefundAction;
use Maxiviper117\Paystack\Actions\Refund\ListRefundsAction;
use Maxiviper117\Paystack\Actions\Refund\RetryRefundAction;
use Maxiviper117\Paystack\Actions\Subscription\CreateSubscriptionAction;
use Maxiviper117\Paystack\Actions\Subscription\DisableSubscriptionAction;
use Maxiviper117\Paystack\Actions\Subscription\EnableSubscriptionAction;
use Maxiviper117\Paystack\Actions\Subscription\FetchSubscriptionAction;
use Maxiviper117\Paystack\Actions\Subscription\GenerateSubscriptionUpdateLinkAction;
use Maxiviper117\Paystack\Actions\Subscription\ListSubscriptionsAction;
use Maxiviper117\Paystack\Actions\Subscription\SendSubscriptionUpdateLinkAction;
use Maxiviper117\Paystack\Actions\Transaction\InitializeTransactionAction;
use Maxiviper117\Paystack\Actions\Transaction\VerifyTransactionAction;
use Maxiviper117\Paystack\Data\Input\Customer\CreateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\FetchCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\ListCustomersInputData;
use Maxiviper117\Paystack\Data\Input\Customer\SetCustomerRiskActionInputData;
use Maxiviper117\Paystack\Data\Input\Customer\UpdateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\ValidateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Dispute\AddDisputeEvidenceInputData;
use Maxiviper117\Paystack\Data\Input\Dispute\FetchDisputeInputData;
use Maxiviper117\Paystack\Data\Input\Dispute\GetDisputeUploadUrlInputData;
use Maxiviper117\Paystack\Data\Input\Dispute\ListDisputesInputData;
use Maxiviper117\Paystack\Data\Input\Dispute\ListTransactionDisputesInputData;
use Maxiviper117\Paystack\Data\Input\Dispute\ResolveDisputeInputData;
use Maxiviper117\Paystack\Data\Input\Dispute\UpdateDisputeInputData;
use Maxiviper117\Paystack\Data\Input\Plan\CreatePlanInputData;
use Maxiviper117\Paystack\Data\Input\Plan\FetchPlanInputData;
use Maxiviper117\Paystack\Data\Input\Plan\ListPlansInputData;
use Maxiviper117\Paystack\Data\Input\Plan\UpdatePlanInputData;
use Maxiviper117\Paystack\Data\Input\Refund\CreateRefundInputData;
use Maxiviper117\Paystack\Data\Input\Refund\FetchRefundInputData;
use Maxiviper117\Paystack\Data\Input\Refund\ListRefundsInputData;
use Maxiviper117\Paystack\Data\Input\Refund\RefundAccountDetailsInputData;
use Maxiviper117\Paystack\Data\Input\Refund\RetryRefundInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\CreateSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\DisableSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\EnableSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\FetchSubscriptionInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\GenerateSubscriptionUpdateLinkInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\ListSubscriptionsInputData;
use Maxiviper117\Paystack\Data\Input\Subscription\SendSubscriptionUpdateLinkInputData;
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
        $callbackReference = $this->callbackTransactionReference($request);

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
                        channels: $this->commaSeparatedValues($request->input('channels')),
                        callbackUrl: (string) $request->input('callback_url', url('/paystack/demo/transactions')),
                        reference: $request->filled('reference') ? (string) $request->input('reference') : null,
                        plan: $request->filled('plan') ? (string) $request->input('plan') : null,
                        invoiceLimit: $request->filled('invoice_limit') ? $request->integer('invoice_limit') : null,
                        metadata: [
                            'source' => 'workbench',
                            'page' => 'transactions',
                        ],
                        currency: $request->filled('currency') ? (string) $request->input('currency') : null,
                        splitCode: $request->filled('split_code') ? (string) $request->input('split_code') : null,
                        subaccount: $request->filled('subaccount') ? (string) $request->input('subaccount') : null,
                        transactionCharge: $request->filled('transaction_charge') ? $request->integer('transaction_charge') : null,
                        bearer: $request->filled('bearer') ? (string) $request->input('bearer') : null,
                    )),
                    'Transaction initialization',
                ],
            };
        });

        if ($result === null && $request->isMethod('get') && $callbackReference !== null) {
            try {
                $result = $verifyTransaction(new VerifyTransactionInputData($callbackReference));
                $resultLabel = 'Transaction verification';
            } catch (Throwable $throwable) {
                $result = [
                    'error' => $throwable->getMessage(),
                    'type' => $throwable::class,
                ];
                $resultLabel = 'Transaction verification';
            }
        }

        $verificationNotice = $this->verificationNotice($request, $result, $resultLabel);

        return $this->render('transactions', [
            'title' => 'Transactions Demo',
            'heading' => 'Transactions',
            'description' => 'Initialize a checkout or verify a returned reference.',
            'result' => $result,
            'resultLabel' => $resultLabel,
            'callbackReference' => $callbackReference,
            'verificationNotice' => $verificationNotice,
            'currentPath' => '/paystack/demo/transactions',
        ]);
    }

    public function customers(
        Request $request,
        CreateCustomerAction $createCustomer,
        FetchCustomerAction $fetchCustomer,
        UpdateCustomerAction $updateCustomer,
        ListCustomersAction $listCustomers,
        ValidateCustomerAction $validateCustomer,
        SetCustomerRiskAction $setCustomerRiskAction,
    ): View {
        [$result, $resultLabel] = $this->capturePost($request, function () use ($request, $createCustomer, $fetchCustomer, $updateCustomer, $listCustomers, $validateCustomer, $setCustomerRiskAction): array {
            return match ((string) $request->input('action', 'create')) {
                'fetch' => [
                    $fetchCustomer(new FetchCustomerInputData((string) $request->input('customer_identifier', ''))),
                    'Customer fetch',
                ],
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
                'validate' => [
                    $validateCustomer(new ValidateCustomerInputData(
                        customerCode: (string) $request->input('customer_code', ''),
                        country: (string) $request->input('country', 'NG'),
                        type: (string) $request->input('type', 'bank_account'),
                        firstName: $request->filled('first_name') ? (string) $request->input('first_name') : null,
                        lastName: $request->filled('last_name') ? (string) $request->input('last_name') : null,
                        middleName: $request->filled('middle_name') ? (string) $request->input('middle_name') : null,
                        bvn: $request->filled('bvn') ? (string) $request->input('bvn') : null,
                        bankCode: $request->filled('bank_code') ? (string) $request->input('bank_code') : null,
                        accountNumber: $request->filled('account_number') ? (string) $request->input('account_number') : null,
                    )),
                    'Customer validation',
                ],
                'risk-action' => [
                    $setCustomerRiskAction(new SetCustomerRiskActionInputData(
                        customer: (string) $request->input('customer', ''),
                        riskAction: $request->filled('risk_action') ? (string) $request->input('risk_action') : null,
                    )),
                    'Customer risk action',
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
            'description' => 'Create, fetch, update, validate, risk-manage, and list customer records.',
            'result' => $result,
            'resultLabel' => $resultLabel,
            'currentPath' => '/paystack/demo/customers',
        ]);
    }

    public function disputes(
        Request $request,
        ListDisputesAction $listDisputes,
        FetchDisputeAction $fetchDispute,
        ListTransactionDisputesAction $listTransactionDisputes,
        UpdateDisputeAction $updateDispute,
        AddDisputeEvidenceAction $addDisputeEvidence,
        GetDisputeUploadUrlAction $getDisputeUploadUrl,
        ResolveDisputeAction $resolveDispute,
        ExportDisputesAction $exportDisputes,
    ): View {
        [$result, $resultLabel] = $this->capturePost($request, function () use ($request, $listDisputes, $fetchDispute, $listTransactionDisputes, $updateDispute, $addDisputeEvidence, $getDisputeUploadUrl, $resolveDispute, $exportDisputes): array {
            return match ((string) $request->input('action', 'list')) {
                'fetch' => [
                    $fetchDispute(new FetchDisputeInputData((string) $request->input('dispute_identifier', ''))),
                    'Dispute fetch',
                ],
                'transaction' => [
                    $listTransactionDisputes(new ListTransactionDisputesInputData((string) $request->input('transaction_identifier', ''))),
                    'Transaction disputes',
                ],
                'update' => [
                    $updateDispute(new UpdateDisputeInputData(
                        id: (string) $request->input('dispute_id', ''),
                        refundAmount: $request->filled('refund_amount') ? $request->integer('refund_amount') : null,
                        uploadedFilename: $request->filled('uploaded_filename') ? (string) $request->input('uploaded_filename') : null,
                    )),
                    'Dispute update',
                ],
                'evidence' => [
                    $addDisputeEvidence(new AddDisputeEvidenceInputData(
                        id: (string) $request->input('dispute_id', ''),
                        customerEmail: (string) $request->input('customer_email', 'customer@example.com'),
                        customerName: (string) $request->input('customer_name', 'Jane Doe'),
                        customerPhone: (string) $request->input('customer_phone', '08023456789'),
                        serviceDetails: (string) $request->input('service_details', 'Service details'),
                        deliveryAddress: $request->filled('delivery_address') ? (string) $request->input('delivery_address') : null,
                        deliveryDate: $request->filled('delivery_date') ? (string) $request->input('delivery_date') : null,
                    )),
                    'Dispute evidence',
                ],
                'upload-url' => [
                    $getDisputeUploadUrl(new GetDisputeUploadUrlInputData(
                        id: (string) $request->input('dispute_id', ''),
                        uploadFilename: (string) $request->input('upload_filename', 'evidence.pdf'),
                    )),
                    'Dispute upload URL',
                ],
                'resolve' => [
                    $resolveDispute(new ResolveDisputeInputData(
                        id: (string) $request->input('dispute_id', ''),
                        resolution: (string) $request->input('resolution', 'merchant-accepted'),
                        message: $request->filled('message') ? (string) $request->input('message') : null,
                        refundAmount: $request->filled('refund_amount') ? $request->integer('refund_amount') : null,
                        uploadedFilename: $request->filled('uploaded_filename') ? (string) $request->input('uploaded_filename') : null,
                        evidence: $request->filled('evidence') ? $request->integer('evidence') : null,
                    )),
                    'Dispute resolution',
                ],
                'export' => [
                    $exportDisputes(new ListDisputesInputData(
                        from: $request->filled('from') ? (string) $request->input('from') : null,
                        to: $request->filled('to') ? (string) $request->input('to') : null,
                        perPage: $request->integer('per_page') ?: 10,
                        page: $request->integer('page') ?: 1,
                        transaction: $request->filled('transaction') ? (string) $request->input('transaction') : null,
                        status: $request->filled('status') ? (string) $request->input('status') : null,
                    )),
                    'Dispute export',
                ],
                default => [
                    $listDisputes(new ListDisputesInputData(
                        from: $request->filled('from') ? (string) $request->input('from') : null,
                        to: $request->filled('to') ? (string) $request->input('to') : null,
                        perPage: $request->integer('per_page') ?: 10,
                        page: $request->integer('page') ?: 1,
                        transaction: $request->filled('transaction') ? (string) $request->input('transaction') : null,
                        status: $request->filled('status') ? (string) $request->input('status') : null,
                    )),
                    'Dispute list',
                ],
            };
        });

        return $this->render('disputes', [
            'title' => 'Disputes Demo',
            'heading' => 'Disputes',
            'description' => 'List, fetch, update, resolve, and export disputes, plus upload and evidence helpers.',
            'result' => $result,
            'resultLabel' => $resultLabel,
            'currentPath' => '/paystack/demo/disputes',
        ]);
    }

    public function refunds(
        Request $request,
        CreateRefundAction $createRefund,
        RetryRefundAction $retryRefund,
        FetchRefundAction $fetchRefund,
        ListRefundsAction $listRefunds,
    ): View {
        [$result, $resultLabel] = $this->capturePost($request, function () use ($request, $createRefund, $retryRefund, $fetchRefund, $listRefunds): array {
            return match ((string) $request->input('action', 'create')) {
                'fetch' => [
                    $fetchRefund(new FetchRefundInputData($request->input('refund_id', ''))),
                    'Refund fetch',
                ],
                'list' => [
                    $listRefunds(new ListRefundsInputData(
                        transaction: $request->filled('transaction') ? $request->input('transaction') : null,
                        currency: $request->filled('currency') ? (string) $request->input('currency') : null,
                        from: $request->filled('from') ? (string) $request->input('from') : null,
                        to: $request->filled('to') ? (string) $request->input('to') : null,
                        perPage: $request->integer('per_page') ?: 10,
                        page: $request->integer('page') ?: 1,
                    )),
                    'Refund list',
                ],
                'retry' => [
                    $retryRefund(new RetryRefundInputData(
                        id: $request->input('refund_id', ''),
                        refundAccountDetails: new RefundAccountDetailsInputData(
                            currency: (string) $request->input('refund_currency', 'NGN'),
                            accountNumber: (string) $request->input('account_number', ''),
                            bankId: (string) $request->input('bank_id', ''),
                        ),
                    )),
                    'Refund retry',
                ],
                default => [
                    $createRefund(new CreateRefundInputData(
                        transaction: $request->input('transaction', ''),
                        amount: $request->filled('amount') ? $request->integer('amount') : null,
                        currency: $request->filled('currency') ? (string) $request->input('currency') : null,
                        customerNote: $request->filled('customer_note') ? (string) $request->input('customer_note') : null,
                        merchantNote: $request->filled('merchant_note') ? (string) $request->input('merchant_note') : null,
                    )),
                    'Refund creation',
                ],
            };
        });

        return $this->render('refunds', [
            'title' => 'Refunds Demo',
            'heading' => 'Refunds',
            'description' => 'Create, retry, fetch, and list refunds.',
            'result' => $result,
            'resultLabel' => $resultLabel,
            'currentPath' => '/paystack/demo/refunds',
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
        GenerateSubscriptionUpdateLinkAction $generateSubscriptionUpdateLink,
        SendSubscriptionUpdateLinkAction $sendSubscriptionUpdateLink,
    ): View {
        [$result, $resultLabel] = $this->capturePost($request, function () use ($request, $createSubscription, $fetchSubscription, $listSubscriptions, $enableSubscription, $disableSubscription, $generateSubscriptionUpdateLink, $sendSubscriptionUpdateLink): array {
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
                'generate-link' => [
                    $generateSubscriptionUpdateLink(new GenerateSubscriptionUpdateLinkInputData(
                        code: (string) $request->input('code', ''),
                    )),
                    'Subscription update-link generation',
                ],
                'send-link' => [
                    $sendSubscriptionUpdateLink(new SendSubscriptionUpdateLinkInputData(
                        code: (string) $request->input('code', ''),
                    )),
                    'Subscription update-link email',
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
            'description' => 'Create, fetch, list, enable, disable, and manage subscription update links.',
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
            ['title' => 'Disputes', 'path' => '/paystack/demo/disputes', 'description' => 'List, fetch, update, and resolve disputes.'],
            ['title' => 'Refunds', 'path' => '/paystack/demo/refunds', 'description' => 'Create, retry, fetch, and list refunds.'],
            ['title' => 'Plans', 'path' => '/paystack/demo/plans', 'description' => 'Create, update, fetch, and list plans.'],
            ['title' => 'Subscriptions', 'path' => '/paystack/demo/subscriptions', 'description' => 'Create, fetch, list, enable, disable, and manage subscription update links.'],
            ['title' => 'Webhooks', 'path' => '/paystack/demo/webhooks', 'description' => 'Inspect webhook intake and stored calls.'],
            ['title' => 'Billing Layer', 'path' => '/paystack/demo/billing-layer', 'description' => 'Exercise the opt-in Billable layer.'],
        ];
    }

    /**
     * @return list<string>|null
     */
    private function commaSeparatedValues(mixed $value): ?array
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $values = array_values(array_filter(array_map('trim', explode(',', $value)), static fn (string $item): bool => $item !== ''));

        return $values === [] ? null : $values;
    }

    private function callbackTransactionReference(Request $request): ?string
    {
        $reference = (string) ($request->query('reference') ?? $request->query('trxref') ?? '');

        return trim($reference) === '' ? null : $reference;
    }

    /**
     * @return array{title: string, message: string, tone: 'success'|'danger'}|null
     */
    private function verificationNotice(Request $request, mixed $result, ?string $resultLabel): ?array
    {
        $isVerificationContext = $resultLabel === 'Transaction verification'
            || ($request->isMethod('post') && (string) $request->input('action', '') === 'verify');

        if (! $isVerificationContext) {
            return null;
        }

        $error = data_get($result, 'error');

        if (is_string($error) && trim($error) !== '') {
            return [
                'title' => 'Verification failed',
                'message' => $error,
                'tone' => 'danger',
            ];
        }

        $status = strtolower((string) data_get($result, 'transaction.status', ''));
        $reference = (string) data_get($result, 'transaction.reference', '');

        if ($status === 'success') {
            return [
                'title' => 'Verification successful',
                'message' => $reference !== ''
                    ? "Transaction {$reference} was verified successfully."
                    : 'Transaction verification was successful.',
                'tone' => 'success',
            ];
        }

        if ($status !== '') {
            return [
                'title' => 'Verification failed',
                'message' => $reference !== ''
                    ? "Transaction {$reference} returned status {$status}."
                    : "Transaction verification returned status {$status}.",
                'tone' => 'danger',
            ];
        }

        return null;
    }
}
