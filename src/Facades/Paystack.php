<?php

namespace Maxiviper117\Paystack\Facades;

use Illuminate\Support\Facades\Facade;
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
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Data\Output\Customer\CreateCustomerResponseData;
use Maxiviper117\Paystack\Data\Output\Customer\FetchCustomerResponseData;
use Maxiviper117\Paystack\Data\Output\Customer\ListCustomersResponseData;
use Maxiviper117\Paystack\Data\Output\Customer\SetCustomerRiskActionResponseData;
use Maxiviper117\Paystack\Data\Output\Customer\UpdateCustomerResponseData;
use Maxiviper117\Paystack\Data\Output\Customer\ValidateCustomerResponseData;
use Maxiviper117\Paystack\Data\Output\Dispute\AddDisputeEvidenceResponseData;
use Maxiviper117\Paystack\Data\Output\Dispute\ExportDisputesResponseData;
use Maxiviper117\Paystack\Data\Output\Dispute\FetchDisputeResponseData;
use Maxiviper117\Paystack\Data\Output\Dispute\GetDisputeUploadUrlResponseData;
use Maxiviper117\Paystack\Data\Output\Dispute\ListDisputesResponseData;
use Maxiviper117\Paystack\Data\Output\Dispute\ListTransactionDisputesResponseData;
use Maxiviper117\Paystack\Data\Output\Dispute\ResolveDisputeResponseData;
use Maxiviper117\Paystack\Data\Output\Dispute\UpdateDisputeResponseData;
use Maxiviper117\Paystack\Data\Output\Plan\CreatePlanResponseData;
use Maxiviper117\Paystack\Data\Output\Plan\FetchPlanResponseData;
use Maxiviper117\Paystack\Data\Output\Plan\ListPlansResponseData;
use Maxiviper117\Paystack\Data\Output\Plan\UpdatePlanResponseData;
use Maxiviper117\Paystack\Data\Output\Refund\CreateRefundResponseData;
use Maxiviper117\Paystack\Data\Output\Refund\FetchRefundResponseData;
use Maxiviper117\Paystack\Data\Output\Refund\ListRefundsResponseData;
use Maxiviper117\Paystack\Data\Output\Refund\RetryRefundResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\CreateSubscriptionResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\DisableSubscriptionResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\EnableSubscriptionResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\FetchSubscriptionResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\GenerateSubscriptionUpdateLinkResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\ListSubscriptionsResponseData;
use Maxiviper117\Paystack\Data\Output\Subscription\SendSubscriptionUpdateLinkResponseData;
use Maxiviper117\Paystack\Data\Output\Transaction\FetchTransactionResponseData;
use Maxiviper117\Paystack\Data\Output\Transaction\InitializeTransactionResponseData;
use Maxiviper117\Paystack\Data\Output\Transaction\ListTransactionsResponseData;
use Maxiviper117\Paystack\Data\Output\Transaction\VerifyTransactionResponseData;
use Maxiviper117\Paystack\PaystackManager;

/**
 * @see PaystackManager
 *
 * @method static InitializeTransactionResponseData initializeTransaction(InitializeTransactionInputData $input)
 * @method static VerifyTransactionResponseData verifyTransaction(VerifyTransactionInputData $input)
 * @method static ListDisputesResponseData listDisputes(ListDisputesInputData $input)
 * @method static FetchDisputeResponseData fetchDispute(FetchDisputeInputData $input)
 * @method static ListTransactionDisputesResponseData listTransactionDisputes(ListTransactionDisputesInputData $input)
 * @method static UpdateDisputeResponseData updateDispute(UpdateDisputeInputData $input)
 * @method static AddDisputeEvidenceResponseData addDisputeEvidence(AddDisputeEvidenceInputData $input)
 * @method static GetDisputeUploadUrlResponseData getDisputeUploadUrl(GetDisputeUploadUrlInputData $input)
 * @method static ResolveDisputeResponseData resolveDispute(ResolveDisputeInputData $input)
 * @method static ExportDisputesResponseData exportDisputes(ListDisputesInputData $input)
 * @method static CreateRefundResponseData createRefund(CreateRefundInputData $input)
 * @method static RetryRefundResponseData retryRefund(RetryRefundInputData $input)
 * @method static FetchRefundResponseData fetchRefund(FetchRefundInputData $input)
 * @method static ListRefundsResponseData listRefunds(ListRefundsInputData $input)
 * @method static FetchTransactionResponseData fetchTransaction(FetchTransactionInputData $input)
 * @method static ListTransactionsResponseData listTransactions(ListTransactionsInputData $input)
 * @method static CreateCustomerResponseData createCustomer(CreateCustomerInputData $input)
 * @method static FetchCustomerResponseData fetchCustomer(FetchCustomerInputData $input)
 * @method static UpdateCustomerResponseData updateCustomer(UpdateCustomerInputData $input)
 * @method static ListCustomersResponseData listCustomers(ListCustomersInputData $input)
 * @method static ValidateCustomerResponseData validateCustomer(ValidateCustomerInputData $input)
 * @method static SetCustomerRiskActionResponseData setCustomerRiskAction(SetCustomerRiskActionInputData $input)
 * @method static CreatePlanResponseData createPlan(CreatePlanInputData $input)
 * @method static UpdatePlanResponseData updatePlan(UpdatePlanInputData $input)
 * @method static FetchPlanResponseData fetchPlan(FetchPlanInputData $input)
 * @method static ListPlansResponseData listPlans(ListPlansInputData $input)
 * @method static CreateSubscriptionResponseData createSubscription(CreateSubscriptionInputData $input)
 * @method static FetchSubscriptionResponseData fetchSubscription(FetchSubscriptionInputData $input)
 * @method static ListSubscriptionsResponseData listSubscriptions(ListSubscriptionsInputData $input)
 * @method static EnableSubscriptionResponseData enableSubscription(EnableSubscriptionInputData $input)
 * @method static DisableSubscriptionResponseData disableSubscription(DisableSubscriptionInputData $input)
 * @method static GenerateSubscriptionUpdateLinkResponseData generateSubscriptionUpdateLink(GenerateSubscriptionUpdateLinkInputData $input)
 * @method static SendSubscriptionUpdateLinkResponseData sendSubscriptionUpdateLink(SendSubscriptionUpdateLinkInputData $input)
 */
class Paystack extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'paystack';
    }
}
