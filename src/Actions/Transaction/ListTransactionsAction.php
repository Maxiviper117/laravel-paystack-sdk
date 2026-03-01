<?php

namespace Maxiviper117\Paystack\Actions\Transaction;

use Maxiviper117\Paystack\Data\Transaction\TransactionListData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Transaction\ListTransactionsRequest;

final class ListTransactionsAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    /**
     * @param array<string, mixed> $filters
     */
    public function execute(array $filters = []): TransactionListData
    {
        $response = $this->connector->send(new ListTransactionsRequest($filters));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof TransactionListData);

        return $dto;
    }

    /**
     * @param array<string, mixed> $filters
     */
    public static function run(array $filters = []): TransactionListData
    {
        return app(self::class)->execute($filters);
    }
}
