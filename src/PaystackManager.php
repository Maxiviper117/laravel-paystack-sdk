<?php

namespace Maxiviper117\Paystack;

use Illuminate\Contracts\Container\Container;
use Maxiviper117\Paystack\Actions\Customer\CreateCustomerAction;
use Maxiviper117\Paystack\Actions\Customer\ListCustomersAction;
use Maxiviper117\Paystack\Actions\Customer\UpdateCustomerAction;
use Maxiviper117\Paystack\Actions\Transaction\FetchTransactionAction;
use Maxiviper117\Paystack\Actions\Transaction\InitializeTransactionAction;
use Maxiviper117\Paystack\Actions\Transaction\ListTransactionsAction;
use Maxiviper117\Paystack\Actions\Transaction\VerifyTransactionAction;
use Maxiviper117\Paystack\Data\Input\Customer\CreateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Customer\ListCustomersInputData;
use Maxiviper117\Paystack\Data\Input\Customer\UpdateCustomerInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\FetchTransactionInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\ListTransactionsInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Data\Output\Customer\CreateCustomerResponseData;
use Maxiviper117\Paystack\Data\Output\Customer\ListCustomersResponseData;
use Maxiviper117\Paystack\Data\Output\Customer\UpdateCustomerResponseData;
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
}
