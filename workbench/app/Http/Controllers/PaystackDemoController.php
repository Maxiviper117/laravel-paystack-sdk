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
use Maxiviper117\Paystack\Actions\Transaction\FetchTransactionAction;
use Maxiviper117\Paystack\Actions\Transaction\InitializeTransactionAction;
use Maxiviper117\Paystack\Actions\Transaction\ListTransactionsAction;
use Maxiviper117\Paystack\Actions\Transaction\VerifyTransactionAction;
use Maxiviper117\Paystack\Data\Customer\CustomerData;
use Maxiviper117\Paystack\Data\Dispute\DisputeData;
use Maxiviper117\Paystack\Data\Dispute\DisputeStatus;
use Maxiviper117\Paystack\Data\Input\Customer\CreateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\CustomerRiskAction;
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
use Maxiviper117\Paystack\Data\Input\Transaction\FetchTransactionInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\ListTransactionsInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\TransactionStatus;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Data\Plan\PlanData;
use Maxiviper117\Paystack\Data\Refund\RefundData;
use Maxiviper117\Paystack\Data\Subscription\SubscriptionData;
use Maxiviper117\Paystack\Data\Transaction\TransactionData;
use Maxiviper117\Paystack\Exceptions\InvalidPaystackInputException;
use Maxiviper117\Paystack\Models\PaystackCustomer;
use Maxiviper117\Paystack\Models\PaystackDispute;
use Maxiviper117\Paystack\Models\PaystackPlan;
use Maxiviper117\Paystack\Models\PaystackRefund;
use Maxiviper117\Paystack\Models\PaystackSubscription;
use Maxiviper117\Paystack\Models\PaystackTransaction;
use Maxiviper117\Paystack\Models\PaystackWebhookCall;
use Maxiviper117\Paystack\PaystackManager;
use Throwable;

class PaystackDemoController extends Controller
{
    public function index(): View
    {
        return $this->render('index', [
            'title' => 'Paystack Workbench Demo',
            'heading' => 'Interactive demo pages for every Paystack feature in this package.',
            'description' => 'Use the feature pages to initialize and verify transactions, manage customers and plans, create subscriptions, inspect webhook intake, and exercise the optional billing mirror.',
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
        ListTransactionsAction $listTransactions,
    ): View {
        $callbackReference = $this->callbackTransactionReference($request);

        [$result, $resultLabel] = $this->capturePost($request, function () use ($request, $initializeTransaction, $verifyTransaction, $listTransactions): array {
            return match ((string) $request->input('action', 'initialize')) {
                'verify' => [
                    $verifyTransaction(new VerifyTransactionInputData((string) $request->input('reference', ''))),
                    'Transaction verification',
                ],
                'list' => [
                    $listTransactions(new ListTransactionsInputData(
                        perPage: $request->integer('per_page') ?: 10,
                        page: $request->integer('page') ?: 1,
                        customer: $request->filled('customer') ? (string) $request->input('customer') : null,
                        status: $this->transactionStatus($request->input('status')),
                        from: $request->filled('from') ? (string) $request->input('from') : null,
                        to: $request->filled('to') ? (string) $request->input('to') : null,
                        amount: $request->filled('amount_filter') ? $request->input('amount_filter') : null,
                        reference: $request->filled('list_reference') ? (string) $request->input('list_reference') : null,
                        terminalId: $request->filled('terminal_id') ? (string) $request->input('terminal_id') : null,
                    )),
                    'Transaction list',
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
            'description' => 'Initialize a checkout, verify a returned reference, or list transactions.',
            'result' => $result,
            'resultLabel' => $resultLabel,
            'callbackReference' => $callbackReference,
            'verificationNotice' => $verificationNotice,
            'transactionStatuses' => TransactionStatus::options(),
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
            'customerRiskActions' => CustomerRiskAction::options(),
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
            'disputeStatuses' => DisputeStatus::options(),
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

    public function billingLayer(Request $request, PaystackManager $paystack): View
    {
        [$result, $resultLabel] = $this->capturePost($request, function () use ($request, $paystack): array {
            $user = $this->resolveBillingUser($request);

            $result = match ((string) $request->input('action', 'sync')) {
                'create-subscription' => $paystack->createBillableSubscription(
                    billable: $user,
                    planCode: (string) $request->input('plan', ''),
                    name: (string) $request->input('subscription_name', 'default'),
                    authorization: $request->filled('authorization') ? (string) $request->input('authorization') : null,
                    startDate: $request->filled('start_date') ? (string) $request->input('start_date') : null,
                ),
                'enable' => $paystack->enableBillableSubscription($user, (string) $request->input('subscription_name', 'default')),
                'disable' => $paystack->disableBillableSubscription($user, (string) $request->input('subscription_name', 'default')),
                default => $paystack->syncBillableCustomer($user),
            };

            return [$this->billingLayerSnapshot($user, $result), 'Billing layer'];
        });

        return $this->render('billing-layer', [
            'title' => 'Billing Layer Demo',
            'heading' => 'Billing layer',
            'description' => 'Test the optional billing lifecycle and customer/subscription orchestration flow.',
            'result' => $result,
            'resultLabel' => $resultLabel,
            'currentPath' => '/paystack/demo/billing-layer',
        ]);
    }

    public function billingSync(
        Request $request,
        ListCustomersAction $listCustomers,
        ListPlansAction $listPlans,
        ListSubscriptionsAction $listSubscriptions,
        ListTransactionsAction $listTransactions,
        ListRefundsAction $listRefunds,
        ListDisputesAction $listDisputes,
        FetchCustomerAction $fetchCustomer,
        FetchPlanAction $fetchPlan,
        FetchSubscriptionAction $fetchSubscription,
        FetchTransactionAction $fetchTransaction,
        FetchRefundAction $fetchRefund,
        FetchDisputeAction $fetchDispute,
    ): View {
        $user = $this->resolveBillingUser($request);
        $resource = $this->billingSyncResource($request);
        $search = $this->billingSyncSearch(
            $request,
            $resource,
            $listCustomers,
            $listPlans,
            $fetchPlan,
            $listSubscriptions,
            $listTransactions,
            $listRefunds,
            $listDisputes,
        );

        [$result, $resultLabel] = $this->capturePost($request, function () use ($request, $user, $fetchCustomer, $fetchPlan, $fetchSubscription, $fetchTransaction, $fetchRefund, $fetchDispute): array {
            return $this->billingSyncExecute(
                $request,
                $user,
                $fetchCustomer,
                $fetchPlan,
                $fetchSubscription,
                $fetchTransaction,
                $fetchRefund,
                $fetchDispute,
            );
        });

        return $this->render('billing-sync', [
            'title' => 'Billing Sync Demo',
            'heading' => 'Billing sync',
            'description' => 'Search Paystack records first, then sync a chosen record into the local mirror tables.',
            'resource' => $resource,
            'resourceOptions' => $this->billingSyncResourceOptions(),
            'search' => $search,
            'result' => $result,
            'resultLabel' => $resultLabel,
            'snapshot' => $this->billingSyncSnapshot($user, $result),
            'currentPath' => '/paystack/demo/billing-sync',
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
            ['title' => 'Billing Layer', 'path' => '/paystack/demo/billing-layer', 'description' => 'Exercise the opt-in billing lifecycle and local mirror tables.'],
            ['title' => 'Billing Sync', 'path' => '/paystack/demo/billing-sync', 'description' => 'Sync mirrored billing records into local tables.'],
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

    private function resolveBillingUser(Request $request): User
    {
        return User::query()->firstOrCreate(
            ['email' => (string) $request->input('email', 'billable@example.com')],
            [
                'name' => (string) $request->input('name', 'Billable Demo User'),
                'password' => 'password',
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function billingLayerSnapshot(User $user, mixed $result): array
    {
        $user->load(['paystackCustomer', 'paystackSubscriptions']);

        return [
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
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function billingSyncSnapshot(User $user, mixed $result): array
    {
        $user->load(['paystackCustomer', 'paystackSubscriptions', 'paystackTransactions', 'paystackRefunds', 'paystackDisputes']);

        return [
            'user' => $user->only(['id', 'name', 'email']),
            'paystack_customer' => $user->paystackCustomer?->only(['id', 'customer_code', 'email']),
            'paystack_plans' => $this->latestPaystackPlans(),
            'paystack_subscriptions' => $this->serializePaystackSubscriptions($user),
            'paystack_transactions' => $this->serializePaystackTransactions($user),
            'paystack_refunds' => $this->serializePaystackRefunds($user),
            'paystack_disputes' => $this->serializePaystackDisputes($user),
            'result' => $result,
        ];
    }

    /**
     * @return array<int, array{value: string, label: string, description: string}>
     */
    private function billingSyncResourceOptions(): array
    {
        return [
            ['value' => 'customers', 'label' => 'Customers', 'description' => 'Search and sync customer records.'],
            ['value' => 'plans', 'label' => 'Plans', 'description' => 'Search and sync plan records.'],
            ['value' => 'subscriptions', 'label' => 'Subscriptions', 'description' => 'Search and sync subscription records.'],
            ['value' => 'transactions', 'label' => 'Transactions', 'description' => 'Search and sync transaction records.'],
            ['value' => 'refunds', 'label' => 'Refunds', 'description' => 'Search and sync refund records.'],
            ['value' => 'disputes', 'label' => 'Disputes', 'description' => 'Search and sync dispute records.'],
        ];
    }

    private function billingSyncResource(Request $request): string
    {
        $resource = (string) $request->input('resource', 'customers');

        return in_array($resource, ['customers', 'plans', 'subscriptions', 'transactions', 'refunds', 'disputes'], true)
            ? $resource
            : 'customers';
    }

    /**
     * @return array<string, mixed>
     */
    private function billingSyncSearch(
        Request $request,
        string $resource,
        ListCustomersAction $listCustomers,
        ListPlansAction $listPlans,
        FetchPlanAction $fetchPlan,
        ListSubscriptionsAction $listSubscriptions,
        ListTransactionsAction $listTransactions,
        ListRefundsAction $listRefunds,
        ListDisputesAction $listDisputes,
    ): array {
        return match ($resource) {
            'plans' => $this->billingSyncPlanSearch($request, $listPlans, $fetchPlan),
            'subscriptions' => $this->billingSyncSubscriptionSearch($request, $listSubscriptions),
            'transactions' => $this->billingSyncTransactionSearch($request, $listTransactions),
            'refunds' => $this->billingSyncRefundSearch($request, $listRefunds),
            'disputes' => $this->billingSyncDisputeSearch($request, $listDisputes),
            default => $this->billingSyncCustomerSearch($request, $listCustomers),
        };
    }

    /**
     * @return array{resource: string, title: string, description: string, fields: array<int, array<string, mixed>>, items: array<int, array<string, mixed>>, meta: array<string, mixed>|null, searched: bool}
     */
    private function billingSyncCustomerSearch(Request $request, ListCustomersAction $listCustomers): array
    {
        $fields = [
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'placeholder' => 'customer@example.com', 'value' => (string) $request->input('email', '')],
            ['name' => 'from', 'label' => 'From', 'type' => 'text', 'placeholder' => '2026-01-01T00:00:00Z', 'value' => (string) $request->input('from', '')],
            ['name' => 'to', 'label' => 'To', 'type' => 'text', 'placeholder' => '2026-12-31T23:59:59Z', 'value' => (string) $request->input('to', '')],
            ['name' => 'per_page', 'label' => 'Per page', 'type' => 'number', 'placeholder' => '10', 'value' => (string) ($request->integer('per_page') ?: 10)],
            ['name' => 'page', 'label' => 'Page', 'type' => 'number', 'placeholder' => '1', 'value' => (string) ($request->integer('page') ?: 1)],
        ];

        if (! $request->boolean('search')) {
            return [
                'resource' => 'customers',
                'title' => 'Customers',
                'description' => 'Search remote customers and sync the one you want locally.',
                'fields' => $fields,
                'items' => [],
                'meta' => null,
                'searched' => false,
            ];
        }

        $response = $listCustomers(new ListCustomersInputData(
            perPage: $request->integer('per_page') ?: 10,
            page: $request->integer('page') ?: 1,
            email: $request->filled('email') ? (string) $request->input('email') : null,
            from: $request->filled('from') ? (string) $request->input('from') : null,
            to: $request->filled('to') ? (string) $request->input('to') : null,
        ));

        return [
            'resource' => 'customers',
            'title' => 'Customers',
            'description' => 'Search remote customers and sync the one you want locally.',
            'fields' => $fields,
            'items' => array_map(fn (CustomerData $customer): array => [
                'label' => $customer->email,
                'identifier' => $customer->customerCode ?? $customer->email,
                'sync_action' => 'sync-customer',
                'summary' => [
                    'Code' => $customer->customerCode,
                    'First name' => $customer->firstName,
                    'Last name' => $customer->lastName,
                    'Phone' => $customer->phone,
                ],
                'payload' => $customer->raw,
            ], $response->customers),
            'meta' => $response->meta?->toArray(),
            'searched' => true,
        ];
    }

    /**
     * @return array{resource: string, title: string, description: string, fields: array<int, array<string, mixed>>, items: array<int, array<string, mixed>>, meta: array<string, mixed>|null, searched: bool}
     */
    private function billingSyncPlanSearch(Request $request, ListPlansAction $listPlans, FetchPlanAction $fetchPlan): array
    {
        $fields = [
            ['name' => 'plan_identifier', 'label' => 'Plan ID or code', 'type' => 'text', 'placeholder' => 'PLN_123 or 123', 'value' => (string) $request->input('plan_identifier', '')],
            ['name' => 'status', 'label' => 'Status', 'type' => 'text', 'placeholder' => 'active', 'value' => (string) $request->input('status', '')],
            ['name' => 'interval', 'label' => 'Interval', 'type' => 'text', 'placeholder' => 'monthly', 'value' => (string) $request->input('interval', '')],
            ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'placeholder' => '5000', 'value' => (string) ($request->input('amount') ?? '')],
            ['name' => 'per_page', 'label' => 'Per page', 'type' => 'number', 'placeholder' => '10', 'value' => (string) ($request->integer('per_page') ?: 10)],
            ['name' => 'page', 'label' => 'Page', 'type' => 'number', 'placeholder' => '1', 'value' => (string) ($request->integer('page') ?: 1)],
        ];

        if (! $request->boolean('search')) {
            return [
                'resource' => 'plans',
                'title' => 'Plans',
                'description' => 'Search remote plans and sync the one you want locally.',
                'fields' => $fields,
                'items' => [],
                'meta' => null,
                'searched' => false,
            ];
        }

        // If a specific plan identifier is provided, fetch that plan directly instead of listing. This allows for more precise syncing when the identifier is known, and also demonstrates both the fetch and list flows.
        if ($request->filled('plan_identifier')) {
            $response = $fetchPlan(new FetchPlanInputData((string) $request->input('plan_identifier')));

            return [
                'resource' => 'plans',
                'title' => 'Plans',
                'description' => 'Search remote plans and sync the one you want locally.',
                'fields' => $fields,
                'items' => [[
                    'label' => $response->plan->name ?? $response->plan->planCode,
                    'identifier' => $response->plan->planCode,
                    'sync_action' => 'sync-plan',
                    'summary' => [
                        'Plan code' => $response->plan->planCode,
                        'Amount' => $response->plan->amount,
                        'Interval' => $response->plan->interval,
                        'Currency' => $response->plan->currency,
                    ],
                    'payload' => $response->plan->raw,
                ]],
                'meta' => null,
                'searched' => true,
            ];
        }

        $response = $listPlans(new ListPlansInputData(
            perPage: $request->integer('per_page') ?: 10,
            page: $request->integer('page') ?: 1,
            status: $request->filled('status') ? (string) $request->input('status') : null,
            interval: $request->filled('interval') ? (string) $request->input('interval') : null,
            amount: $request->filled('amount') ? $request->input('amount') : null,
        ));

        return [
            'resource' => 'plans',
            'title' => 'Plans',
            'description' => 'Search remote plans and sync the one you want locally.',
            'fields' => $fields,
            'items' => array_map(fn (PlanData $plan): array => [
                'label' => $plan->name ?? $plan->planCode,
                'identifier' => $plan->planCode,
                'sync_action' => 'sync-plan',
                'summary' => [
                    'Plan code' => $plan->planCode,
                    'Amount' => $plan->amount,
                    'Interval' => $plan->interval,
                    'Currency' => $plan->currency,
                ],
                'payload' => $plan->raw,
            ], $response->plans),
            'meta' => $response->meta?->toArray(),
            'searched' => true,
        ];
    }

    /**
     * @return array{resource: string, title: string, description: string, fields: array<int, array<string, mixed>>, items: array<int, array<string, mixed>>, meta: array<string, mixed>|null, searched: bool}
     */
    private function billingSyncSubscriptionSearch(Request $request, ListSubscriptionsAction $listSubscriptions): array
    {
        $fields = [
            ['name' => 'customer', 'label' => 'Customer', 'type' => 'text', 'placeholder' => 'CUS_123', 'value' => (string) $request->input('customer', '')],
            ['name' => 'plan', 'label' => 'Plan', 'type' => 'text', 'placeholder' => 'PLN_123', 'value' => (string) $request->input('plan', '')],
            ['name' => 'per_page', 'label' => 'Per page', 'type' => 'number', 'placeholder' => '10', 'value' => (string) ($request->integer('per_page') ?: 10)],
            ['name' => 'page', 'label' => 'Page', 'type' => 'number', 'placeholder' => '1', 'value' => (string) ($request->integer('page') ?: 1)],
        ];

        if (! $request->boolean('search')) {
            return [
                'resource' => 'subscriptions',
                'title' => 'Subscriptions',
                'description' => 'Search remote subscriptions and sync the one you want locally.',
                'fields' => $fields,
                'items' => [],
                'meta' => null,
                'searched' => false,
            ];
        }

        $response = $listSubscriptions(new ListSubscriptionsInputData(
            perPage: $request->integer('per_page') ?: 10,
            page: $request->integer('page') ?: 1,
            customer: $request->filled('customer') ? (string) $request->input('customer') : null,
            plan: $request->filled('plan') ? (string) $request->input('plan') : null,
        ));

        return [
            'resource' => 'subscriptions',
            'title' => 'Subscriptions',
            'description' => 'Search remote subscriptions and sync the one you want locally.',
            'fields' => $fields,
            'items' => array_map(fn (SubscriptionData $subscription): array => [
                'label' => $subscription->subscriptionCode,
                'identifier' => $subscription->subscriptionCode,
                'sync_action' => 'sync-subscription',
                'summary' => [
                    'Status' => $subscription->status?->value,
                    'Plan' => $subscription->plan?->planCode ?? data_get($subscription->raw, 'plan.plan_code'),
                    'Customer' => $subscription->customer?->customerCode ?? data_get($subscription->raw, 'customer.customer_code'),
                    'Next payment' => $subscription->nextPaymentDate?->toAtomString(),
                ],
                'payload' => $subscription->raw,
            ], $response->subscriptions),
            'meta' => $response->meta?->toArray(),
            'searched' => true,
        ];
    }

    /**
     * @return array{resource: string, title: string, description: string, fields: array<int, array<string, mixed>>, items: array<int, array<string, mixed>>, meta: array<string, mixed>|null, searched: bool}
     */
    private function billingSyncTransactionSearch(Request $request, ListTransactionsAction $listTransactions): array
    {
        $fields = [
            ['name' => 'customer', 'label' => 'Customer', 'type' => 'text', 'placeholder' => 'CUS_123', 'value' => (string) $request->input('customer', '')],
            ['name' => 'status', 'label' => 'Status', 'type' => 'text', 'placeholder' => 'success', 'value' => (string) $request->input('status', '')],
            ['name' => 'from', 'label' => 'From', 'type' => 'text', 'placeholder' => '2026-01-01T00:00:00Z', 'value' => (string) $request->input('from', '')],
            ['name' => 'to', 'label' => 'To', 'type' => 'text', 'placeholder' => '2026-12-31T23:59:59Z', 'value' => (string) $request->input('to', '')],
            ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'placeholder' => '7500', 'value' => (string) ($request->input('amount') ?? '')],
            ['name' => 'reference', 'label' => 'Reference', 'type' => 'text', 'placeholder' => 'TRX_123', 'value' => (string) $request->input('reference', '')],
            ['name' => 'terminal_id', 'label' => 'Terminal ID', 'type' => 'text', 'placeholder' => 'TERM_123', 'value' => (string) $request->input('terminal_id', '')],
            ['name' => 'per_page', 'label' => 'Per page', 'type' => 'number', 'placeholder' => '10', 'value' => (string) ($request->integer('per_page') ?: 10)],
            ['name' => 'page', 'label' => 'Page', 'type' => 'number', 'placeholder' => '1', 'value' => (string) ($request->integer('page') ?: 1)],
        ];

        if (! $request->boolean('search')) {
            return [
                'resource' => 'transactions',
                'title' => 'Transactions',
                'description' => 'Search remote transactions and sync the one you want locally.',
                'fields' => $fields,
                'items' => [],
                'meta' => null,
                'searched' => false,
            ];
        }

        $response = $listTransactions(new ListTransactionsInputData(
            perPage: $request->integer('per_page') ?: 10,
            page: $request->integer('page') ?: 1,
            customer: $request->filled('customer') ? (string) $request->input('customer') : null,
            status: $request->filled('status') ? $this->transactionStatus($request->input('status')) : null,
            from: $request->filled('from') ? (string) $request->input('from') : null,
            to: $request->filled('to') ? (string) $request->input('to') : null,
            amount: $request->filled('amount') ? $request->input('amount') : null,
            reference: $request->filled('reference') ? (string) $request->input('reference') : null,
            terminalId: $request->filled('terminal_id') ? (string) $request->input('terminal_id') : null,
        ));

        return [
            'resource' => 'transactions',
            'title' => 'Transactions',
            'description' => 'Search remote transactions and sync the one you want locally.',
            'fields' => $fields,
            'items' => array_map(fn (TransactionData $transaction): array => [
                'label' => $transaction->reference,
                'identifier' => (string) $transaction->id,
                'sync_action' => 'sync-transaction',
                'summary' => [
                    'ID' => $transaction->id,
                    'Status' => $transaction->status?->value,
                    'Amount' => $transaction->amount,
                    'Currency' => $transaction->currency,
                    'Channel' => $transaction->channel,
                    'Paid at' => $transaction->paidAt?->toAtomString(),
                ],
                'payload' => $transaction->raw,
            ], $response->transactions),
            'meta' => $response->meta?->toArray(),
            'searched' => true,
        ];
    }

    /**
     * @return array{resource: string, title: string, description: string, fields: array<int, array<string, mixed>>, items: array<int, array<string, mixed>>, meta: array<string, mixed>|null, searched: bool}
     */
    private function billingSyncRefundSearch(Request $request, ListRefundsAction $listRefunds): array
    {
        $fields = [
            ['name' => 'transaction', 'label' => 'Transaction', 'type' => 'text', 'placeholder' => 'TRX_123', 'value' => (string) $request->input('transaction', '')],
            ['name' => 'currency', 'label' => 'Currency', 'type' => 'text', 'placeholder' => 'NGN', 'value' => (string) $request->input('currency', '')],
            ['name' => 'from', 'label' => 'From', 'type' => 'text', 'placeholder' => '2026-01-01T00:00:00Z', 'value' => (string) $request->input('from', '')],
            ['name' => 'to', 'label' => 'To', 'type' => 'text', 'placeholder' => '2026-12-31T23:59:59Z', 'value' => (string) $request->input('to', '')],
            ['name' => 'per_page', 'label' => 'Per page', 'type' => 'number', 'placeholder' => '10', 'value' => (string) ($request->integer('per_page') ?: 10)],
            ['name' => 'page', 'label' => 'Page', 'type' => 'number', 'placeholder' => '1', 'value' => (string) ($request->integer('page') ?: 1)],
        ];

        if (! $request->boolean('search')) {
            return [
                'resource' => 'refunds',
                'title' => 'Refunds',
                'description' => 'Search remote refunds and sync the one you want locally.',
                'fields' => $fields,
                'items' => [],
                'meta' => null,
                'searched' => false,
            ];
        }

        $response = $listRefunds(new ListRefundsInputData(
            transaction: $request->filled('transaction') ? $request->input('transaction') : null,
            currency: $request->filled('currency') ? (string) $request->input('currency') : null,
            from: $request->filled('from') ? (string) $request->input('from') : null,
            to: $request->filled('to') ? (string) $request->input('to') : null,
            perPage: $request->integer('per_page') ?: 10,
            page: $request->integer('page') ?: 1,
        ));

        return [
            'resource' => 'refunds',
            'title' => 'Refunds',
            'description' => 'Search remote refunds and sync the one you want locally.',
            'fields' => $fields,
            'items' => array_map(fn (RefundData $refund): array => [
                'label' => $refund->bankReference ?? (string) $refund->id,
                'identifier' => (string) ($refund->id ?? $refund->bankReference ?? $refund->transaction),
                'sync_action' => 'sync-refund',
                'summary' => [
                    'ID' => $refund->id,
                    'Transaction' => is_int($refund->transaction) || is_string($refund->transaction)
                        ? $refund->transaction
                        : $refund->transaction?->reference,
                    'Status' => $refund->status?->value,
                    'Amount' => $refund->amount,
                    'Currency' => $refund->currency,
                ],
                'payload' => $refund->raw,
            ], $response->refunds),
            'meta' => $response->meta?->toArray(),
            'searched' => true,
        ];
    }

    /**
     * @return array{resource: string, title: string, description: string, fields: array<int, array<string, mixed>>, items: array<int, array<string, mixed>>, meta: array<string, mixed>|null, searched: bool}
     */
    private function billingSyncDisputeSearch(Request $request, ListDisputesAction $listDisputes): array
    {
        $fields = [
            ['name' => 'transaction', 'label' => 'Transaction', 'type' => 'text', 'placeholder' => 'TRX_123', 'value' => (string) $request->input('transaction', '')],
            ['name' => 'status', 'label' => 'Status', 'type' => 'text', 'placeholder' => 'pending', 'value' => (string) $request->input('status', '')],
            ['name' => 'from', 'label' => 'From', 'type' => 'text', 'placeholder' => '2026-01-01T00:00:00Z', 'value' => (string) $request->input('from', '')],
            ['name' => 'to', 'label' => 'To', 'type' => 'text', 'placeholder' => '2026-12-31T23:59:59Z', 'value' => (string) $request->input('to', '')],
            ['name' => 'per_page', 'label' => 'Per page', 'type' => 'number', 'placeholder' => '10', 'value' => (string) ($request->integer('per_page') ?: 10)],
            ['name' => 'page', 'label' => 'Page', 'type' => 'number', 'placeholder' => '1', 'value' => (string) ($request->integer('page') ?: 1)],
        ];

        if (! $request->boolean('search')) {
            return [
                'resource' => 'disputes',
                'title' => 'Disputes',
                'description' => 'Search remote disputes and sync the one you want locally.',
                'fields' => $fields,
                'items' => [],
                'meta' => null,
                'searched' => false,
            ];
        }

        $response = $listDisputes(new ListDisputesInputData(
            from: $request->filled('from') ? (string) $request->input('from') : null,
            to: $request->filled('to') ? (string) $request->input('to') : null,
            perPage: $request->integer('per_page') ?: 10,
            page: $request->integer('page') ?: 1,
            transaction: $request->filled('transaction') ? (string) $request->input('transaction') : null,
            status: $request->filled('status') ? (string) $request->input('status') : null,
        ));

        return [
            'resource' => 'disputes',
            'title' => 'Disputes',
            'description' => 'Search remote disputes and sync the one you want locally.',
            'fields' => $fields,
            'items' => array_map(fn (DisputeData $dispute): array => [
                'label' => (string) ($dispute->id ?? $dispute->transactionReference),
                'identifier' => (string) ($dispute->id ?? $dispute->transactionReference),
                'sync_action' => 'sync-dispute',
                'summary' => [
                    'ID' => $dispute->id,
                    'Transaction' => $dispute->transactionReference,
                    'Status' => $dispute->status?->value,
                    'Refund amount' => $dispute->refundAmount,
                    'Resolution' => $dispute->resolution,
                ],
                'payload' => $dispute->raw,
            ], $response->disputes),
            'meta' => $response->meta?->toArray(),
            'searched' => true,
        ];
    }

    /**
     * @return array{0: array<string, mixed>|object|null, 1: string|null}
     */
    private function billingSyncExecute(
        Request $request,
        User $user,
        FetchCustomerAction $fetchCustomer,
        FetchPlanAction $fetchPlan,
        FetchSubscriptionAction $fetchSubscription,
        FetchTransactionAction $fetchTransaction,
        FetchRefundAction $fetchRefund,
        FetchDisputeAction $fetchDispute,
    ): array {
        return match ((string) $request->input('action', '')) {
            'sync-customer' => [
                $this->billingSyncCustomerFromPaystack(
                    $user,
                    $fetchCustomer,
                    (string) $request->input('identifier', ''),
                ),
                'Customer sync',
            ],
            'sync-plan' => [
                $this->billingSyncPlanFromPaystack(
                    $fetchPlan,
                    $user,
                    (string) $request->input('identifier', ''),
                ),
                'Plan sync',
            ],
            'sync-subscription' => [
                $this->billingSyncSubscriptionFromPaystack(
                    $user,
                    $fetchSubscription,
                    (string) $request->input('identifier', ''),
                    (string) $request->input('subscription_name', 'default'),
                ),
                'Subscription sync',
            ],
            'sync-transaction' => [
                $this->billingSyncTransactionFromPaystack(
                    $user,
                    $fetchTransaction,
                    (int) $request->input('identifier', 0),
                ),
                'Transaction sync',
            ],
            'sync-refund' => [
                $this->billingSyncRefundFromPaystack(
                    $user,
                    $fetchRefund,
                    $request->input('identifier', ''),
                ),
                'Refund sync',
            ],
            'sync-dispute' => [
                $this->billingSyncDisputeFromPaystack(
                    $user,
                    $fetchDispute,
                    $request->input('identifier', ''),
                ),
                'Dispute sync',
            ],
            default => [
                ['error' => 'Unknown billing sync action.'],
                'Error',
            ],
        };
    }

    private function billingSyncCustomerFromPaystack(User $user, FetchCustomerAction $fetchCustomer, string $identifier): array
    {
        $response = $fetchCustomer(new FetchCustomerInputData($identifier));
        $record = PaystackCustomer::syncFromCustomerData($response->customer, $user);

        return [
            'resource' => 'customer',
            'identifier' => $identifier,
            'remote' => $this->customerSummary($response->customer),
            'local' => $record->only(['id', 'customer_code', 'email', 'first_name', 'last_name', 'phone']),
        ];
    }

    private function billingSyncPlanFromPaystack(FetchPlanAction $fetchPlan, ?User $user, string $identifier): array
    {
        $response = $fetchPlan(new FetchPlanInputData($identifier));
        $record = PaystackPlan::syncFromPlanData($response->plan);

        if ($user !== null) {
            $record->billable()->associate($user);
            $record->save();
        }

        return [
            'resource' => 'plan',
            'identifier' => $identifier,
            'remote' => $this->planSummary($response->plan),
            'local' => $record->only(['id', 'plan_code', 'name', 'amount', 'interval', 'currency']),
        ];
    }

    private function billingSyncSubscriptionFromPaystack(User $user, FetchSubscriptionAction $fetchSubscription, string $identifier, string $name): array
    {
        $response = $fetchSubscription(new FetchSubscriptionInputData($identifier));
        $record = PaystackSubscription::syncFromSubscriptionData($response->subscription, $name, $user);

        return [
            'resource' => 'subscription',
            'identifier' => $identifier,
            'remote' => $this->subscriptionSummary($response->subscription),
            'local' => $record->only(['id', 'name', 'subscription_code', 'status', 'plan_code', 'email_token', 'next_payment_date']),
        ];
    }

    private function billingSyncTransactionFromPaystack(User $user, FetchTransactionAction $fetchTransaction, int $identifier): array
    {
        $response = $fetchTransaction(new FetchTransactionInputData($identifier));
        $record = PaystackTransaction::syncFromTransactionData($response->transaction);
        $record->billable()->associate($user);
        $record->save();

        return [
            'resource' => 'transaction',
            'identifier' => $identifier,
            'remote' => $this->transactionSummary($response->transaction),
            'local' => $record->only(['id', 'reference', 'status', 'amount', 'currency', 'channel', 'paid_at']),
        ];
    }

    private function billingSyncRefundFromPaystack(User $user, FetchRefundAction $fetchRefund, int|string $identifier): array
    {
        $response = $fetchRefund(new FetchRefundInputData($identifier));
        $record = PaystackRefund::syncFromRefundData($response->refund);
        $record->billable()->associate($user);
        $record->save();

        return [
            'resource' => 'refund',
            'identifier' => $identifier,
            'remote' => $this->refundSummary($response->refund),
            'local' => $record->only(['id', 'refund_reference', 'transaction_reference', 'status', 'amount', 'currency']),
        ];
    }

    private function billingSyncDisputeFromPaystack(User $user, FetchDisputeAction $fetchDispute, int|string $identifier): array
    {
        $response = $fetchDispute(new FetchDisputeInputData($identifier));
        $record = PaystackDispute::syncFromDisputeData($response->dispute);
        $record->billable()->associate($user);
        $record->save();

        return [
            'resource' => 'dispute',
            'identifier' => $identifier,
            'remote' => $this->disputeSummary($response->dispute),
            'local' => $record->only(['id', 'paystack_id', 'transaction_reference', 'status', 'refund_amount', 'currency']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function customerSummary(CustomerData $customer): array
    {
        return [
            'email' => $customer->email,
            'customer_code' => $customer->customerCode,
            'first_name' => $customer->firstName,
            'last_name' => $customer->lastName,
            'phone' => $customer->phone,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function planSummary(PlanData $plan): array
    {
        return [
            'id' => $plan->id,
            'plan_code' => $plan->planCode,
            'name' => $plan->name,
            'amount' => $plan->amount,
            'interval' => $plan->interval,
            'currency' => $plan->currency,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function subscriptionSummary(SubscriptionData $subscription): array
    {
        return [
            'id' => $subscription->id,
            'subscription_code' => $subscription->subscriptionCode,
            'status' => $subscription->status?->value,
            'email_token' => $subscription->emailToken,
            'plan_code' => $subscription->plan?->planCode,
            'customer_code' => $subscription->customer?->customerCode,
            'next_payment_date' => $subscription->nextPaymentDate?->toAtomString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transactionSummary(TransactionData $transaction): array
    {
        return [
            'id' => $transaction->id,
            'reference' => $transaction->reference,
            'status' => $transaction->status?->value,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'channel' => $transaction->channel,
            'paid_at' => $transaction->paidAt?->toAtomString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function refundSummary(RefundData $refund): array
    {
        return [
            'id' => $refund->id,
            'bank_reference' => $refund->bankReference,
            'transaction_reference' => is_int($refund->transaction) || is_string($refund->transaction)
                ? $refund->transaction
                : $refund->transaction?->reference,
            'status' => $refund->status?->value,
            'amount' => $refund->amount,
            'currency' => $refund->currency,
            'refunded_at' => $refund->refundedAt?->toAtomString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function disputeSummary(DisputeData $dispute): array
    {
        return [
            'id' => $dispute->id,
            'transaction_reference' => $dispute->transactionReference,
            'status' => $dispute->status?->value,
            'refund_amount' => $dispute->refundAmount,
            'resolution' => $dispute->resolution,
            'due_at' => $dispute->dueAt?->toAtomString(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function latestPaystackPlans(): array
    {
        return PaystackPlan::query()
            ->latest()
            ->take(5)
            ->get()
            ->map(static fn (mixed $plan): array => $plan->only([
                'id',
                'plan_code',
                'name',
                'amount',
                'interval',
                'currency',
            ]))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function serializePaystackSubscriptions(User $user): array
    {
        return $user->paystackSubscriptions
            ->map(static fn (mixed $subscription): array => $subscription->only([
                'id',
                'name',
                'subscription_code',
                'status',
                'plan_code',
                'email_token',
            ]))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function serializePaystackTransactions(User $user): array
    {
        return $user->paystackTransactions
            ->map(static fn (mixed $transaction): array => $transaction->only([
                'id',
                'reference',
                'status',
                'amount',
                'currency',
                'channel',
                'paid_at',
            ]))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function serializePaystackRefunds(User $user): array
    {
        return $user->paystackRefunds
            ->map(static fn (mixed $refund): array => $refund->only([
                'id',
                'refund_reference',
                'transaction_reference',
                'status',
                'amount',
                'currency',
            ]))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function serializePaystackDisputes(User $user): array
    {
        return $user->paystackDisputes
            ->map(static fn (mixed $dispute): array => $dispute->only([
                'id',
                'dispute_id',
                'transaction_reference',
                'status',
                'refund_amount',
            ]))
            ->values()
            ->all();
    }

    private function billingSyncPlanData(Request $request): PlanData
    {
        return PlanData::fromPayload([
            'id' => $request->filled('plan_id') ? $request->input('plan_id') : null,
            'plan_code' => (string) $request->input('plan_code', 'PLN_sync'),
            'name' => (string) $request->input('plan_name', 'Sync Plan'),
            'amount' => $request->integer('plan_amount') ?: 5000,
            'interval' => (string) $request->input('plan_interval', 'monthly'),
            'description' => $request->filled('plan_description') ? (string) $request->input('plan_description') : 'Workbench sync trigger',
            'currency' => $request->filled('plan_currency') ? (string) $request->input('plan_currency') : 'NGN',
            'invoice_limit' => $request->filled('plan_invoice_limit') ? $request->integer('plan_invoice_limit') : null,
            'send_invoices' => $request->filled('plan_send_invoices') ? $request->boolean('plan_send_invoices') : null,
            'send_sms' => $request->filled('plan_send_sms') ? $request->boolean('plan_send_sms') : null,
        ]);
    }

    private function billingSyncSubscriptionData(Request $request): SubscriptionData
    {
        return SubscriptionData::fromPayload([
            'id' => $request->filled('subscription_id') ? $request->input('subscription_id') : null,
            'subscription_code' => (string) $request->input('subscription_code', 'SUB_sync'),
            'status' => (string) $request->input('subscription_status', 'active'),
            'email_token' => $request->filled('subscription_email_token') ? (string) $request->input('subscription_email_token') : 'token_sync',
            'next_payment_date' => (string) $request->input('subscription_next_payment_date', now()->addMonth()->toAtomString()),
            'open_invoice' => $request->filled('subscription_open_invoice') ? $request->input('subscription_open_invoice') : null,
            'plan' => [
                'plan_code' => (string) $request->input('plan_code', 'PLN_sync'),
                'name' => (string) $request->input('plan_name', 'Sync Plan'),
                'amount' => $request->integer('plan_amount') ?: 5000,
                'interval' => (string) $request->input('plan_interval', 'monthly'),
                'description' => $request->filled('plan_description') ? (string) $request->input('plan_description') : 'Workbench sync trigger',
                'currency' => $request->filled('plan_currency') ? (string) $request->input('plan_currency') : 'NGN',
            ],
            'customer' => [
                'email' => (string) $request->input('email', 'billable@example.com'),
                'customer_code' => $request->filled('customer_code') ? (string) $request->input('customer_code') : 'CUS_sync',
                'first_name' => $request->filled('first_name') ? (string) $request->input('first_name') : 'Billable',
                'last_name' => $request->filled('last_name') ? (string) $request->input('last_name') : 'User',
                'phone' => $request->filled('phone') ? (string) $request->input('phone') : '+27110000000',
            ],
        ]);
    }

    private function billingSyncTransactionData(Request $request): TransactionData
    {
        return TransactionData::fromPayload([
            'id' => $request->filled('transaction_id') ? $request->input('transaction_id') : null,
            'reference' => (string) $request->input('transaction_reference', 'TRX_sync'),
            'status' => (string) $request->input('transaction_status', 'success'),
            'amount' => $request->integer('transaction_amount') ?: 7500,
            'currency' => (string) $request->input('transaction_currency', 'NGN'),
            'channel' => (string) $request->input('transaction_channel', 'card'),
            'paid_at' => (string) $request->input('transaction_paid_at', now()->toAtomString()),
            'customer' => [
                'email' => (string) $request->input('email', 'billable@example.com'),
                'customer_code' => $request->filled('customer_code') ? (string) $request->input('customer_code') : 'CUS_sync',
                'first_name' => $request->filled('first_name') ? (string) $request->input('first_name') : 'Billable',
                'last_name' => $request->filled('last_name') ? (string) $request->input('last_name') : 'User',
                'phone' => $request->filled('phone') ? (string) $request->input('phone') : '+27110000000',
            ],
        ]);
    }

    private function billingSyncRefundData(Request $request): RefundData
    {
        return RefundData::fromPayload([
            'id' => $request->filled('refund_id') ? $request->input('refund_id') : null,
            'transaction' => $request->filled('refund_transaction_reference')
                ? (string) $request->input('refund_transaction_reference')
                : (string) $request->input('transaction_reference', 'TRX_sync'),
            'integration' => $request->filled('refund_integration') ? $request->input('refund_integration') : null,
            'dispute' => $request->filled('refund_dispute') ? $request->input('refund_dispute') : null,
            'settlement' => $request->filled('refund_settlement') ? $request->input('refund_settlement') : null,
            'domain' => (string) $request->input('refund_domain', 'test'),
            'amount' => $request->integer('refund_amount') ?: 2500,
            'deducted_amount' => $request->filled('refund_deducted_amount') ? $request->integer('refund_deducted_amount') : 2500,
            'currency' => (string) $request->input('refund_currency', 'NGN'),
            'channel' => (string) $request->input('refund_channel', 'card'),
            'fully_deducted' => $request->filled('refund_fully_deducted') ? $request->boolean('refund_fully_deducted') : true,
            'refunded_by' => $request->filled('refund_refunded_by') ? (string) $request->input('refund_refunded_by') : 'merchant',
            'refunded_at' => (string) $request->input('refund_refunded_at', now()->toAtomString()),
            'expected_at' => (string) $request->input('refund_expected_at', now()->addDay()->toAtomString()),
            'customer_note' => $request->filled('refund_customer_note') ? (string) $request->input('refund_customer_note') : 'Workbench refund sync',
            'merchant_note' => $request->filled('refund_merchant_note') ? (string) $request->input('refund_merchant_note') : 'Workbench refund sync',
            'status' => (string) $request->input('refund_status', 'pending'),
            'created_at' => (string) $request->input('refund_created_at', now()->toAtomString()),
            'updated_at' => (string) $request->input('refund_updated_at', now()->toAtomString()),
            'bank_reference' => $request->filled('refund_bank_reference') ? (string) $request->input('refund_bank_reference') : 'BANK_sync',
            'reason' => $request->filled('refund_reason') ? (string) $request->input('refund_reason') : 'Test refund',
            'customer' => [
                'email' => (string) $request->input('email', 'billable@example.com'),
                'customer_code' => $request->filled('customer_code') ? (string) $request->input('customer_code') : 'CUS_sync',
                'first_name' => $request->filled('first_name') ? (string) $request->input('first_name') : 'Billable',
                'last_name' => $request->filled('last_name') ? (string) $request->input('last_name') : 'User',
                'phone' => $request->filled('phone') ? (string) $request->input('phone') : '+27110000000',
            ],
            'initiated_by' => $request->filled('refund_initiated_by') ? (string) $request->input('refund_initiated_by') : 'merchant',
            'reversed_at' => $request->filled('refund_reversed_at') ? (string) $request->input('refund_reversed_at') : null,
            'session_id' => $request->filled('refund_session_id') ? (string) $request->input('refund_session_id') : null,
        ]);
    }

    private function billingSyncDisputeData(Request $request): DisputeData
    {
        return DisputeData::fromPayload([
            'id' => $request->filled('dispute_id') ? $request->input('dispute_id') : null,
            'refund_amount' => $request->integer('dispute_refund_amount') ?: 5000,
            'currency' => (string) $request->input('dispute_currency', 'NGN'),
            'status' => (string) $request->input('dispute_status', 'pending'),
            'resolution' => $request->filled('dispute_resolution') ? (string) $request->input('dispute_resolution') : 'merchant-accepted',
            'domain' => (string) $request->input('dispute_domain', 'test'),
            'category' => $request->filled('dispute_category') ? (string) $request->input('dispute_category') : 'chargeback',
            'note' => $request->filled('dispute_note') ? (string) $request->input('dispute_note') : 'Workbench dispute sync',
            'attachments' => $request->filled('dispute_attachments') ? (string) $request->input('dispute_attachments') : null,
            'last4' => $request->filled('dispute_last4') ? (string) $request->input('dispute_last4') : '4242',
            'bin' => $request->filled('dispute_bin') ? (string) $request->input('dispute_bin') : '408408',
            'transaction_reference' => (string) $request->input('transaction_reference', 'TRX_sync'),
            'merchant_transaction_reference' => $request->filled('dispute_merchant_transaction_reference') ? (string) $request->input('dispute_merchant_transaction_reference') : 'MERCHANT_sync',
            'source' => $request->filled('dispute_source') ? (string) $request->input('dispute_source') : 'merchant',
            'created_by' => $request->filled('dispute_created_by') ? $request->input('dispute_created_by') : null,
            'organization' => $request->filled('dispute_organization') ? $request->input('dispute_organization') : null,
            'integration' => $request->filled('dispute_integration') ? $request->input('dispute_integration') : null,
            'evidence' => null,
            'resolved_at' => $request->filled('dispute_resolved_at') ? (string) $request->input('dispute_resolved_at') : null,
            'due_at' => $request->filled('dispute_due_at') ? (string) $request->input('dispute_due_at') : null,
            'created_at' => (string) $request->input('dispute_created_at', now()->toAtomString()),
            'updated_at' => (string) $request->input('dispute_updated_at', now()->toAtomString()),
            'transaction' => [
                'reference' => (string) $request->input('transaction_reference', 'TRX_sync'),
                'amount' => $request->integer('transaction_amount') ?: 7500,
                'status' => (string) $request->input('transaction_status', 'success'),
                'currency' => (string) $request->input('transaction_currency', 'NGN'),
                'channel' => (string) $request->input('transaction_channel', 'card'),
                'customer' => [
                    'email' => (string) $request->input('email', 'billable@example.com'),
                    'customer_code' => $request->filled('customer_code') ? (string) $request->input('customer_code') : 'CUS_sync',
                    'first_name' => $request->filled('first_name') ? (string) $request->input('first_name') : 'Billable',
                    'last_name' => $request->filled('last_name') ? (string) $request->input('last_name') : 'User',
                    'phone' => $request->filled('phone') ? (string) $request->input('phone') : '+27110000000',
                ],
            ],
            'customer' => [
                'email' => (string) $request->input('email', 'billable@example.com'),
                'customer_code' => $request->filled('customer_code') ? (string) $request->input('customer_code') : 'CUS_sync',
                'first_name' => $request->filled('first_name') ? (string) $request->input('first_name') : 'Billable',
                'last_name' => $request->filled('last_name') ? (string) $request->input('last_name') : 'User',
                'phone' => $request->filled('phone') ? (string) $request->input('phone') : '+27110000000',
            ],
            'history' => [],
            'messages' => [],
        ]);
    }

    private function transactionStatus(mixed $value): ?TransactionStatus
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return TransactionStatus::tryFrom($value)
            ?? throw new InvalidPaystackInputException('The Paystack transaction status filter is invalid.');
    }

    private function enumValue(mixed $value): string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        return is_string($value) ? $value : (string) $value;
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

        if (\is_string($error) && trim($error) !== '') {
            return [
                'title' => 'Verification failed',
                'message' => $error,
                'tone' => 'danger',
            ];
        }

        $status = strtolower($this->enumValue(data_get($result, 'transaction.status', '')));
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
