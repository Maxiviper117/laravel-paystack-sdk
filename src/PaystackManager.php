<?php

namespace Maxiviper117\Paystack;

use Illuminate\Contracts\Container\Container;
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
use Maxiviper117\Paystack\Actions\Transaction\FetchTransactionAction;
use Maxiviper117\Paystack\Actions\Transaction\InitializeTransactionAction;
use Maxiviper117\Paystack\Actions\Transaction\ListTransactionsAction;
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
use Maxiviper117\Paystack\Data\Input\Transaction\FetchTransactionInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\ListTransactionsInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
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

class PaystackManager
{
    public function __construct(
        protected Container $container
    ) {}

    public function initializeTransaction(InitializeTransactionInputData $input): InitializeTransactionResponseData
    {
        return $this->container->make(InitializeTransactionAction::class)->execute($input);
    }

    public function verifyTransaction(VerifyTransactionInputData $input): VerifyTransactionResponseData
    {
        return $this->container->make(VerifyTransactionAction::class)->execute($input);
    }

    public function fetchTransaction(FetchTransactionInputData $input): FetchTransactionResponseData
    {
        return $this->container->make(FetchTransactionAction::class)->execute($input);
    }

    public function listTransactions(ListTransactionsInputData $input): ListTransactionsResponseData
    {
        return $this->container->make(ListTransactionsAction::class)->execute($input);
    }

    public function createCustomer(CreateCustomerInputData $input): CreateCustomerResponseData
    {
        return $this->container->make(CreateCustomerAction::class)->execute($input);
    }

    public function updateCustomer(UpdateCustomerInputData $input): UpdateCustomerResponseData
    {
        return $this->container->make(UpdateCustomerAction::class)->execute($input);
    }

    public function listCustomers(ListCustomersInputData $input): ListCustomersResponseData
    {
        return $this->container->make(ListCustomersAction::class)->execute($input);
    }

    public function createPlan(CreatePlanInputData $input): CreatePlanResponseData
    {
        return $this->container->make(CreatePlanAction::class)->execute($input);
    }

    public function updatePlan(UpdatePlanInputData $input): UpdatePlanResponseData
    {
        return $this->container->make(UpdatePlanAction::class)->execute($input);
    }

    public function fetchPlan(FetchPlanInputData $input): FetchPlanResponseData
    {
        return $this->container->make(FetchPlanAction::class)->execute($input);
    }

    public function listPlans(ListPlansInputData $input): ListPlansResponseData
    {
        return $this->container->make(ListPlansAction::class)->execute($input);
    }

    public function createSubscription(CreateSubscriptionInputData $input): CreateSubscriptionResponseData
    {
        return $this->container->make(CreateSubscriptionAction::class)->execute($input);
    }

    public function fetchSubscription(FetchSubscriptionInputData $input): FetchSubscriptionResponseData
    {
        return $this->container->make(FetchSubscriptionAction::class)->execute($input);
    }

    public function listSubscriptions(ListSubscriptionsInputData $input): ListSubscriptionsResponseData
    {
        return $this->container->make(ListSubscriptionsAction::class)->execute($input);
    }

    public function enableSubscription(EnableSubscriptionInputData $input): EnableSubscriptionResponseData
    {
        return $this->container->make(EnableSubscriptionAction::class)->execute($input);
    }

    public function disableSubscription(DisableSubscriptionInputData $input): DisableSubscriptionResponseData
    {
        return $this->container->make(DisableSubscriptionAction::class)->execute($input);
    }
}
