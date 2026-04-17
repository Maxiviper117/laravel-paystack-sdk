<?php

declare(strict_types=1);

namespace Maxiviper117\Paystack\Actions\Transaction;

use Maxiviper117\Paystack\Data\Input\Transaction\ListTransactionsInputData;
use Maxiviper117\Paystack\Data\Output\Transaction\ListTransactionsResponseData;
use Maxiviper117\Paystack\Integrations\PaystackConnector;
use Maxiviper117\Paystack\Integrations\Requests\Transaction\ListTransactionsRequest;

final class ListTransactionsAction
{
    public function __construct(
        protected PaystackConnector $connector
    ) {}

    public function execute(ListTransactionsInputData $input): ListTransactionsResponseData
    {
        $response = $this->connector->send(new ListTransactionsRequest($input));

        if ($this->connector->throwsOnApiError()) {
            $response->throw();
        }

        $dto = $response->dto();

        assert($dto instanceof ListTransactionsResponseData);

        return $dto;
    }

    public function __invoke(ListTransactionsInputData $input): ListTransactionsResponseData
    {
        return $this->execute($input);
    }
}
