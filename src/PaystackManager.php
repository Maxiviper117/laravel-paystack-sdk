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
use Maxiviper117\Paystack\Data\Customer\CustomerData;
use Maxiviper117\Paystack\Data\Customer\CustomerListData;
use Maxiviper117\Paystack\Data\Transaction\InitializedTransactionData;
use Maxiviper117\Paystack\Data\Transaction\TransactionData;
use Maxiviper117\Paystack\Data\Transaction\TransactionListData;
use Maxiviper117\Paystack\Data\Transaction\VerificationData;

class PaystackManager
{
    public function __construct(
        protected Container $container
    ) {}

    /**
     * @param  array<string, mixed>  $options
     */
    public function initializeTransaction(string $email, int|float|string $amount, array $options = []): InitializedTransactionData
    {
        return $this->container->make(InitializeTransactionAction::class)->execute($email, $amount, $options);
    }

    public function verifyTransaction(string $reference): VerificationData
    {
        return $this->container->make(VerifyTransactionAction::class)->execute($reference);
    }

    public function fetchTransaction(int|string $idOrReference): TransactionData
    {
        return $this->container->make(FetchTransactionAction::class)->execute($idOrReference);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function listTransactions(array $filters = []): TransactionListData
    {
        return $this->container->make(ListTransactionsAction::class)->execute($filters);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createCustomer(string $email, array $attributes = []): CustomerData
    {
        return $this->container->make(CreateCustomerAction::class)->execute($email, $attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateCustomer(string $customerCode, array $attributes): CustomerData
    {
        return $this->container->make(UpdateCustomerAction::class)->execute($customerCode, $attributes);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function listCustomers(array $filters = []): CustomerListData
    {
        return $this->container->make(ListCustomersAction::class)->execute($filters);
    }
}
