<?php

namespace Maxiviper117\Paystack\Facades;

use Illuminate\Support\Facades\Facade;
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
use Maxiviper117\Paystack\Data\Input\Transaction\FetchTransactionInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\ListTransactionsInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Data\Input\Webhook\VerifyWebhookSignatureInputData;
use Maxiviper117\Paystack\Data\Output\Customer\CreateCustomerResponseData;
use Maxiviper117\Paystack\Data\Output\Customer\ListCustomersResponseData;
use Maxiviper117\Paystack\Data\Output\Customer\UpdateCustomerResponseData;
use Maxiviper117\Paystack\Data\Output\Plan\CreatePlanResponseData;
use Maxiviper117\Paystack\Data\Output\Plan\FetchPlanResponseData;
use Maxiviper117\Paystack\Data\Output\Plan\ListPlansResponseData;
use Maxiviper117\Paystack\Data\Output\Plan\UpdatePlanResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\CreateSubscriptionResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\DisableSubscriptionResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\EnableSubscriptionResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\FetchSubscriptionResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\ListSubscriptionsResponseData;
use Maxiviper117\Paystack\Data\Output\Transaction\FetchTransactionResponseData;
use Maxiviper117\Paystack\Data\Output\Transaction\InitializeTransactionResponseData;
use Maxiviper117\Paystack\Data\Output\Transaction\ListTransactionsResponseData;
use Maxiviper117\Paystack\Data\Output\Transaction\VerifyTransactionResponseData;
use Maxiviper117\Paystack\Data\Output\Webhook\VerifyWebhookSignatureResponseData;
use Maxiviper117\Paystack\PaystackManager;

/**
 * @see PaystackManager
 *
 * @method static InitializeTransactionResponseData initializeTransaction(InitializeTransactionInputData $input)
 * @method static VerifyTransactionResponseData verifyTransaction(VerifyTransactionInputData $input)
 * @method static FetchTransactionResponseData fetchTransaction(FetchTransactionInputData $input)
 * @method static ListTransactionsResponseData listTransactions(ListTransactionsInputData $input)
 * @method static CreateCustomerResponseData createCustomer(CreateCustomerInputData $input)
 * @method static UpdateCustomerResponseData updateCustomer(UpdateCustomerInputData $input)
 * @method static ListCustomersResponseData listCustomers(ListCustomersInputData $input)
 * @method static CreatePlanResponseData createPlan(CreatePlanInputData $input)
 * @method static UpdatePlanResponseData updatePlan(UpdatePlanInputData $input)
 * @method static FetchPlanResponseData fetchPlan(FetchPlanInputData $input)
 * @method static ListPlansResponseData listPlans(ListPlansInputData $input)
 * @method static CreateSubscriptionResponseData createSubscription(CreateSubscriptionInputData $input)
 * @method static FetchSubscriptionResponseData fetchSubscription(FetchSubscriptionInputData $input)
 * @method static ListSubscriptionsResponseData listSubscriptions(ListSubscriptionsInputData $input)
 * @method static EnableSubscriptionResponseData enableSubscription(EnableSubscriptionInputData $input)
 * @method static DisableSubscriptionResponseData disableSubscription(DisableSubscriptionInputData $input)
 * @method static VerifyWebhookSignatureResponseData verifyWebhookSignature(VerifyWebhookSignatureInputData $input)
 */
class Paystack extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'paystack';
    }
}
